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
        $lesson = Lesson::factory()->create(['course_id' => $course->id]);

        get(route('staff.courses.lessons.edit', [$course, $lesson]))
            ->assertRedirect(route('login'));
    });

    test('allows admin to view the edit form', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id]);

        actingAs($admin);

        get(route('staff.courses.lessons.edit', [$course, $lesson]))
            ->assertOk();
    });

    test('does not allow moderators to edit lessons', function () {
        $moderator = User::factory()->moderator()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id]);

        actingAs($moderator);

        get(route('staff.courses.lessons.edit', [$course, $lesson]))
            ->assertForbidden();
    });

    test('does not allow regular users to edit lessons', function () {
        /** @var User */
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id]);

        actingAs($user);

        get(route('staff.courses.lessons.edit', [$course, $lesson]))
            ->assertForbidden();
    });
});

describe('data', function () {
    test('returns correct lesson data', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'gated' => true,
        ]);

        actingAs($admin);

        get(route('staff.courses.lessons.edit', [$course, $lesson]))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('staff-area/courses/lessons/edit', shouldExist: false)
                ->has('lesson', fn (AssertableInertia $data) => $data
                    ->where('id', $lesson->id)
                    ->where('slug', $lesson->slug)
                    ->where('title', $lesson->title)
                    ->where('tagline', $lesson->tagline)
                    ->where('description', $lesson->description)
                    ->where('learning_objectives', $lesson->learning_objectives)
                    ->where('copy', $lesson->copy)
                    ->where('gated', true)
                    ->where('visible', $lesson->visible)
                    ->where('publish_date', $lesson->publish_date?->format('Y-m-d'))
                    ->where('order', $lesson->order)
                    ->where('asset_id', $lesson->asset_id)
                    ->where('thumbnail_url', null)
                    ->where('thumbnail_rect_strings', null)
                    ->where('thumbnail_crops', null)
                )
                ->has('course', fn (AssertableInertia $data) => $data
                    ->where('id', $course->id)
                    ->where('slug', $course->slug)
                    ->where('title', $course->title)
                )
            );
    });

    test('enforces scope binding', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $otherCourse = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $otherCourse->id]);

        actingAs($admin);

        get(route('staff.courses.lessons.edit', [$course, $lesson]))
            ->assertNotFound();
    });
});
