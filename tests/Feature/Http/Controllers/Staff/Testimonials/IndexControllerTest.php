<?php

use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

describe('auth', function () {
    test('requires authentication', function () {
        get(route('staff.testimonials.index'))
            ->assertRedirect(route('login'));
    });

    test('forbids regular users', function () {
        /** @var User */
        $user = User::factory()->create();

        actingAs($user);

        get(route('staff.testimonials.index'))
            ->assertForbidden();
    });

    test('allows moderators to view testimonials index', function () {
        $moderator = User::factory()->moderator()->create();

        actingAs($moderator);

        get(route('staff.testimonials.index'))
            ->assertSuccessful();
    });
});
