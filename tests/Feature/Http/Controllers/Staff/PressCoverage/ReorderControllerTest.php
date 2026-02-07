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

describe('reordering', function () {
    test('updates display_order for multiple items', function () {
        $moderator = User::factory()->moderator()->create();
        $first = PressCoverage::factory()->create(['display_order' => 0]);
        $second = PressCoverage::factory()->create(['display_order' => 1]);
        $third = PressCoverage::factory()->create(['display_order' => 2]);

        actingAs($moderator);

        post(route('staff.press-coverage.reorder'), [
            'items' => [
                ['id' => $first->id, 'display_order' => 2],
                ['id' => $second->id, 'display_order' => 0],
                ['id' => $third->id, 'display_order' => 1],
            ],
        ])->assertRedirect();

        expect($first->refresh()->display_order)->toBe(2)
            ->and($second->refresh()->display_order)->toBe(0)
            ->and($third->refresh()->display_order)->toBe(1);
    });
});

describe('validation', function () {
    test('validates required and invalid data', function (array $data, array $invalidFields) {
        $moderator = User::factory()->moderator()->create();

        actingAs($moderator);

        post(route('staff.press-coverage.reorder'), $data)
            ->assertInvalid($invalidFields);
    })->with([
        'items is required' => [
            [],
            ['items'],
        ],
        'items must be an array' => [
            ['items' => 'not-array'],
            ['items'],
        ],
        'items.*.id is required' => [
            ['items' => [['display_order' => 0]]],
            ['items.0.id'],
        ],
        'items.*.id must exist' => [
            ['items' => [['id' => 99999, 'display_order' => 0]]],
            ['items.0.id'],
        ],
        'items.*.display_order is required' => [
            ['items' => [['id' => 1]]],
            ['items.0.display_order'],
        ],
        'items.*.display_order must be integer' => [
            ['items' => [['id' => 1, 'display_order' => 'abc']]],
            ['items.0.display_order'],
        ],
        'items.*.display_order min 0' => [
            ['items' => [['id' => 1, 'display_order' => -1]]],
            ['items.0.display_order'],
        ],
    ]);
});
