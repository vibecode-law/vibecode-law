<?php

use App\Models\Course\Course;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

describe('auth', function () {
    test('requires authentication', function () {
        $course = Course::factory()->create();

        get(route('staff.courses.lessons.create', $course))
            ->assertRedirect(route('login'));
    });

    test('allows admin to view the create form', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();

        actingAs($admin);

        get(route('staff.courses.lessons.create', $course))
            ->assertOk();
    });

    test('does not allow moderators to create lessons', function () {
        $moderator = User::factory()->moderator()->create();
        $course = Course::factory()->create();

        actingAs($moderator);

        get(route('staff.courses.lessons.create', $course))
            ->assertForbidden();
    });

    test('does not allow regular users to create lessons', function () {
        /** @var User */
        $user = User::factory()->create();
        $course = Course::factory()->create();

        actingAs($user);

        get(route('staff.courses.lessons.create', $course))
            ->assertForbidden();
    });
});

describe('data', function () {
    test('returns course data for breadcrumb', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();

        actingAs($admin);

        get(route('staff.courses.lessons.create', $course))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('staff-area/courses/lessons/create', shouldExist: false)
                ->has('course', fn (AssertableInertia $data) => $data
                    ->where('id', $course->id)
                    ->where('slug', $course->slug)
                    ->where('title', $course->title)
                )
            );
    });
});
