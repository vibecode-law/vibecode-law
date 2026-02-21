<?php

use App\Models\Course\Course;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

describe('auth', function () {
    test('requires authentication', function () {
        $course = Course::factory()->create();

        post(route('staff.academy.courses.reorder'), [
            'items' => [
                ['id' => $course->id, 'order' => 0],
            ],
        ])->assertRedirect(route('login'));
    });

    test('forbids regular users', function () {
        /** @var User */
        $user = User::factory()->create();
        $course = Course::factory()->create();

        actingAs($user);

        post(route('staff.academy.courses.reorder'), [
            'items' => [
                ['id' => $course->id, 'order' => 0],
            ],
        ])->assertForbidden();
    });

    test('allows admins to reorder courses', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();

        actingAs($admin);

        post(route('staff.academy.courses.reorder'), [
            'items' => [
                ['id' => $course->id, 'order' => 0],
            ],
        ])->assertRedirect();
    });
});

describe('reordering', function () {
    test('updates order for multiple courses', function () {
        $admin = User::factory()->admin()->create();
        $first = Course::factory()->create(['order' => 0]);
        $second = Course::factory()->create(['order' => 1]);
        $third = Course::factory()->create(['order' => 2]);

        actingAs($admin);

        post(route('staff.academy.courses.reorder'), [
            'items' => [
                ['id' => $first->id, 'order' => 2],
                ['id' => $second->id, 'order' => 0],
                ['id' => $third->id, 'order' => 1],
            ],
        ])->assertRedirect();

        expect($first->refresh()->order)->toBe(2)
            ->and($second->refresh()->order)->toBe(0)
            ->and($third->refresh()->order)->toBe(1);
    });
});

describe('validation', function () {
    test('validates required and invalid data', function (array $data, array $invalidFields) {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        post(route('staff.academy.courses.reorder'), $data)
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
            ['items' => [['order' => 0]]],
            ['items.0.id'],
        ],
        'items.*.id must exist' => [
            ['items' => [['id' => 99999, 'order' => 0]]],
            ['items.0.id'],
        ],
        'items.*.order is required' => [
            ['items' => [['id' => 1]]],
            ['items.0.order'],
        ],
        'items.*.order must be integer' => [
            ['items' => [['id' => 1, 'order' => 'abc']]],
            ['items.0.order'],
        ],
        'items.*.order min 0' => [
            ['items' => [['id' => 1, 'order' => -1]]],
            ['items.0.order'],
        ],
    ]);
});
