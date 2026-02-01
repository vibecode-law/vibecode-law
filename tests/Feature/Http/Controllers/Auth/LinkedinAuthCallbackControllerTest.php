<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User as LinkedinUser;

use function Pest\Laravel\assertAuthenticated;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
});

describe('new user', function () {
    it('creates a new user from linkedin callback when linkedin email is verified', function () {
        Http::fake();

        Socialite::fake('linkedin-openid', (new LinkedinUser)->map([
            'id' => 'linkedin-123',
            'email' => 'fake@email.com',
            'user' => [
                'given_name' => 'John',
                'family_name' => 'Doe',
                'email_verified' => true,
            ],
        ])->setToken('fake-access-token'));

        get('/auth/login/linkedin/callback')
            ->assertRedirect(route('auth.complete-profile'));

        assertDatabaseHas('users', [
            'linkedin_id' => 'linkedin-123',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'handle' => 'john-doe',
            'email' => 'fake@email.com',
            'linkedin_token' => 'fake-access-token',
        ]);
    });

    it('redirects new users to complete profile page', function () {
        Http::fake();

        Socialite::fake('linkedin-openid', (new LinkedinUser)->map([
            'id' => 'linkedin-new-user',
            'email' => 'newuser@email.com',
            'user' => [
                'given_name' => 'New',
                'family_name' => 'User',
                'email_verified' => true,
            ],
        ])->setToken('fake-token'));

        get('/auth/login/linkedin/callback')
            ->assertRedirect(route('auth.complete-profile'));
    });

    it('passes intended url to complete profile page', function () {
        Http::fake();

        Socialite::fake('linkedin-openid', (new LinkedinUser)->map([
            'id' => 'linkedin-intended',
            'email' => 'intended@email.com',
            'user' => [
                'given_name' => 'Intended',
                'family_name' => 'User',
                'email_verified' => true,
            ],
        ])->setToken('fake-token'));

        test()->withSession(['url.intended' => '/showcases'])
            ->get('/auth/login/linkedin/callback')
            ->assertRedirect(route('auth.complete-profile', ['intended' => '/showcases']));
    });

    it('generates unique handle when name already exists', function () {
        Http::fake();

        User::factory()->create(['handle' => 'john-doe']);

        Socialite::fake('linkedin-openid', (new LinkedinUser)->map([
            'id' => 'linkedin-123',
            'email' => 'fake@email.com',
            'user' => [
                'given_name' => 'John',
                'family_name' => 'Doe',
                'email_verified' => true,
            ],
        ])->setToken('fake-access-token'));

        get('/auth/login/linkedin/callback')
            ->assertRedirect(route('auth.complete-profile'));

        $user = User::where('linkedin_id', 'linkedin-123')->first();

        expect($user->handle)->toStartWith('john-doe-');
        expect($user->handle)->not->toBe('john-doe');
    });

    it('sets email_verified_at when creating user with verified linkedin email', function () {
        Http::fake();

        Socialite::fake('linkedin-openid', (new LinkedinUser)->map([
            'id' => 'linkedin-123',
            'email' => 'fake@email.com',
            'user' => [
                'given_name' => 'John',
                'family_name' => 'Doe',
                'email_verified' => true,
            ],
        ])->setToken('fake-access-token'));

        get('/auth/login/linkedin/callback');

        $user = User::where('linkedin_id', 'linkedin-123')->first();

        expect($user->email_verified_at)->not->toBeNull();
    });

    it('does not create user when linkedin email is unverified', function () {
        Http::fake();

        Socialite::fake('linkedin-openid', (new LinkedinUser)->map([
            'id' => 'linkedin-123',
            'email' => 'fake@email.com',
            'user' => [
                'given_name' => 'John',
                'family_name' => 'Doe',
                'email_verified' => false,
            ],
        ])->setToken('fake-access-token'));

        get('/auth/login/linkedin/callback')
            ->assertRedirect(route('login'))
            ->assertSessionHas('flash.message', ['message' => 'Your LinkedIn email address has not been verified. Please verify your email on LinkedIn and try again.', 'type' => 'error']);

        expect(User::count())->toBe(0);
    });

    it('authenticates a new user after callback', function () {
        Http::fake();

        Socialite::fake('linkedin-openid', (new LinkedinUser)->map([
            'id' => 'linkedin-123',
            'email' => 'fake@email.com',
            'user' => [
                'given_name' => 'John',
                'family_name' => 'Doe',
                'email_verified' => true,
            ],
        ])->setToken('fake-access-token'));

        get('/auth/login/linkedin/callback');

        assertAuthenticated();
    });

    it('stores the linkedin token for api calls', function () {
        Http::fake();

        Socialite::fake('linkedin-openid', (new LinkedinUser)->map([
            'id' => 'linkedin-456',
            'email' => 'fake@email.com',
            'user' => [
                'given_name' => 'Jane',
                'family_name' => 'Smith',
                'email_verified' => true,
            ],
        ])->setToken('my-special-token-123'));

        get('/auth/login/linkedin/callback');

        $user = User::where('linkedin_id', 'linkedin-456')->first();

        expect($user->linkedin_token)->toBe('my-special-token-123');
    });
});

describe('existing local account handling', function () {
    it('updates an existing user that is already linked and authenticates as that user', function () {
        Http::fake();

        $existingUser = User::factory()->create([
            'linkedin_id' => 'linkedin-123',
            'first_name' => 'Old',
            'last_name' => 'Name',
            'email' => 'fake@email.com',
            'linkedin_token' => 'old-token',
        ]);

        Socialite::fake('linkedin-openid', (new LinkedinUser)->map([
            'id' => 'linkedin-123',
            'email' => 'new-fake@email.com',
            'user' => [
                'given_name' => 'Jane',
                'family_name' => 'Miggins',
            ],
        ])->setToken('new-access-token'));

        get('/auth/login/linkedin/callback')
            ->assertRedirect(route('home'));

        assertDatabaseHas('users', [
            'id' => $existingUser->id,
            'linkedin_id' => 'linkedin-123',
            'first_name' => 'Jane',
            'last_name' => 'Miggins',
            'email' => 'new-fake@email.com',
            'linkedin_token' => 'new-access-token',
        ]);

        expect(User::count())->toBe(1);
        expect(Auth::user()->is($existingUser));
    });

    it('links linkedin to existing user when local email is verified', function () {
        Http::fake();

        $existingUser = User::factory()->create([
            'email' => 'verified@email.com',
            'email_verified_at' => now(),
            'linkedin_id' => null,
        ]);

        Socialite::fake('linkedin-openid', (new LinkedinUser)->map([
            'id' => 'linkedin-new-link',
            'email' => 'verified@email.com',
            'user' => [
                'given_name' => 'John',
                'family_name' => 'Doe',
            ],
        ])->setToken('new-token'));

        get('/auth/login/linkedin/callback')
            ->assertRedirect(route('home'));

        $existingUser->refresh();

        expect($existingUser->linkedin_id)->toBe('linkedin-new-link');
        expect($existingUser->linkedin_token)->toBe('new-token');
        expect(User::count())->toBe(1);
        expect(Auth::id())->toBe($existingUser->id);
    });

    it('shows appropriate error message when both local and linkedin emails are unverified', function () {
        Http::fake();

        User::factory()->create([
            'email' => 'unverified@email.com',
            'email_verified_at' => null,
        ]);

        Socialite::fake('linkedin-openid', (new LinkedinUser)->map([
            'id' => 'linkedin-attempt',
            'email' => 'unverified@email.com',
            'user' => [
                'given_name' => 'Jane',
                'family_name' => 'Doe',
                'email_verified' => false,
            ],
        ])->setToken('some-token'));

        get('/auth/login/linkedin/callback')
            ->assertRedirect(route('login'))
            ->assertSessionHas('flash.message', ['message' => 'Your Linkedin account does not have a verified email address. Please verify it and try again.', 'type' => 'error']);
    });

    it('verifies local user when linkedin email is verified but local is not', function () {
        Http::fake();

        $existingUser = User::factory()->create([
            'email' => 'unverified@email.com',
            'email_verified_at' => null,
            'linkedin_id' => null,
        ]);

        Socialite::fake('linkedin-openid', (new LinkedinUser)->map([
            'id' => 'linkedin-verify',
            'email' => 'unverified@email.com',
            'user' => [
                'given_name' => 'Jane',
                'family_name' => 'Doe',
                'email_verified' => true,
            ],
        ])->setToken('some-token'));

        get('/auth/login/linkedin/callback')
            ->assertRedirect(route('home'));

        $existingUser->refresh();

        expect($existingUser->email_verified_at)->not->toBeNull();
        expect($existingUser->linkedin_id)->toBe('linkedin-verify');
        expect(Auth::id())->toBe($existingUser->id);
    });

    it('preserves existing user data when linking linkedin to verified account', function () {
        Http::fake();

        $existingUser = User::factory()->create([
            'first_name' => 'Original',
            'last_name' => 'Name',
            'email' => 'verified@email.com',
            'email_verified_at' => now(),
            'linkedin_id' => null,
        ]);

        Socialite::fake('linkedin-openid', (new LinkedinUser)->map([
            'id' => 'linkedin-link',
            'email' => 'verified@email.com',
            'user' => [
                'given_name' => 'Linkedin',
                'family_name' => 'New',
            ],
        ])->setToken('token'));

        get('/auth/login/linkedin/callback');

        $existingUser->refresh();

        // Updates with new name
        expect($existingUser->first_name)->toBe('Linkedin');
        expect($existingUser->last_name)->toBe('New');

        // Linkedin ID is updated
        expect($existingUser->linkedin_id)->toBe('linkedin-link');
    });

    it('does not unverify a verified local account when linkedin is not verified', function () {
        Http::fake();

        $existingUser = User::factory()->create([
            'email' => 'verified@email.com',
            'email_verified_at' => $verifiedAt = Date::now(),
            'linkedin_id' => null,
        ]);

        Socialite::fake('linkedin-openid', (new LinkedinUser)->map([
            'id' => 'linkedin-verify',
            'email' => 'verified@email.com',
            'user' => [
                'given_name' => 'Jane',
                'family_name' => 'Doe',
                'email_verified' => false,
            ],
        ])->setToken('some-token'));

        get('/auth/login/linkedin/callback')
            ->assertRedirect(route('home'));

        $existingUser->refresh();

        expect($existingUser->email_verified_at->toDateTimeString())->toBe($verifiedAt->toDateTimeString());
        expect(Auth::id())->toBe($existingUser->id);
    });
});

describe('avatar handling', function () {
    it('handles users with no avatar', function () {
        Http::fake();

        Socialite::fake('linkedin-openid', (new LinkedinUser)->map([
            'id' => 'linkedin-789',
            'email' => 'fake@email.com',
            'user' => [
                'given_name' => 'No',
                'family_name' => 'Avatar',
                'email_verified' => true,
            ],
            'avatar' => null,
        ])->setToken('fake-token'));

        get('/auth/login/linkedin/callback')
            ->assertRedirect(route('auth.complete-profile'));

        $user = User::where('linkedin_id', 'linkedin-789')->first();

        expect($user->avatar_path)->toBeNull();
    });

    it('downloads and stores a new avatar from linkedin', function () {
        $avatarContent = 'fake-image-content-for-avatar';

        Http::fake([
            'https://media.linkedin.com/avatar.jpg' => Http::response(
                body: $avatarContent,
                status: 200,
                headers: ['Content-Type' => 'image/jpeg']
            ),
            '*' => Http::response(),
        ]);

        Socialite::fake('linkedin-openid', (new LinkedinUser)->map([
            'id' => 'linkedin-avatar-test',
            'email' => 'fake@email.com',
            'avatar' => 'https://media.linkedin.com/avatar.jpg',
            'user' => [
                'given_name' => 'Avatar',
                'family_name' => 'User',
                'email_verified' => true,
            ],
        ])->setToken('fake-token'));

        get('/auth/login/linkedin/callback')
            ->assertRedirect(route('auth.complete-profile'));

        $user = User::where('linkedin_id', 'linkedin-avatar-test')->first();

        expect($user->avatar_path)->not->toBeNull();
        expect($user->avatar_path)->toStartWith('users/avatars/');
        expect($user->avatar_path)->toEndWith('.jpg');
        expect(Storage::disk('public')->get($user->avatar_path))->toBe($avatarContent);
    });

    it('does not update avatar when it has not changed', function () {
        $avatarContent = 'existing-avatar-content';
        $existingAvatarPath = 'users/avatars/existing-avatar.jpg';

        Storage::disk('public')->put($existingAvatarPath, $avatarContent);

        $existingUser = User::factory()->create([
            'linkedin_id' => 'linkedin-same-avatar',
            'avatar_path' => $existingAvatarPath,
        ]);

        Http::fake([
            'https://media.linkedin.com/same-avatar.jpg' => Http::response(
                body: $avatarContent,
                status: 200,
                headers: ['Content-Type' => 'image/jpeg']
            ),
            '*' => Http::response(),
        ]);

        Socialite::fake('linkedin-openid', (new LinkedinUser)->map([
            'id' => 'linkedin-same-avatar',
            'email' => 'fake@email.com',
            'avatar' => 'https://media.linkedin.com/same-avatar.jpg',
            'user' => [
                'given_name' => 'Same',
                'family_name' => 'Avatar',
            ],
        ])->setToken('fake-token'));

        get('/auth/login/linkedin/callback')
            ->assertRedirect(route('home'));

        $existingUser->refresh();

        expect($existingUser->avatar_path)->toBe($existingAvatarPath);
        expect(Storage::disk('public')->exists($existingAvatarPath))->toBeTrue();
    });

    it('updates avatar when it has changed', function () {
        $oldAvatarContent = 'old-avatar-content';
        $newAvatarContent = 'new-avatar-content-different';
        $existingAvatarPath = 'users/avatars/old-avatar.jpg';

        Storage::disk('public')->put($existingAvatarPath, $oldAvatarContent);

        $existingUser = User::factory()->create([
            'linkedin_id' => 'linkedin-changed-avatar',
            'avatar_path' => $existingAvatarPath,
        ]);

        Http::fake([
            'https://media.linkedin.com/new-avatar.jpg' => Http::response(
                body: $newAvatarContent,
                status: 200,
                headers: ['Content-Type' => 'image/jpeg']
            ),
            '*' => Http::response(),
        ]);

        Socialite::fake('linkedin-openid', (new LinkedinUser)->map([
            'id' => 'linkedin-changed-avatar',
            'email' => 'fake@email.com',
            'avatar' => 'https://media.linkedin.com/new-avatar.jpg',
            'user' => [
                'given_name' => 'Changed',
                'family_name' => 'Avatar',
            ],
        ])->setToken('fake-token'));

        get('/auth/login/linkedin/callback')
            ->assertRedirect(route('home'));

        $existingUser->refresh();

        expect($existingUser->avatar_path)->not->toBe($existingAvatarPath);
        expect($existingUser->avatar_path)->toStartWith('users/avatars/');
        expect(Storage::disk('public')->get($existingUser->avatar_path))->toBe($newAvatarContent);
        expect(Storage::disk('public')->exists($existingAvatarPath))->toBeFalse();
    });
});

describe('error handling', function () {
    it('redirects to login with error when oauth state is invalid', function () {
        Socialite::shouldReceive('driver')
            ->with('linkedin-openid')
            ->andReturnSelf()
            ->shouldReceive('user')
            ->andThrow(new InvalidStateException);

        get('/auth/login/linkedin/callback')
            ->assertRedirect(route('login'))
            ->assertSessionHas('flash.message', [
                'message' => "There was an issue with Linkedin's response. Please try again.",
                'type' => 'error',
            ]);

        expect(User::count())->toBe(0);
    });
});

describe('linkedin profile url', function () {
    it('does not fetch linkedin profile url when config is disabled', function () {
        config()->set('services.linkedin-openid.auto_fetch_profile_url', false);

        Http::fake([
            'https://api.linkedin.com/rest/identityMe' => Http::response([
                'basicInfo' => [
                    'profileUrl' => 'https://www.linkedin.com/profile/redirect/abc123',
                ],
            ], 200),
            '*' => Http::response(),
        ]);

        Socialite::fake('linkedin-openid', (new LinkedinUser)->map([
            'id' => 'linkedin-config-disabled',
            'email' => 'fake@email.com',
            'user' => [
                'given_name' => 'Config',
                'family_name' => 'Disabled',
                'email_verified' => true,
            ],
        ])->setToken('fake-token'));

        get('/auth/login/linkedin/callback')
            ->assertRedirect(route('auth.complete-profile'));

        $user = User::where('linkedin_id', 'linkedin-config-disabled')->first();

        expect($user)->not->toBeNull();
        expect($user->linkedin_url)->toBeNull();

        Http::assertNotSent(fn ($request) => $request->url() === 'https://api.linkedin.com/rest/identityMe');
    });

    it('retrieves and stores the linkedin profile url', function () {
        Http::fake([
            'https://api.linkedin.com/rest/identityMe' => Http::response([
                'basicInfo' => [
                    'profileUrl' => 'https://www.linkedin.com/profile/redirect/abc123',
                ],
            ], 200),
            'https://www.linkedin.com/profile/redirect/abc123' => Http::response(
                body: '',
                status: 302,
                headers: ['Location' => 'https://www.linkedin.com/in/johndoe']
            ),
            '*' => Http::response(),
        ]);

        Socialite::fake('linkedin-openid', (new LinkedinUser)->map([
            'id' => 'linkedin-url-test',
            'email' => 'fake@email.com',
            'user' => [
                'given_name' => 'John',
                'family_name' => 'Doe',
                'email_verified' => true,
            ],
        ])->setToken('fake-token'));

        get('/auth/login/linkedin/callback')
            ->assertRedirect(route('auth.complete-profile'));

        $user = User::where('linkedin_id', 'linkedin-url-test')->first();

        expect($user->linkedin_url)->toBe('https://www.linkedin.com/in/johndoe');
    });

    it('does not overwrite existing linkedin url', function () {
        $existingUser = User::factory()->create([
            'linkedin_id' => 'linkedin-existing-url',
            'linkedin_url' => 'https://www.linkedin.com/in/existing-profile',
        ]);

        Http::fake([
            'https://api.linkedin.com/rest/identityMe' => Http::response([
                'basicInfo' => [
                    'profileUrl' => 'https://www.linkedin.com/profile/redirect/new123',
                ],
            ], 200),
            '*' => Http::response(),
        ]);

        Socialite::fake('linkedin-openid', (new LinkedinUser)->map([
            'id' => 'linkedin-existing-url',
            'email' => 'fake@email.com',
            'user' => [
                'given_name' => 'Existing',
                'family_name' => 'User',
            ],
        ])->setToken('fake-token'));

        get('/auth/login/linkedin/callback')
            ->assertRedirect(route('home'));

        $existingUser->refresh();

        expect($existingUser->linkedin_url)->toBe('https://www.linkedin.com/in/existing-profile');

        Http::assertNotSent(fn ($request) => $request->url() === 'https://api.linkedin.com/rest/identityMe');
    });

    it('handles failed linkedin api response gracefully', function () {
        Http::fake([
            'https://api.linkedin.com/rest/identityMe' => Http::response(
                body: 'Unauthorized',
                status: 401
            ),
            '*' => Http::response(),
        ]);

        Socialite::fake('linkedin-openid', (new LinkedinUser)->map([
            'id' => 'linkedin-api-fail',
            'email' => 'fake@email.com',
            'user' => [
                'given_name' => 'Api',
                'family_name' => 'Fail',
                'email_verified' => true,
            ],
        ])->setToken('fake-token'));

        get('/auth/login/linkedin/callback')
            ->assertRedirect(route('auth.complete-profile'));

        $user = User::where('linkedin_id', 'linkedin-api-fail')->first();

        expect($user)->not->toBeNull();
        expect($user->linkedin_url)->toBeNull();
    });

    it('handles profile redirect url that does not redirect gracefully', function () {
        Http::fake([
            'https://api.linkedin.com/rest/identityMe' => Http::response([
                'basicInfo' => [
                    'profileUrl' => 'https://www.linkedin.com/profile/redirect/abc123',
                ],
            ], 200),
            'https://www.linkedin.com/profile/redirect/abc123' => Http::response(
                body: 'Not Found',
                status: 200
            ),
            '*' => Http::response(),
        ]);

        Socialite::fake('linkedin-openid', (new LinkedinUser)->map([
            'id' => 'linkedin-no-redirect',
            'email' => 'fake@email.com',
            'user' => [
                'given_name' => 'No',
                'family_name' => 'Redirect',
                'email_verified' => true,
            ],
        ])->setToken('fake-token'));

        get('/auth/login/linkedin/callback')
            ->assertRedirect(route('auth.complete-profile'));

        $user = User::where('linkedin_id', 'linkedin-no-redirect')->first();

        expect($user)->not->toBeNull();
        expect($user->linkedin_url)->toBeNull();
    });

});

describe('existing user redirect', function () {
    it('does not redirect an existing user to complete profile', function () {
        Http::fake();

        $existingUser = User::factory()->create([
            'linkedin_id' => 'linkedin-existing',
            'email' => 'existing@email.com',
        ]);

        Socialite::fake('linkedin-openid', (new LinkedinUser)->map([
            'id' => 'linkedin-existing',
            'email' => 'existing@email.com',
            'user' => [
                'given_name' => 'Existing',
                'family_name' => 'User',
            ],
        ])->setToken('fake-token'));

        get('/auth/login/linkedin/callback')
            ->assertRedirect(route('home'));

        expect(User::count())->toBe(1);
    });
});
