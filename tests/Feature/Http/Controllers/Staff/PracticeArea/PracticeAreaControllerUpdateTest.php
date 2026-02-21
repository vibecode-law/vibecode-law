<?php

use App\Models\PracticeArea;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\put;

describe('auth', function () {
    test('requires authentication', function () {
        $practiceArea = PracticeArea::factory()->create();

        $response = put(route('staff.metadata.practice-areas.update', $practiceArea), [
            'name' => 'Updated Name',
        ]);

        $response->assertRedirect(route('login'));
    });

    test('requires admin privileges', function () {
        /** @var User */
        $user = User::factory()->create();

        $practiceArea = PracticeArea::factory()->create();

        actingAs($user);

        $response = put(route('staff.metadata.practice-areas.update', $practiceArea), [
            'name' => 'Updated Name',
        ]);

        $response->assertForbidden();
    });

    test('allows admin to update practice area', function () {
        $admin = User::factory()->admin()->create();
        $practiceArea = PracticeArea::factory()->create();

        actingAs($admin);

        $response = put(route('staff.metadata.practice-areas.update', $practiceArea), [
            'name' => 'Updated Name',
        ]);

        $response->assertRedirect(route('staff.metadata.practice-areas.index'));
    });
});

describe('validation', function () {
    test('validates practice area data', function (array $data, array $invalidFields) {
        if (isset($data['_setup'])) {
            $data['_setup']();
            unset($data['_setup']);
        }

        $admin = User::factory()->admin()->create();
        $practiceArea = PracticeArea::factory()->create(['name' => 'Original', 'slug' => 'original']);

        actingAs($admin);

        $baseData = [
            'name' => 'Updated Name',
        ];

        $response = put(route('staff.metadata.practice-areas.update', $practiceArea), array_merge($baseData, $data));

        $response->assertInvalid($invalidFields);
    })->with([
        'name is required' => [
            ['name' => null],
            ['name'],
        ],
        'name cannot exceed 255 characters' => [
            ['name' => str_repeat('a', 256)],
            ['name'],
        ],
        'name must be unique' => [
            [
                'name' => 'Existing Area',
                '_setup' => fn () => PracticeArea::factory()->create(['name' => 'Existing Area', 'slug' => 'existing-area']),
            ],
            ['name'],
        ],
    ]);

    test('allows updating with same name', function () {
        $admin = User::factory()->admin()->create();
        $practiceArea = PracticeArea::factory()->create(['name' => 'Original Name', 'slug' => 'original-name']);

        actingAs($admin);

        $response = put(route('staff.metadata.practice-areas.update', $practiceArea), [
            'name' => 'Original Name',
        ]);

        $response->assertRedirect(route('staff.metadata.practice-areas.index'));
    });
});

describe('update', function () {
    test('updates practice area name', function () {
        $admin = User::factory()->admin()->create();
        $practiceArea = PracticeArea::factory()->create(['name' => 'Original', 'slug' => 'original']);

        actingAs($admin);

        put(route('staff.metadata.practice-areas.update', $practiceArea), [
            'name' => 'Updated Name',
        ]);

        assertDatabaseHas('practice_areas', [
            'id' => $practiceArea->id,
            'name' => 'Updated Name',
            'slug' => 'original',
        ]);
    });

    test('does not update slug', function () {
        $admin = User::factory()->admin()->create();
        $practiceArea = PracticeArea::factory()->create(['name' => 'Original', 'slug' => 'original']);

        actingAs($admin);

        put(route('staff.metadata.practice-areas.update', $practiceArea), [
            'name' => 'Updated Name',
        ]);

        assertDatabaseHas('practice_areas', [
            'id' => $practiceArea->id,
            'slug' => 'original',
        ]);
    });
});

describe('response', function () {
    test('redirects to practice areas index', function () {
        $admin = User::factory()->admin()->create();
        $practiceArea = PracticeArea::factory()->create();

        actingAs($admin);

        $response = put(route('staff.metadata.practice-areas.update', $practiceArea), [
            'name' => 'Updated Name',
        ]);

        $response->assertRedirect(route('staff.metadata.practice-areas.index'));
    });

    test('includes success message in session', function () {
        $admin = User::factory()->admin()->create();
        $practiceArea = PracticeArea::factory()->create();

        actingAs($admin);

        $response = put(route('staff.metadata.practice-areas.update', $practiceArea), [
            'name' => 'Updated Name',
        ]);

        $response->assertSessionHas('flash.message', ['message' => 'Practice area updated successfully.', 'type' => 'success']);
    });
});
