<?php

use App\Models\User;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

describe('auth', function () {
    test('requires authentication', function () {
        get(route('staff.users.create'))
            ->assertRedirect(route('login'));
    });

    test('allows admin to view create user page', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        get(route('staff.users.create'))
            ->assertOk();
    });

    test('does not allow moderators to view create user page', function () {
        $moderator = User::factory()->moderator()->create();

        actingAs($moderator);

        get(route('staff.users.create'))
            ->assertForbidden();
    });

    test('does not allow regular users to view create user page', function () {
        $user = User::factory()->create();

        actingAs($user);

        get(route('staff.users.create'))
            ->assertForbidden();
    });
});

describe('create page', function () {
    test('renders the create user page', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        get(route('staff.users.create'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('staff-area/users/create')
                ->has('roles')
                ->has('teamTypes')
            );
    });

    test('includes available roles', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        get(route('staff.users.create'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('roles')
                ->where('roles', fn ($roles) => is_array($roles) || $roles instanceof Collection)
            );
    });

    test('includes team types', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        get(route('staff.users.create'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('teamTypes', 2)
                ->where('teamTypes.0.value', 1)
                ->where('teamTypes.0.label', 'Core Team')
                ->where('teamTypes.1.value', 2)
                ->where('teamTypes.1.label', 'Collaborator')
            );
    });
});
