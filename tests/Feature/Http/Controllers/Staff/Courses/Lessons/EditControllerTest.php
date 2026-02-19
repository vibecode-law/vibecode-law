<?php

use App\Models\Course\Course;
use App\Models\Course\Lesson;
use App\Models\Tag;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

describe('auth', function () {
    test('requires authentication', function () {
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id]);

        get(route('staff.academy.courses.lessons.edit', [$course, $lesson]))
            ->assertRedirect(route('login'));
    });

    test('allows admin to view the edit form', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id]);

        actingAs($admin);

        get(route('staff.academy.courses.lessons.edit', [$course, $lesson]))
            ->assertOk();
    });

    test('does not allow moderators to edit lessons', function () {
        $moderator = User::factory()->moderator()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id]);

        actingAs($moderator);

        get(route('staff.academy.courses.lessons.edit', [$course, $lesson]))
            ->assertForbidden();
    });

    test('does not allow regular users to edit lessons', function () {
        /** @var User */
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id]);

        actingAs($user);

        get(route('staff.academy.courses.lessons.edit', [$course, $lesson]))
            ->assertForbidden();
    });
});

describe('data', function () {
    test('returns correct lesson data', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $instructor = User::factory()->create();
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'gated' => true,
            'allow_preview' => true,
            'publish_date' => '2026-03-15',
        ]);
        $lesson->instructors()->attach($instructor);

        actingAs($admin);

        get(route('staff.academy.courses.lessons.edit', [$course, $lesson]))
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
                    ->where('allow_preview', true)
                    ->where('is_previewable', true)
                    ->where('is_scheduled', false)
                    ->where('publish_date', '2026-03-15')
                    ->where('order', $lesson->order)
                    ->where('asset_id', $lesson->asset_id)
                    ->where('playback_id', $lesson->playback_id)
                    ->where('duration_seconds', $lesson->duration_seconds)
                    ->where('has_vtt_transcript', false)
                    ->where('has_txt_transcript', false)
                    ->where('has_transcript_lines', false)
                    ->where('thumbnail_url', null)
                    ->where('thumbnail_rect_strings', null)
                    ->where('thumbnail_crops', null)
                    ->where('tags', [])
                    ->has('instructors', 1, fn (AssertableInertia $u) => $u
                        ->where('id', $instructor->id)
                        ->where('first_name', $instructor->first_name)
                        ->where('last_name', $instructor->last_name)
                        ->where('handle', $instructor->handle)
                        ->where('organisation', $instructor->organisation)
                        ->where('job_title', $instructor->job_title)
                        ->where('avatar', $instructor->avatar)
                        ->where('linkedin_url', $instructor->linkedin_url)
                        ->where('team_role', $instructor->team_role)
                    )
                )
                ->has('course', fn (AssertableInertia $data) => $data
                    ->where('id', $course->id)
                    ->where('slug', $course->slug)
                    ->where('title', $course->title)
                )
                ->has('availableTags')
            );
    });

    test('returns lesson tags', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $tags = Tag::factory()->count(2)->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id]);
        $lesson->tags()->attach($tags->pluck('id'));

        actingAs($admin);

        get(route('staff.academy.courses.lessons.edit', [$course, $lesson]))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('lesson.tags', 2)
            );
    });

    test('returns available tags', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        Tag::factory()->count(3)->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id]);

        actingAs($admin);

        get(route('staff.academy.courses.lessons.edit', [$course, $lesson]))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('availableTags', 3)
            );
    });

    test('enforces scope binding', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $otherCourse = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $otherCourse->id]);

        actingAs($admin);

        get(route('staff.academy.courses.lessons.edit', [$course, $lesson]))
            ->assertNotFound();
    });
});
