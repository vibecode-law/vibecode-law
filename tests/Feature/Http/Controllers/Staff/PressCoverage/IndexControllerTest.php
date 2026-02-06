<?php

use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

describe('auth', function () {
    test('requires authentication', function () {
        get(route('staff.press-coverage.index'))
            ->assertRedirect(route('login'));
    });

    test('forbids regular users', function () {
        /** @var User */
        $user = User::factory()->create();

        actingAs($user);

        get(route('staff.press-coverage.index'))
            ->assertForbidden();
    });

    test('allows moderators to view press coverage index', function () {
        $moderator = User::factory()->moderator()->create();

        actingAs($moderator);

        get(route('staff.press-coverage.index'))
            ->assertSuccessful();
    });
});
