<?php

use App\Models\PressCoverage;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\delete;

describe('auth', function () {
    test('requires authentication', function () {
        $pressCoverage = PressCoverage::factory()->create();

        delete(route('staff.press-coverage.destroy', $pressCoverage))
            ->assertRedirect(route('login'));
    });

    test('forbids regular users', function () {
        /** @var User */
        $user = User::factory()->create();
        $pressCoverage = PressCoverage::factory()->create();

        actingAs($user);

        delete(route('staff.press-coverage.destroy', $pressCoverage))
            ->assertForbidden();
    });

    test('allows moderators to delete press coverage', function () {
        $moderator = User::factory()->moderator()->create();
        $pressCoverage = PressCoverage::factory()->create();

        actingAs($moderator);

        delete(route('staff.press-coverage.destroy', $pressCoverage))
            ->assertRedirect();
    });
});
