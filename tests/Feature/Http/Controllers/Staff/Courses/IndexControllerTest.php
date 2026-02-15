<?php

use App\Models\Course\Course;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

describe('auth', function () {
    test('requires authentication', function () {
        get(route('staff.courses.index'))
            ->assertRedirect(route('login'));
    });

    test('allows admin to view the courses list', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        get(route('staff.courses.index'))
            ->assertOk();
    });

    test('does not allow moderators to view courses', function () {
        $moderator = User::factory()->moderator()->create();

        actingAs($moderator);

        get(route('staff.courses.index'))
            ->assertForbidden();
    });

    test('does not allow regular users to view courses', function () {
        /** @var User */
        $user = User::factory()->create();

        actingAs($user);

        get(route('staff.courses.index'))
            ->assertForbidden();
    });
});

describe('data', function () {
    test('returns all courses', function () {
        $admin = User::factory()->admin()->create();
        Course::factory()->count(30)->create();

        actingAs($admin);

        get(route('staff.courses.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('staff-area/courses/index', shouldExist: false)
                ->has('courses', 30)
            );
    });

    test('returns courses with correct structure and values', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create([
            'visible' => true,
            'is_featured' => true,
        ]);

        actingAs($admin);

        get(route('staff.courses.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('staff-area/courses/index', shouldExist: false)
                ->has('courses.0', fn (AssertableInertia $data) => $data
                    ->where('id', $course->id)
                    ->where('slug', $course->slug)
                    ->where('title', $course->title)
                    ->where('tagline', $course->tagline)
                    ->where('visible', true)
                    ->where('is_featured', true)
                    ->where('order', $course->order)
                    ->where('lessons_count', 0)
                    ->where('thumbnail_url', null)
                    ->missing('description')
                    ->missing('description_html')
                    ->missing('learning_objectives')
                )
            );
    });

    test('returns lessons count', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        \App\Models\Course\Lesson::factory()->count(3)->create(['course_id' => $course->id]);

        actingAs($admin);

        get(route('staff.courses.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('courses.0.lessons_count', 3)
            );
    });

    test('orders courses by order ascending', function () {
        $admin = User::factory()->admin()->create();
        $second = Course::factory()->create(['order' => 2]);
        $first = Course::factory()->create(['order' => 1]);

        actingAs($admin);

        get(route('staff.courses.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('courses.0.id', $first->id)
                ->where('courses.1.id', $second->id)
            );
    });
});
