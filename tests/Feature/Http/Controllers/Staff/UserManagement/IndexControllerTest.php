<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

describe('auth', function () {
    test('requires authentication', function () {
        $response = get(route('staff.users.index'));

        $response->assertRedirect(route('login'));
    });

    test('allows admin to view the users list', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        $response = get(route('staff.users.index'));

        $response->assertOk();
    });

    test('does not allow moderators to view users', function () {
        $moderator = User::factory()->moderator()->create();

        actingAs($moderator);

        get(route('staff.users.index'))
            ->assertForbidden();
    });

    test('does not allow regular users to view users', function () {
        /** @var User */
        $user = User::factory()->create();

        actingAs($user);

        get(route('staff.users.index'))
            ->assertForbidden();
    });
});

describe('data', function () {
    test('returns paginated users', function () {
        $admin = User::factory()->admin()->create();
        User::factory()->count(30)->create();

        actingAs($admin);

        $response = get(route('staff.users.index'));

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('staff-area/users/index', shouldExist: false)
            ->has('users.data', 25)
            ->has('users.meta')
            ->has('users.links')
        );
    });

    test('returns users with correct structure and values', function () {
        $admin = User::factory()->admin()->create([
            'created_at' => now()->subMinute(),
        ]);
        $user = User::factory()->moderator()->create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'handle' => 'test-user',
            'email' => 'testuser@example.com',
            'organisation' => 'Test Org',
            'created_at' => now(),
        ]);

        actingAs($admin);

        $response = get(route('staff.users.index'));

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('staff-area/users/index', shouldExist: false)
            ->has('users.data.0', fn (AssertableInertia $data) => $data
                ->where('id', $user->id)
                ->where('first_name', 'Test')
                ->where('last_name', 'User')
                ->where('handle', 'test-user')
                ->where('email', 'testuser@example.com')
                ->where('organisation', 'Test Org')
                ->where('avatar', $user->avatar)
                ->where('is_superadmin', false)
                ->where('blocked_from_submissions_at', null)
                ->whereType('created_at', 'string')
                ->where('roles', ['Moderator'])
                ->where('showcases_count', 0)
                ->missing('job_title')
                ->missing('linkedin_url')
                ->missing('bio')
            )
        );
    });

    test('returns available roles', function () {
        $admin = User::factory()->admin()->create();
        User::factory()->moderator()->create(); // Creates the Moderator role

        actingAs($admin);

        $response = get(route('staff.users.index'));

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->where('roles', ['Academy Manager', 'Challenge Manager', 'Marketing Manager', 'Moderator', 'Staff MCP User'])
        );
    });

    test('returns filters with default values', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        $response = get(route('staff.users.index'));

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->where('filters.search', '')
            ->where('filters.role', '')
            ->where('filters.blocked', null)
        );
    });

    test('returns filters with applied values', function () {
        $admin = User::factory()->admin()->create();
        User::factory()->moderator()->create();

        actingAs($admin);

        $response = get(route('staff.users.index', [
            'search' => 'test query',
            'role' => 'Moderator',
            'blocked' => 'true',
        ]));

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->where('filters.search', 'test query')
            ->where('filters.role', 'Moderator')
            ->where('filters.blocked', true)
        );
    });
});

describe('filtering', function () {
    test('can search users by first name', function () {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['first_name' => 'Xanderbert']);
        User::factory()->create(['first_name' => 'Jane']);

        actingAs($admin);

        $response = get(route('staff.users.index', ['search' => 'Xanderbert']));

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->has('users.data', 1)
            ->where('users.data.0.first_name', 'Xanderbert')
        );
    });

    test('can search users by email', function () {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['email' => 'searchable-unique-test@example.com']);
        User::factory()->create(['email' => 'other@example.com']);

        actingAs($admin);

        $response = get(route('staff.users.index', ['search' => 'searchable-unique-test']));

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->has('users.data', 1)
            ->where('users.data.0.email', 'searchable-unique-test@example.com')
        );
    });

    test('can filter users by role', function () {
        $admin = User::factory()->admin()->create();
        $moderator = User::factory()->moderator()->create(['email' => 'moderator@example.com']);
        User::factory()->create(['email' => 'regular@example.com']);

        actingAs($admin);

        $response = get(route('staff.users.index', ['role' => 'Moderator']));

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->has('users.data', 1)
            ->where('users.data.0.id', $moderator->id)
            ->where('users.data.0.email', 'moderator@example.com')
            ->where('users.data.0.roles', ['Moderator'])
        );
    });

    test('can filter blocked users', function () {
        $admin = User::factory()->admin()->create();
        $blockedUser = User::factory()->blockedFromSubmissions()->create(['email' => 'blocked@example.com']);
        User::factory()->create(['email' => 'unblocked@example.com']);

        actingAs($admin);

        $response = get(route('staff.users.index', ['blocked' => 'true']));

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->has('users.data', 1)
            ->where('users.data.0.id', $blockedUser->id)
            ->where('users.data.0.email', 'blocked@example.com')
            ->whereType('users.data.0.blocked_from_submissions_at', 'string')
        );
    });

    test('can filter non-blocked users', function () {
        $admin = User::factory()->admin()->create();
        User::factory()->blockedFromSubmissions()->create(['email' => 'blocked@example.com']);
        User::factory()->create(['email' => 'unblocked@example.com']);

        actingAs($admin);

        $response = get(route('staff.users.index', ['blocked' => 'false']));

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->has('users.data', 2)
            ->where('users.data.0.blocked_from_submissions_at', null)
            ->where('users.data.1.blocked_from_submissions_at', null)
        );
    });
});
