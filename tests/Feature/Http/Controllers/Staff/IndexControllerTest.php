<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

describe('auth', function () {
    test('requires authentication', function () {
        get(route('staff.index'))
            ->assertRedirect(route('login'));
    });

    test('forbids regular users', function () {
        /** @var User */
        $user = User::factory()->create();

        actingAs($user);

        get(route('staff.index'))
            ->assertForbidden();
    });

    test('allows superadmins', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        get(route('staff.index'))
            ->assertSuccessful();
    });

    test('allows moderators', function () {
        $moderator = User::factory()->moderator()->create();

        actingAs($moderator);

        get(route('staff.index'))
            ->assertSuccessful();
    });

    test('allows marketing managers', function () {
        $user = User::factory()->marketingManager()->create();

        actingAs($user);

        get(route('staff.index'))
            ->assertSuccessful();
    });

    test('allows academy managers', function () {
        $user = User::factory()->academyManager()->create();

        actingAs($user);

        get(route('staff.index'))
            ->assertSuccessful();
    });

    test('allows challenge managers', function () {
        $user = User::factory()->challengeManager()->create();

        actingAs($user);

        get(route('staff.index'))
            ->assertSuccessful();
    });
});

describe('data', function () {
    test('renders the correct Inertia component', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        get(route('staff.index'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('staff-area/index')
            );
    });
});
