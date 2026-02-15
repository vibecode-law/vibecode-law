<?php

use App\Models\Course\Course;
use App\Models\Course\CourseTag;
use App\Models\Course\Lesson;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\get;

test('show returns 200 for guests with valid course slug', function () {
    $course = Course::factory()->create();

    get(route('learn.courses.show', $course))
        ->assertOk();
});

test('show returns 404 for nonexistent course', function () {
    get(route('learn.courses.show', ['course' => 'nonexistent-slug']))
        ->assertNotFound();
});

test('show renders the correct inertia component', function () {
    $course = Course::factory()->create();

    get(route('learn.courses.show', $course))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('learn/courses/show')
        );
});

test('show includes course details with description_html', function () {
    $course = Course::factory()->create()->fresh();

    get(route('learn.courses.show', $course))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('course', fn (AssertableInertia $c) => $c
                ->where('id', $course->id)
                ->where('slug', $course->slug)
                ->where('title', $course->title)
                ->where('tagline', $course->tagline)
                ->where('description', $course->description)
                ->has('description_html')
                ->where('thumbnail_url', $course->thumbnail_url)
                ->where('thumbnail_rect_strings', $course->thumbnail_rect_strings)
                ->where('learning_objectives', $course->learning_objectives)
                ->where('duration_seconds', $course->duration_seconds)
                ->where('visible', $course->visible)
                ->where('is_featured', $course->is_featured)
                ->where('publish_date', $course->publish_date?->toDateString())
                ->where('order', $course->order)
                ->has('experience_level')
                ->where('started_count', $course->started_count)
                ->where('completed_count', $course->completed_count)
                ->has('lessons')
                ->has('tags')
                ->has('user')
            )
        );
});

test('show includes ordered lessons list', function () {
    $course = Course::factory()->create();
    $second = Lesson::factory()->for($course)->create(['order' => 2]);
    $first = Lesson::factory()->for($course)->create(['order' => 1]);

    get(route('learn.courses.show', $course))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('course.lessons', 2)
            ->where('course.lessons.0.id', $first->id)
            ->where('course.lessons.1.id', $second->id)
        );
});

test('show includes tags', function () {
    $course = Course::factory()->create();
    $tag = CourseTag::factory()->create();
    $course->tags()->attach($tag);

    get(route('learn.courses.show', $course))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('course.tags', 1)
            ->where('course.tags.0.id', $tag->id)
            ->where('course.tags.0.name', $tag->name)
            ->where('course.tags.0.slug', $tag->slug)
        );
});

test('show includes user when course has one', function () {
    $user = User::factory()->create();
    $course = Course::factory()->for($user)->create();

    get(route('learn.courses.show', $course))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('course.user.handle', $user->handle)
            ->where('course.user.first_name', $user->first_name)
            ->where('course.user.last_name', $user->last_name)
        );
});

test('show returns null user when course has no user', function () {
    $course = Course::factory()->create(['user_id' => null]);

    get(route('learn.courses.show', $course))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('course.user', null)
        );
});
