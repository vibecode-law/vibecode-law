<?php

use App\Enums\TeamType;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

describe('auth', function () {
    test('requires authentication', function () {
        $user = User::factory()->create();

        $response = get(route('staff.users.edit', $user));

        $response->assertRedirect(route('login'));
    });

    test('allows admin to view edit page', function () {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        actingAs($admin);

        $response = get(route('staff.users.edit', $user));

        $response->assertOk();
    });

    test('does not allow moderators to view edit page', function () {
        $moderator = User::factory()->moderator()->create();
        $user = User::factory()->create();

        actingAs($moderator);

        get(route('staff.users.edit', $user))
            ->assertForbidden();
    });

    test('does not allow regular users to view edit page', function () {
        /** @var User */
        $regularUser = User::factory()->create();
        $user = User::factory()->create();

        actingAs($regularUser);

        get(route('staff.users.edit', $user))
            ->assertForbidden();
    });
});

describe('data', function () {
    test('returns user data with correct structure and values', function () {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->moderator()->coreTeam(role: 'Lead Developer')->create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'handle' => 'test-user',
            'email' => 'testuser@example.com',
            'organisation' => 'Test Org',
            'job_title' => 'Developer',
            'linkedin_url' => 'https://linkedin.com/in/testuser',
            'bio' => 'A test bio',
        ]);

        actingAs($admin);

        $response = get(route('staff.users.edit', $user));

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('staff-area/users/edit', shouldExist: false)
            ->has('user', fn (AssertableInertia $data) => $data
                ->where('id', $user->id)
                ->where('first_name', 'Test')
                ->where('last_name', 'User')
                ->where('handle', 'test-user')
                ->where('email', 'testuser@example.com')
                ->where('organisation', 'Test Org')
                ->where('job_title', 'Developer')
                ->where('avatar', $user->avatar)
                ->where('linkedin_url', 'https://linkedin.com/in/testuser')
                ->where('bio', 'A test bio')
                ->where('is_admin', false)
                ->where('blocked_from_submissions_at', null)
                ->whereType('created_at', 'string')
                ->where('team_type', TeamType::CoreTeam->value)
                ->where('team_role', 'Lead Developer')
                ->where('roles', ['Moderator'])
                ->where('showcases_count', 0)
                ->where('marketing_opt_out_at', null)
            )
        );
    });

    test('returns available roles', function () {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->moderator()->create(); // Creates the Moderator role

        actingAs($admin);

        $response = get(route('staff.users.edit', $user));

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->where('roles', ['Moderator'])
        );
    });

    test('returns team types', function () {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        actingAs($admin);

        get(route('staff.users.edit', $user))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('teamTypes', 2)
                ->where('teamTypes.0.value', 1)
                ->where('teamTypes.0.label', 'Core Team')
                ->where('teamTypes.1.value', 2)
                ->where('teamTypes.1.label', 'Collaborator')
            );
    });
});
