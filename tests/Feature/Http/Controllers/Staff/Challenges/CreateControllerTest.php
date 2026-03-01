<?php

use App\Enums\ChallengeVisibility;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

describe('auth', function () {
    test('requires authentication', function () {
        get(route('staff.challenges.create'))
            ->assertRedirect(route('login'));
    });

    test('allows admin to view the create form', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        get(route('staff.challenges.create'))
            ->assertOk();
    });

    test('does not allow moderators to create challenges', function () {
        $moderator = User::factory()->moderator()->create();

        actingAs($moderator);

        get(route('staff.challenges.create'))
            ->assertForbidden();
    });

    test('does not allow regular users to create challenges', function () {
        /** @var User */
        $user = User::factory()->create();

        actingAs($user);

        get(route('staff.challenges.create'))
            ->assertForbidden();
    });
});

describe('data', function () {
    test('renders the correct component', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        get(route('staff.challenges.create'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('staff-area/challenges/create', shouldExist: false)
            );
    });

    test('returns visibility options from enum', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        get(route('staff.challenges.create'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('visibilityOptions', count(ChallengeVisibility::cases()))
                ->has('visibilityOptions.0', fn (AssertableInertia $vo) => $vo
                    ->where('value', (string) ChallengeVisibility::Public->value)
                    ->where('label', ChallengeVisibility::Public->label())
                    ->has('name')
                )
                ->has('visibilityOptions.1', fn (AssertableInertia $vo) => $vo
                    ->where('value', (string) ChallengeVisibility::InviteToSubmit->value)
                    ->where('label', ChallengeVisibility::InviteToSubmit->label())
                    ->has('name')
                )
                ->has('visibilityOptions.2', fn (AssertableInertia $vo) => $vo
                    ->where('value', (string) ChallengeVisibility::InviteToViewAndSubmit->value)
                    ->where('label', ChallengeVisibility::InviteToViewAndSubmit->label())
                    ->has('name')
                )
            );
    });
});
