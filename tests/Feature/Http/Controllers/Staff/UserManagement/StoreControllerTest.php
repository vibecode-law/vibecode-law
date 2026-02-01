<?php

use App\Enums\TeamType;
use App\Models\User;
use App\Notifications\UserInvitation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\post;

describe('auth', function () {
    test('requires authentication', function () {
        post(route('staff.users.store'), [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'handle' => 'john-doe',
            'email' => 'john@example.com',
        ])->assertRedirect(route('login'));
    });

    test('allows admin to create users', function () {
        Notification::fake();

        $admin = User::factory()->admin()->create();

        actingAs($admin);

        post(route('staff.users.store'), [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'handle' => 'john-doe',
            'email' => 'john@example.com',
        ])->assertRedirect();
    });

    test('does not allow moderators to create users', function () {
        $moderator = User::factory()->moderator()->create();

        actingAs($moderator);

        post(route('staff.users.store'), [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'handle' => 'john-doe',
            'email' => 'john@example.com',
        ])->assertForbidden();
    });

    test('does not allow regular users to create users', function () {
        $user = User::factory()->create();

        actingAs($user);

        post(route('staff.users.store'), [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'handle' => 'john-doe',
            'email' => 'john@example.com',
        ])->assertForbidden();
    });
});

describe('store', function () {
    test('creates a new user', function () {
        Notification::fake();

        $admin = User::factory()->admin()->create();

        actingAs($admin);

        post(route('staff.users.store'), [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'handle' => 'john-doe',
            'email' => 'john@example.com',
            'organisation' => 'Acme Inc',
            'job_title' => 'Developer',
            'bio' => 'A developer at Acme',
            'linkedin_url' => 'https://linkedin.com/in/johndoe',
        ]);

        assertDatabaseHas('users', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'handle' => 'john-doe',
            'email' => 'john@example.com',
            'organisation' => 'Acme Inc',
            'job_title' => 'Developer',
            'bio' => 'A developer at Acme',
            'linkedin_url' => 'https://linkedin.com/in/johndoe',
        ]);
    });

    test('uses provided handle', function () {
        Notification::fake();

        $admin = User::factory()->admin()->create();

        actingAs($admin);

        post(route('staff.users.store'), [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'handle' => 'custom-handle',
            'email' => 'john@example.com',
        ]);

        assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'handle' => 'custom-handle',
        ]);
    });

    test('assigns roles to user', function () {
        Notification::fake();

        $admin = User::factory()->admin()->create();

        actingAs($admin);

        post(route('staff.users.store'), [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'handle' => 'john-doe',
            'email' => 'john@example.com',
            'roles' => ['Moderator'],
        ]);

        $user = User::query()->where('email', 'john@example.com')->first();

        expect($user->hasRole('Moderator'))->toBeTrue();
    });

    test('sends invitation notification', function () {
        Notification::fake();

        $admin = User::factory()->admin()->create();

        actingAs($admin);

        post(route('staff.users.store'), [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'handle' => 'john-doe',
            'email' => 'john@example.com',
        ]);

        $user = User::query()->where('email', 'john@example.com')->first();

        Notification::assertSentTo($user, UserInvitation::class);
    });

    test('redirects to edit page on success', function () {
        Notification::fake();

        $admin = User::factory()->admin()->create();

        actingAs($admin);

        $response = post(route('staff.users.store'), [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'handle' => 'john-doe',
            'email' => 'john@example.com',
        ]);

        $user = User::query()->where('email', 'john@example.com')->first();

        $response->assertRedirect(route('staff.users.edit', $user));
    });

    test('returns success message', function () {
        Notification::fake();

        $admin = User::factory()->admin()->create();

        actingAs($admin);

        post(route('staff.users.store'), [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'handle' => 'john-doe',
            'email' => 'john@example.com',
        ])->assertSessionHas('flash.message', [
            'message' => 'User created and invitation sent.',
            'type' => 'success',
        ]);
    });

    test('creates user without password', function () {
        Notification::fake();

        $admin = User::factory()->admin()->create();

        actingAs($admin);

        post(route('staff.users.store'), [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'handle' => 'john-doe',
            'email' => 'john@example.com',
        ]);

        $user = User::query()->where('email', 'john@example.com')->first();

        expect($user->password)->toBeNull();
    });

    test('creates user with team membership', function () {
        Notification::fake();

        $admin = User::factory()->admin()->create();

        actingAs($admin);

        post(route('staff.users.store'), [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'handle' => 'john-doe',
            'email' => 'john@example.com',
            'team_type' => TeamType::CoreTeam->value,
            'team_role' => 'Lead Developer',
        ]);

        assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'team_type' => TeamType::CoreTeam->value,
            'team_role' => 'Lead Developer',
        ]);
    });

    test('creates user without team membership', function () {
        Notification::fake();

        $admin = User::factory()->admin()->create();

        actingAs($admin);

        post(route('staff.users.store'), [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'handle' => 'john-doe',
            'email' => 'john@example.com',
        ]);

        assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'team_type' => null,
            'team_role' => null,
        ]);
    });

    test('creates user with marketing opt-out', function () {
        Notification::fake();

        $admin = User::factory()->admin()->create();

        actingAs($admin);

        post(route('staff.users.store'), [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'handle' => 'john-doe',
            'email' => 'john@example.com',
            'marketing_opt_out' => true,
        ]);

        $user = User::query()->where('email', 'john@example.com')->first();

        expect($user->marketing_opt_out_at)->not->toBeNull();
    });

    test('creates user without marketing opt-out by default', function () {
        Notification::fake();

        $admin = User::factory()->admin()->create();

        actingAs($admin);

        post(route('staff.users.store'), [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'handle' => 'john-doe',
            'email' => 'john@example.com',
        ]);

        $user = User::query()->where('email', 'john@example.com')->first();

        expect($user->marketing_opt_out_at)->toBeNull();
    });

    test('creates user with avatar', function () {
        Notification::fake();
        Storage::fake('public');

        $admin = User::factory()->admin()->create();

        actingAs($admin);

        $avatar = UploadedFile::fake()->image(name: 'avatar.jpg', width: 200, height: 200);

        post(route('staff.users.store'), [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'handle' => 'john-doe',
            'email' => 'john@example.com',
            'avatar' => $avatar,
        ]);

        $user = User::query()->where('email', 'john@example.com')->first();

        expect($user->avatar_path)->not->toBeNull();
        Storage::disk('public')->assertExists($user->avatar_path);
    });

    test('auto-generates handle when not provided', function () {
        Notification::fake();

        $admin = User::factory()->admin()->create();

        actingAs($admin);

        post(route('staff.users.store'), [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john-auto@example.com',
        ]);

        $user = User::query()->where('email', 'john-auto@example.com')->first();

        expect($user->handle)->not->toBeNull();
        expect($user->handle)->toContain('john');
    });
});

describe('validation', function () {
    test('validates required fields', function ($data, $invalid) {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        post(route('staff.users.store'), $data)
            ->assertSessionHasErrors($invalid);
    })->with([
        'missing first_name' => [
            ['last_name' => 'Doe', 'handle' => 'john-doe', 'email' => 'john@example.com'],
            ['first_name'],
        ],
        'missing last_name' => [
            ['first_name' => 'John', 'handle' => 'john-doe', 'email' => 'john@example.com'],
            ['last_name'],
        ],
        'missing email' => [
            ['first_name' => 'John', 'last_name' => 'Doe', 'handle' => 'john-doe'],
            ['email'],
        ],
        'invalid email format' => [
            ['first_name' => 'John', 'last_name' => 'Doe', 'handle' => 'john-doe', 'email' => 'invalid'],
            ['email'],
        ],
        'invalid handle format' => [
            ['first_name' => 'John', 'last_name' => 'Doe', 'email' => 'john@example.com', 'handle' => 'Invalid Handle!'],
            ['handle'],
        ],
        'invalid linkedin_url' => [
            ['first_name' => 'John', 'last_name' => 'Doe', 'handle' => 'john-doe', 'email' => 'john@example.com', 'linkedin_url' => 'not-a-url'],
            ['linkedin_url'],
        ],
        'invalid role' => [
            ['first_name' => 'John', 'last_name' => 'Doe', 'handle' => 'john-doe', 'email' => 'john@example.com', 'roles' => ['NonExistentRole']],
            ['roles.0'],
        ],
        'invalid team_type' => [
            ['first_name' => 'John', 'last_name' => 'Doe', 'handle' => 'john-doe', 'email' => 'john@example.com', 'team_type' => 999],
            ['team_type'],
        ],
    ]);

    test('validates unique email', function () {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['email' => 'existing@example.com']);

        actingAs($admin);

        post(route('staff.users.store'), [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'handle' => 'john-doe',
            'email' => 'existing@example.com',
        ])->assertSessionHasErrors(['email']);
    });

    test('validates unique handle', function () {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['handle' => 'existing-handle']);

        actingAs($admin);

        post(route('staff.users.store'), [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'handle' => 'existing-handle',
        ])->assertSessionHasErrors(['handle']);
    });

    test('validates avatar file type', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        $file = UploadedFile::fake()->create(name: 'document.pdf', mimeType: 'application/pdf');

        post(route('staff.users.store'), [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'handle' => 'john-doe',
            'email' => 'john@example.com',
            'avatar' => $file,
        ])->assertSessionHasErrors(['avatar']);
    });

    test('validates avatar max size', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        $file = UploadedFile::fake()->image(name: 'large-avatar.jpg')->size(kilobytes: 3000);

        post(route('staff.users.store'), [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'handle' => 'john-doe',
            'email' => 'john@example.com',
            'avatar' => $file,
        ])->assertSessionHasErrors(['avatar']);
    });
});
