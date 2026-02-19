<?php

use App\Models\PracticeArea;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\post;

describe('auth', function () {
    test('requires authentication', function () {
        $response = post(route('staff.metadata.practice-areas.store'), [
            'name' => 'Test Practice Area',
            'slug' => 'test-practice-area',
        ]);

        $response->assertRedirect(route('login'));
    });

    test('requires admin privileges', function () {
        /** @var User */
        $user = User::factory()->create();

        actingAs($user);

        $response = post(route('staff.metadata.practice-areas.store'), [
            'name' => 'Test Practice Area',
            'slug' => 'test-practice-area',
        ]);

        $response->assertForbidden();
    });

    test('allows admin to create practice area', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        $response = post(route('staff.metadata.practice-areas.store'), [
            'name' => 'Test Practice Area',
            'slug' => 'test-practice-area',
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

        actingAs($admin);

        $response = post(route('staff.metadata.practice-areas.store'), $data);

        $response->assertInvalid($invalidFields);
    })->with([
        'name is required' => [
            ['name' => null, 'slug' => 'test-slug'],
            ['name'],
        ],
        'slug is required' => [
            ['name' => 'Test Name', 'slug' => null],
            ['slug'],
        ],
        'name cannot exceed 255 characters' => [
            ['name' => str_repeat('a', 256), 'slug' => 'test-slug'],
            ['name'],
        ],
        'name must be unique' => [
            [
                'name' => 'Existing Area',
                'slug' => 'new-slug',
                '_setup' => fn () => PracticeArea::factory()->create(['name' => 'Existing Area', 'slug' => 'existing-area']),
            ],
            ['name'],
        ],
        'slug must be unique' => [
            [
                'name' => 'New Area',
                'slug' => 'existing-slug',
                '_setup' => fn () => PracticeArea::factory()->create(['name' => 'Other Area', 'slug' => 'existing-slug']),
            ],
            ['slug'],
        ],
        'slug must be alpha_dash' => [
            ['name' => 'Test', 'slug' => 'invalid slug with spaces'],
            ['slug'],
        ],
    ]);
});

describe('creation', function () {
    test('creates practice area with provided slug', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        post(route('staff.metadata.practice-areas.store'), [
            'name' => 'Healthcare Law',
            'slug' => 'health-law',
        ]);

        assertDatabaseHas('practice_areas', [
            'name' => 'Healthcare Law',
            'slug' => 'health-law',
        ]);
    });
});

describe('response', function () {
    test('redirects to practice areas index', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        $response = post(route('staff.metadata.practice-areas.store'), [
            'name' => 'Test Practice Area',
            'slug' => 'test-practice-area',
        ]);

        $response->assertRedirect(route('staff.metadata.practice-areas.index'));
    });

    test('includes success message in session', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        $response = post(route('staff.metadata.practice-areas.store'), [
            'name' => 'Test Practice Area',
            'slug' => 'test-practice-area',
        ]);

        $response->assertSessionHas('flash.message', ['message' => 'Practice area created successfully.', 'type' => 'success']);
    });
});
