<?php

use App\Models\Course\Course;
use App\Models\Course\CourseTag;
use App\Models\Course\Lesson;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\get;

test('show returns 200 for valid course and lesson pair', function () {
    $course = Course::factory()->create();
    $lesson = Lesson::factory()->for($course)->create();

    get(route('learn.courses.lessons.show', [$course, $lesson]))
        ->assertOk();
});

test('show returns 404 for nonexistent lesson slug', function () {
    $course = Course::factory()->create();

    get(route('learn.courses.lessons.show', [$course, 'nonexistent-slug']))
        ->assertNotFound();
});

test('show returns 404 when lesson does not belong to the course', function () {
    $courseA = Course::factory()->create();
    $courseB = Course::factory()->create();
    $lesson = Lesson::factory()->for($courseB)->create();

    get(route('learn.courses.lessons.show', [$courseA, $lesson]))
        ->assertNotFound();
});

test('show renders the correct inertia component', function () {
    $course = Course::factory()->create();
    $lesson = Lesson::factory()->for($course)->create();

    get(route('learn.courses.lessons.show', [$course, $lesson]))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('learn/courses/lessons/show')
        );
});

test('show includes lesson details with description_html and copy_html', function () {
    $course = Course::factory()->create();
    $lesson = Lesson::factory()->for($course)->create([
        'copy' => 'Some **copy** content',
    ])->fresh();

    get(route('learn.courses.lessons.show', [$course, $lesson]))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('lesson', fn (AssertableInertia $l) => $l
                ->where('id', $lesson->id)
                ->where('slug', $lesson->slug)
                ->where('title', $lesson->title)
                ->where('tagline', $lesson->tagline)
                ->where('description', $lesson->description)
                ->has('description_html')
                ->where('thumbnail_url', $lesson->thumbnail_url)
                ->where('thumbnail_rect_strings', $lesson->thumbnail_rect_strings)
                ->where('copy', $lesson->copy)
                ->has('copy_html')
                ->has('learning_objectives')
                ->has('duration_seconds')
                ->has('transcript')
                ->has('embed')
                ->has('host')
                ->where('gated', $lesson->gated)
                ->where('order', $lesson->order)
                ->has('course')
            )
        );
});

test('show includes parent course with sibling lessons for navigation', function () {
    $course = Course::factory()->create();
    $lessonA = Lesson::factory()->for($course)->create(['order' => 1]);
    $lessonB = Lesson::factory()->for($course)->create(['order' => 2]);
    $tag = CourseTag::factory()->create();
    $course->tags()->attach($tag);

    get(route('learn.courses.lessons.show', [$course, $lessonA]))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('course', fn (AssertableInertia $c) => $c
                ->where('id', $course->id)
                ->where('slug', $course->slug)
                ->where('title', $course->title)
                ->where('tagline', $course->tagline)
                ->has('lessons', 2)
                ->where('lessons.0.id', $lessonA->id)
                ->where('lessons.1.id', $lessonB->id)
                ->has('tags', 1)
                ->missing('description')
                ->missing('description_html')
                ->missing('user')
            )
        );
});
