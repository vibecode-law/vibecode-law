<?php

use App\Models\PracticeArea;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

describe('auth', function () {
    test('requires authentication', function () {
        $response = get(route('staff.metadata.practice-areas.index'));

        $response->assertRedirect(route('login'));
    });

    test('allows admin to view the practice areas list', function () {
        /** @var User */
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        $response = get(route('staff.metadata.practice-areas.index'));

        $response->assertOk();
    });

    test('does not allow non-admin users to view practice areas', function () {
        /** @var User */
        $user = User::factory()->create();

        actingAs($user);

        get(route('staff.metadata.practice-areas.index'))
            ->assertForbidden();
    });
});

describe('data', function () {
    test('returns all practice areas', function () {
        $admin = User::factory()->admin()->create();
        PracticeArea::factory()->count(25)->create();

        actingAs($admin);

        $response = get(route('staff.metadata.practice-areas.index'));

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('staff-area/practice-areas/index', shouldExist: false)
            ->has('practiceAreas', 25)
        );
    });

    test('returns practice areas with showcase counts', function () {
        $admin = User::factory()->admin()->create();
        PracticeArea::factory()->create();

        actingAs($admin);

        $response = get(route('staff.metadata.practice-areas.index'));

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('staff-area/practice-areas/index', shouldExist: false)
            ->has('practiceAreas.0', fn (AssertableInertia $data) => $data
                ->has('id')
                ->has('name')
                ->has('slug')
                ->has('showcases_count')
            )
        );
    });

    test('orders practice areas by name', function () {
        $admin = User::factory()->admin()->create();
        PracticeArea::factory()->create(['name' => 'Zebra', 'slug' => 'zebra']);
        PracticeArea::factory()->create(['name' => 'Alpha', 'slug' => 'alpha']);

        actingAs($admin);

        $response = get(route('staff.metadata.practice-areas.index'));

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->where('practiceAreas.0.name', 'Alpha')
            ->where('practiceAreas.1.name', 'Zebra')
        );
    });
});
