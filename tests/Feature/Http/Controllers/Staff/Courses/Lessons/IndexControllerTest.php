<?php

use App\Models\Course\Course;
use App\Models\Course\Lesson;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

describe('auth', function () {
    test('requires authentication', function () {
        $course = Course::factory()->create();

        get(route('staff.academy.courses.lessons.index', $course))
            ->assertRedirect(route('login'));
    });

    test('allows admin to view the lessons list', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();

        actingAs($admin);

        get(route('staff.academy.courses.lessons.index', $course))
            ->assertOk();
    });

    test('does not allow moderators to view lessons', function () {
        $moderator = User::factory()->moderator()->create();
        $course = Course::factory()->create();

        actingAs($moderator);

        get(route('staff.academy.courses.lessons.index', $course))
            ->assertForbidden();
    });

    test('does not allow regular users to view lessons', function () {
        /** @var User */
        $user = User::factory()->create();
        $course = Course::factory()->create();

        actingAs($user);

        get(route('staff.academy.courses.lessons.index', $course))
            ->assertForbidden();
    });
});

describe('data', function () {
    test('returns lessons for the course', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        Lesson::factory()->count(3)->create(['course_id' => $course->id]);

        // Lesson for another course should not appear
        Lesson::factory()->create();

        actingAs($admin);

        get(route('staff.academy.courses.lessons.index', $course))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('staff-area/courses/lessons/index', shouldExist: false)
                ->has('lessons', 3)
            );
    });

    test('returns lessons with correct structure and values', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'gated' => true,
            'allow_preview' => true,
            'publish_date' => null,
        ]);

        actingAs($admin);

        get(route('staff.academy.courses.lessons.index', $course))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('lessons.0', fn (AssertableInertia $data) => $data
                    ->where('id', $lesson->id)
                    ->where('slug', $lesson->slug)
                    ->where('title', $lesson->title)
                    ->where('tagline', $lesson->tagline)
                    ->where('gated', true)
                    ->where('is_previewable', true)
                    ->where('is_scheduled', false)
                    ->where('order', $lesson->order)
                    ->where('thumbnail_url', null)
                    ->missing('description')
                    ->missing('learning_objectives')
                    ->missing('copy')
                )
            );
    });

    test('returns course data for breadcrumb', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();

        actingAs($admin);

        get(route('staff.academy.courses.lessons.index', $course))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('course', fn (AssertableInertia $data) => $data
                    ->where('id', $course->id)
                    ->where('slug', $course->slug)
                    ->where('title', $course->title)
                )
            );
    });

    test('orders lessons by order ascending', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $second = Lesson::factory()->create(['course_id' => $course->id, 'order' => 2]);
        $first = Lesson::factory()->create(['course_id' => $course->id, 'order' => 1]);

        actingAs($admin);

        get(route('staff.academy.courses.lessons.index', $course))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('lessons.0.id', $first->id)
                ->where('lessons.1.id', $second->id)
            );
    });
});
