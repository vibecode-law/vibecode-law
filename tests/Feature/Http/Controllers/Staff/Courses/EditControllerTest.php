<?php

use App\Enums\ExperienceLevel;
use App\Models\Course\Course;
use App\Models\Tag;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

describe('auth', function () {
    test('requires authentication', function () {
        $course = Course::factory()->create();

        get(route('staff.academy.courses.edit', $course))
            ->assertRedirect(route('login'));
    });

    test('allows admin to view the edit form', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();

        actingAs($admin);

        get(route('staff.academy.courses.edit', $course))
            ->assertOk();
    });

    test('does not allow moderators to edit courses', function () {
        $moderator = User::factory()->moderator()->create();
        $course = Course::factory()->create();

        actingAs($moderator);

        get(route('staff.academy.courses.edit', $course))
            ->assertForbidden();
    });

    test('does not allow regular users to edit courses', function () {
        /** @var User */
        $user = User::factory()->create();
        $course = Course::factory()->create();

        actingAs($user);

        get(route('staff.academy.courses.edit', $course))
            ->assertForbidden();
    });
});

describe('data', function () {
    test('returns correct course data', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create([
            'allow_preview' => true,
            'is_featured' => true,
            'experience_level' => ExperienceLevel::Advanced,
            'publish_date' => '2026-03-15',
        ]);

        actingAs($admin);

        get(route('staff.academy.courses.edit', $course))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('staff-area/courses/edit', shouldExist: false)
                ->has('course', fn (AssertableInertia $data) => $data
                    ->where('id', $course->id)
                    ->where('slug', $course->slug)
                    ->where('title', $course->title)
                    ->where('tagline', $course->tagline)
                    ->where('description', $course->description)
                    ->where('learning_objectives', $course->learning_objectives)
                    ->where('experience_level.value', (string) ExperienceLevel::Advanced->value)
                    ->where('experience_level.label', ExperienceLevel::Advanced->label())
                    ->where('allow_preview', true)
                    ->where('is_previewable', true)
                    ->where('is_scheduled', false)
                    ->where('is_featured', true)
                    ->where('publish_date', '2026-03-15')
                    ->where('order', $course->order)
                    ->where('thumbnail_url', null)
                    ->where('thumbnail_rect_strings', null)
                    ->where('thumbnail_crops', null)
                    ->where('lessons_count', 0)
                    ->where('tags', [])
                    ->where('lessons', [])
                )
                ->has('experienceLevels', count(ExperienceLevel::cases()))
                ->has('availableTags')
            );
    });

    test('returns course tags', function () {
        $admin = User::factory()->admin()->create();
        $tags = Tag::factory()->count(2)->create();
        $course = Course::factory()->create();
        $course->tags()->attach($tags->pluck('id'));

        actingAs($admin);

        get(route('staff.academy.courses.edit', $course))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('course.tags', 2)
            );
    });

    test('returns available tags', function () {
        $admin = User::factory()->admin()->create();
        Tag::factory()->count(3)->create();
        $course = Course::factory()->create();

        actingAs($admin);

        get(route('staff.academy.courses.edit', $course))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('availableTags', 3)
            );
    });

    test('returns lessons count', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        \App\Models\Course\Lesson::factory()->count(5)->create(['course_id' => $course->id]);

        actingAs($admin);

        get(route('staff.academy.courses.edit', $course))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('course.lessons_count', 5)
            );
    });
});
