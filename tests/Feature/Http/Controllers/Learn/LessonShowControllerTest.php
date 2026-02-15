<?php

use App\Models\Course\Course;
use App\Models\Course\CourseTag;
use App\Models\Course\Lesson;
use App\Models\Course\LessonUser;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
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

test('show includes lesson details with rendered markdown', function () {
    $course = Course::factory()->create();
    $lesson = Lesson::factory()->for($course)->create([
        'description' => "Hello World\n\nThis is **bold** text.",
        'copy' => 'Some **copy** content',
        'learning_objectives' => 'Learn about **testing**',
    ])->fresh();

    get(route('learn.courses.lessons.show', [$course, $lesson]))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('lesson', fn (AssertableInertia $l) => $l
                ->where('id', $lesson->id)
                ->where('slug', $lesson->slug)
                ->where('title', $lesson->title)
                ->where('tagline', $lesson->tagline)
                ->where('description_html', fn (string $html) => str_contains($html, '<p>Hello World</p>')
                    && str_contains($html, '<strong>bold</strong>')
                )
                ->where('thumbnail_url', $lesson->thumbnail_url)
                ->where('thumbnail_rect_strings', $lesson->thumbnail_rect_strings)
                ->where('copy_html', fn (string $html) => str_contains($html, '<strong>copy</strong>'))
                ->where('learning_objectives_html', fn (string $html) => str_contains($html, '<strong>testing</strong>'))
                ->where('duration_seconds', $lesson->duration_seconds)
                ->where('transcript', $lesson->transcript)
                ->where('gated', $lesson->gated)
                ->where('visible', $lesson->visible)
                ->where('publish_date', $lesson->publish_date?->format('Y-m-d'))
                ->where('order', $lesson->order)
            )
        );
});

test('show returns null copy_html when lesson has no copy', function () {
    $course = Course::factory()->create();
    $lesson = Lesson::factory()->for($course)->create([
        'copy' => null,
    ]);

    get(route('learn.courses.lessons.show', [$course, $lesson]))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('lesson.copy_html', null)
        );
});

test('show includes parent course with sibling lessons for navigation', function () {
    $course = Course::factory()->create();
    $lessonA = Lesson::factory()->for($course)->create(['order' => 1])->fresh();
    $lessonB = Lesson::factory()->for($course)->create(['order' => 2])->fresh();
    $tag = CourseTag::factory()->create();
    $course->tags()->attach($tag);

    get(route('learn.courses.lessons.show', [$course, $lessonA]))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('course', fn (AssertableInertia $c) => $c
                ->where('id', $course->id)
                ->where('slug', $course->slug)
                ->where('title', $course->title)
                ->where('tagline', $course->tagline)
                ->has('lessons', 2, fn (AssertableInertia $l) => $l
                    ->where('id', $lessonA->id)
                    ->where('slug', $lessonA->slug)
                    ->where('title', $lessonA->title)
                    ->where('tagline', $lessonA->tagline)
                    ->where('thumbnail_url', $lessonA->thumbnail_url)
                    ->where('thumbnail_rect_strings', $lessonA->thumbnail_rect_strings)
                    ->where('gated', $lessonA->gated)
                    ->where('visible', $lessonA->visible)
                    ->where('publish_date', $lessonA->publish_date?->format('Y-m-d'))
                    ->where('order', $lessonA->order)
                )
                ->has('tags', 1, fn (AssertableInertia $t) => $t
                    ->where('id', $tag->id)
                    ->where('name', $tag->name)
                    ->where('slug', $tag->slug)
                )
                ->missing('description')
                ->missing('description_html')
                ->missing('user')
            )
        );
});

test('show includes previousLesson and nextLesson for navigation', function () {
    $course = Course::factory()->create();
    $first = Lesson::factory()->for($course)->create(['order' => 1]);
    $second = Lesson::factory()->for($course)->create(['order' => 2]);
    $third = Lesson::factory()->for($course)->create(['order' => 3]);

    get(route('learn.courses.lessons.show', [$course, $second]))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('previousLesson', fn (AssertableInertia $l) => $l
                ->where('slug', $first->slug)
                ->where('title', $first->title)
            )
            ->has('nextLesson', fn (AssertableInertia $l) => $l
                ->where('slug', $third->slug)
                ->where('title', $third->title)
            )
        );
});

test('show returns null previousLesson when on first lesson', function () {
    $course = Course::factory()->create();
    $first = Lesson::factory()->for($course)->create(['order' => 1]);
    $second = Lesson::factory()->for($course)->create(['order' => 2]);

    get(route('learn.courses.lessons.show', [$course, $first]))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('previousLesson', null)
            ->has('nextLesson', fn (AssertableInertia $l) => $l
                ->where('slug', $second->slug)
                ->where('title', $second->title)
            )
        );
});

test('show returns null nextLesson when on last lesson', function () {
    $course = Course::factory()->create();
    $first = Lesson::factory()->for($course)->create(['order' => 1]);
    $last = Lesson::factory()->for($course)->create(['order' => 2]);

    get(route('learn.courses.lessons.show', [$course, $last]))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('previousLesson', fn (AssertableInertia $l) => $l
                ->where('slug', $first->slug)
                ->where('title', $first->title)
            )
            ->where('nextLesson', null)
        );
});

test('show returns empty completedLessonIds for unauthenticated user', function () {
    $course = Course::factory()->create();
    $lesson = Lesson::factory()->for($course)->create();

    get(route('learn.courses.lessons.show', [$course, $lesson]))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('completedLessonIds', [])
        );
});

describe('lesson progress', function () {
    test('show returns completedLessonIds for user with completed lessons', function () {
        /** @var User $user */
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $lessonA = Lesson::factory()->for($course)->create(['order' => 1]);
        $lessonB = Lesson::factory()->for($course)->create(['order' => 2]);

        LessonUser::factory()->completed()->create([
            'user_id' => $user->id,
            'lesson_id' => $lessonA->id,
        ]);

        actingAs($user)
            ->get(route('learn.courses.lessons.show', [$course, $lessonB]))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('completedLessonIds', [$lessonA->id])
            );
    });

    test('show excludes lessons without completed_at from completedLessonIds', function () {
        /** @var User $user */
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->for($course)->create();

        LessonUser::factory()->started()->create([
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'completed_at' => null,
        ]);

        actingAs($user)
            ->get(route('learn.courses.lessons.show', [$course, $lesson]))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('completedLessonIds', [])
            );
    });
});
