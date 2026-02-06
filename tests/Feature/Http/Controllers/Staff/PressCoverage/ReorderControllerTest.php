<?php

use App\Models\PressCoverage;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

describe('auth', function () {
    test('requires authentication', function () {
        $pressCoverage = PressCoverage::factory()->create();

        post(route('staff.press-coverage.reorder'), [
            'items' => [
                ['id' => $pressCoverage->id, 'display_order' => 0],
            ],
        ])->assertRedirect(route('login'));
    });

    test('forbids regular users', function () {
        /** @var User */
        $user = User::factory()->create();
        $pressCoverage = PressCoverage::factory()->create();

        actingAs($user);

        post(route('staff.press-coverage.reorder'), [
            'items' => [
                ['id' => $pressCoverage->id, 'display_order' => 0],
            ],
        ])->assertForbidden();
    });

    test('allows moderators to reorder press coverage', function () {
        $moderator = User::factory()->moderator()->create();
        $pressCoverage = PressCoverage::factory()->create();

        actingAs($moderator);

        post(route('staff.press-coverage.reorder'), [
            'items' => [
                ['id' => $pressCoverage->id, 'display_order' => 0],
            ],
        ])->assertRedirect();
    });
});
