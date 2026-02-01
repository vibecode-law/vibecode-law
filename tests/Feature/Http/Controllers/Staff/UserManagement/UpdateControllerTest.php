<?php

use App\Enums\TeamType;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\patch;

describe('auth', function () {
    test('requires authentication', function () {
        $user = User::factory()->create();

        $response = patch(route('staff.users.update', $user), [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'handle' => 'updated-name',
            'email' => 'updated@example.com',
        ]);

        $response->assertRedirect(route('login'));
    });

    test('allows admin to update users', function () {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        actingAs($admin);

        $response = patch(route('staff.users.update', $user), [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'handle' => 'updated-name',
            'email' => 'updated@example.com',
        ]);

        $response->assertRedirect(route('staff.users.edit', $user->fresh()));
    });

    test('does not allow moderators to update users', function () {
        $moderator = User::factory()->moderator()->create();
        $user = User::factory()->create();

        actingAs($moderator);

        patch(route('staff.users.update', $user), [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'handle' => 'updated-name',
            'email' => 'updated@example.com',
        ])->assertForbidden();
    });

    test('does not allow regular users to update users', function () {
        /** @var User */
        $regularUser = User::factory()->create();
        $user = User::factory()->create();

        actingAs($regularUser);

        patch(route('staff.users.update', $user), [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'handle' => 'updated-name',
            'email' => 'updated@example.com',
        ])->assertForbidden();
    });
});

describe('update', function () {
    test('updates user details', function () {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        actingAs($admin);

        patch(route('staff.users.update', $user), [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'handle' => 'updated-name',
            'email' => 'updated@example.com',
            'organisation' => 'Updated Org',
            'job_title' => 'Updated Title',
            'bio' => 'Updated bio',
            'linkedin_url' => 'https://linkedin.com/in/updated',
        ]);

        assertDatabaseHas('users', [
            'id' => $user->id,
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'handle' => 'updated-name',
            'email' => 'updated@example.com',
            'organisation' => 'Updated Org',
            'job_title' => 'Updated Title',
            'bio' => 'Updated bio',
            'linkedin_url' => 'https://linkedin.com/in/updated',
        ]);
    });

    test('syncs user roles', function () {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        actingAs($admin);

        patch(route('staff.users.update', $user), [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'handle' => $user->handle,
            'email' => $user->email,
            'roles' => ['Moderator'],
        ]);

        expect($user->fresh()->hasRole('Moderator'))->toBeTrue();
    });

    test('can remove all roles', function () {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->moderator()->create();

        expect($user->hasRole('Moderator'))->toBeTrue();

        actingAs($admin);

        patch(route('staff.users.update', $user), [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'handle' => $user->handle,
            'email' => $user->email,
            'roles' => [],
        ]);

        expect($user->fresh()->hasRole('Moderator'))->toBeFalse();
    });

    test('updates team membership', function () {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        actingAs($admin);

        patch(route('staff.users.update', $user), [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'handle' => $user->handle,
            'email' => $user->email,
            'team_type' => TeamType::CoreTeam->value,
            'team_role' => 'Lead Developer',
        ]);

        assertDatabaseHas('users', [
            'id' => $user->id,
            'team_type' => TeamType::CoreTeam->value,
            'team_role' => 'Lead Developer',
        ]);
    });

    test('can remove team membership', function () {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->coreTeam(role: 'Developer')->create();

        expect($user->team_type)->toBe(TeamType::CoreTeam);

        actingAs($admin);

        patch(route('staff.users.update', $user), [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'handle' => $user->handle,
            'email' => $user->email,
            'team_type' => '',
            'team_role' => '',
        ]);

        $user->refresh();

        expect($user->team_type)->toBeNull();
        expect($user->team_role)->toBeNull();
    });

    test('uploads user avatar', function () {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        actingAs($admin);

        $avatar = UploadedFile::fake()->image(name: 'avatar.jpg', width: 200, height: 200);

        patch(route('staff.users.update', $user), [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'handle' => $user->handle,
            'email' => $user->email,
            'avatar' => $avatar,
        ]);

        $user->refresh();

        expect($user->avatar_path)->not->toBeNull();
        Storage::disk('public')->assertExists($user->avatar_path);
    });

    test('replaces existing avatar when uploading new one', function () {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $oldAvatarPath = 'users/avatars/old-avatar.jpg';
        Storage::disk('public')->put(path: $oldAvatarPath, contents: 'old-avatar');
        $user->avatar_path = $oldAvatarPath;
        $user->save();

        actingAs($admin);

        $avatar = UploadedFile::fake()->image(name: 'new-avatar.jpg', width: 200, height: 200);

        patch(route('staff.users.update', $user), [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'handle' => $user->handle,
            'email' => $user->email,
            'avatar' => $avatar,
        ]);

        $user->refresh();

        expect($user->avatar_path)->not->toBe($oldAvatarPath);
        Storage::disk('public')->assertMissing($oldAvatarPath);
        Storage::disk('public')->assertExists($user->avatar_path);
    });

    test('removes user avatar', function () {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $avatarPath = 'users/avatars/avatar.jpg';
        Storage::disk('public')->put(path: $avatarPath, contents: 'avatar');
        $user->avatar_path = $avatarPath;
        $user->save();

        actingAs($admin);

        patch(route('staff.users.update', $user), [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'handle' => $user->handle,
            'email' => $user->email,
            'remove_avatar' => true,
        ]);

        $user->refresh();

        expect($user->avatar_path)->toBeNull();
        Storage::disk('public')->assertMissing($avatarPath);
    });

    test('opts user out of marketing', function () {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['marketing_opt_out_at' => null]);

        actingAs($admin);

        patch(route('staff.users.update', $user), [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'handle' => $user->handle,
            'email' => $user->email,
            'marketing_opt_out' => true,
        ]);

        $user->refresh();

        expect($user->marketing_opt_out_at)->not->toBeNull();
    });

    test('opts user back into marketing', function () {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->marketingOptedOut()->create();

        expect($user->marketing_opt_out_at)->not->toBeNull();

        actingAs($admin);

        patch(route('staff.users.update', $user), [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'handle' => $user->handle,
            'email' => $user->email,
            'marketing_opt_out' => false,
        ]);

        $user->refresh();

        expect($user->marketing_opt_out_at)->toBeNull();
    });
});

describe('validation', function () {
    test('validates required fields', function ($data, $invalid) {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        actingAs($admin);

        patch(route('staff.users.update', $user), $data)
            ->assertSessionHasErrors($invalid);
    })->with([
        'missing first_name' => [
            ['last_name' => 'Name', 'handle' => 'test-user', 'email' => 'test@example.com'],
            ['first_name'],
        ],
        'missing last_name' => [
            ['first_name' => 'Test', 'handle' => 'test-user', 'email' => 'test@example.com'],
            ['last_name'],
        ],
        'missing handle' => [
            ['first_name' => 'Test', 'last_name' => 'Name', 'email' => 'test@example.com'],
            ['handle'],
        ],
        'missing email' => [
            ['first_name' => 'Test', 'last_name' => 'Name', 'handle' => 'test-user'],
            ['email'],
        ],
        'invalid email format' => [
            ['first_name' => 'Test', 'last_name' => 'Name', 'handle' => 'test-user', 'email' => 'invalid'],
            ['email'],
        ],
        'invalid handle format' => [
            ['first_name' => 'Test', 'last_name' => 'Name', 'handle' => 'Invalid Handle!', 'email' => 'test@example.com'],
            ['handle'],
        ],
        'invalid linkedin_url' => [
            ['first_name' => 'Test', 'last_name' => 'Name', 'handle' => 'test-user', 'email' => 'test@example.com', 'linkedin_url' => 'not-a-url'],
            ['linkedin_url'],
        ],
        'invalid role' => [
            ['first_name' => 'Test', 'last_name' => 'Name', 'handle' => 'test-user', 'email' => 'test@example.com', 'roles' => ['NonExistentRole']],
            ['roles.0'],
        ],
        'invalid team_type' => [
            ['first_name' => 'Test', 'last_name' => 'Name', 'handle' => 'test-user', 'email' => 'test@example.com', 'team_type' => 999],
            ['team_type'],
        ],
    ]);

    test('validates unique email', function () {
        $admin = User::factory()->admin()->create();
        $existingUser = User::factory()->create(['email' => 'existing@example.com']);
        $user = User::factory()->create();

        actingAs($admin);

        patch(route('staff.users.update', $user), [
            'first_name' => 'Test',
            'last_name' => 'Name',
            'handle' => 'test-user',
            'email' => 'existing@example.com',
        ])->assertSessionHasErrors(['email']);
    });

    test('validates unique handle', function () {
        $admin = User::factory()->admin()->create();
        $existingUser = User::factory()->create(['handle' => 'existing-handle']);
        $user = User::factory()->create();

        actingAs($admin);

        patch(route('staff.users.update', $user), [
            'first_name' => 'Test',
            'last_name' => 'Name',
            'handle' => 'existing-handle',
            'email' => 'test@example.com',
        ])->assertSessionHasErrors(['handle']);
    });

    test('allows keeping same email', function () {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['email' => 'same@example.com']);

        actingAs($admin);

        patch(route('staff.users.update', $user), [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'handle' => $user->handle,
            'email' => 'same@example.com',
        ])->assertSessionDoesntHaveErrors(['email']);
    });

    test('allows keeping same handle', function () {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['handle' => 'my-handle']);

        actingAs($admin);

        patch(route('staff.users.update', $user), [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'handle' => 'my-handle',
            'email' => $user->email,
        ])->assertSessionDoesntHaveErrors(['handle']);
    });

    test('validates avatar file type', function () {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        actingAs($admin);

        $file = UploadedFile::fake()->create(name: 'document.pdf', mimeType: 'application/pdf');

        patch(route('staff.users.update', $user), [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'handle' => $user->handle,
            'email' => $user->email,
            'avatar' => $file,
        ])->assertSessionHasErrors(['avatar']);
    });

    test('validates avatar max size', function () {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        actingAs($admin);

        $file = UploadedFile::fake()->image(name: 'large-avatar.jpg')->size(kilobytes: 3000);

        patch(route('staff.users.update', $user), [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'handle' => $user->handle,
            'email' => $user->email,
            'avatar' => $file,
        ])->assertSessionHasErrors(['avatar']);
    });
});
