<?php

use App\Models\Course\Course;
use App\Models\Course\CourseTag;
use App\Models\Course\Lesson;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\get;

test('index returns 200 for guests', function () {
    get(route('learn.courses.index'))
        ->assertOk();
});

test('index renders the correct inertia component', function () {
    get(route('learn.courses.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('learn/courses/index')
        );
});

test('index returns courses ordered by order column', function () {
    $third = Course::factory()->create(['order' => 3]);
    $first = Course::factory()->create(['order' => 1]);
    $second = Course::factory()->create(['order' => 2]);

    get(route('learn.courses.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('courses', 3)
            ->where('courses.0.id', $first->id)
            ->where('courses.1.id', $second->id)
            ->where('courses.2.id', $third->id)
        );
});

test('index includes lesson counts', function () {
    $course = Course::factory()->create();
    Lesson::factory()->count(3)->for($course)->create();

    get(route('learn.courses.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('courses.0.lessons_count', 3)
        );
});

test('index includes tags', function () {
    $course = Course::factory()->create();
    $tag = CourseTag::factory()->create();
    $course->tags()->attach($tag);

    get(route('learn.courses.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('courses.0.tags', 1)
            ->where('courses.0.tags.0.id', $tag->id)
            ->where('courses.0.tags.0.name', $tag->name)
            ->where('courses.0.tags.0.slug', $tag->slug)
        );
});

test('index returns the correct data structure', function () {
    $course = Course::factory()->create()->fresh();
    $tag = CourseTag::factory()->create();
    $course->tags()->attach($tag);
    Lesson::factory()->count(2)->for($course)->create();

    get(route('learn.courses.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('courses', 1)
            ->has('courses.0', fn (AssertableInertia $c) => $c
                ->where('id', $course->id)
                ->where('slug', $course->slug)
                ->where('title', $course->title)
                ->where('tagline', $course->tagline)
                ->where('order', $course->order)
                ->where('lessons_count', 2)
                ->where('started_count', $course->started_count)
                ->where('completed_count', $course->completed_count)
                ->has('experience_level')
                ->has('tags', 1)
                ->missing('description')
                ->missing('description_html')
                ->missing('lessons')
                ->missing('user')
            )
        );
});
