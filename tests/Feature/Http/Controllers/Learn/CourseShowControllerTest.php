<?php

use App\Models\Course\Course;
use App\Models\Course\Lesson;
use App\Models\Course\LessonUser;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
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

test('show includes course details with description_html and learning_objectives_html', function () {
    $user = User::factory()->create();
    $course = Course::factory()->for($user)->create([
        'description' => "Hello World\n\nThis is **bold** text.",
        'learning_objectives' => 'Learn **important** things.',
    ])->fresh();
    $lesson = Lesson::factory()->for($course)->create()->fresh();

    get(route('learn.courses.show', $course))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('course', fn (AssertableInertia $c) => $c
                ->where('id', $course->id)
                ->where('slug', $course->slug)
                ->where('title', $course->title)
                ->where('tagline', $course->tagline)
                ->where('description_html', fn (string $html) => str_contains($html, '<p>Hello World</p>')
                    && str_contains($html, '<strong>bold</strong>')
                )
                ->where('thumbnail_url', $course->thumbnail_url)
                ->where('thumbnail_rect_strings', $course->thumbnail_rect_strings)
                ->where('learning_objectives_html', fn (string $html) => str_contains($html, '<strong>important</strong>'))
                ->where('duration_seconds', $course->duration_seconds)
                ->where('visible', $course->visible)
                ->where('is_featured', $course->is_featured)
                ->where('publish_date', $course->publish_date?->toDateString())
                ->where('order', $course->order)
                ->where('experience_level', $course->experience_level->forFrontend()->toArray())
                ->where('started_count', $course->started_count)
                ->has('lessons', 1, fn (AssertableInertia $l) => $l
                    ->where('id', $lesson->id)
                    ->where('slug', $lesson->slug)
                    ->where('title', $lesson->title)
                    ->where('tagline', $lesson->tagline)
                    ->where('thumbnail_url', $lesson->thumbnail_url)
                    ->where('thumbnail_rect_strings', $lesson->thumbnail_rect_strings)
                    ->where('gated', $lesson->gated)
                    ->where('visible', $lesson->visible)
                    ->where('publish_date', $lesson->publish_date?->format('Y-m-d'))
                    ->where('order', $lesson->order)
                )
                ->has('user', fn (AssertableInertia $u) => $u
                    ->where('first_name', $user->first_name)
                    ->where('last_name', $user->last_name)
                    ->where('handle', $user->handle)
                    ->where('organisation', $user->organisation)
                    ->where('job_title', $user->job_title)
                    ->where('avatar', $user->avatar)
                    ->where('linkedin_url', $user->linkedin_url)
                    ->where('team_role', $user->team_role)
                )
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

test('show returns null user when course has no user', function () {
    $course = Course::factory()->create(['user_id' => null]);

    get(route('learn.courses.show', $course))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('course.user', null)
        );
});

test('show includes totalLessons count', function () {
    $course = Course::factory()->create();
    Lesson::factory()->for($course)->count(3)->create();

    get(route('learn.courses.show', $course))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('totalLessons', 3)
        );
});

test('show returns empty progress for unauthenticated user', function () {
    $course = Course::factory()->create();
    Lesson::factory()->for($course)->create(['order' => 1]);

    get(route('learn.courses.show', $course))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('completedLessonIds', [])
        );
});

test('show returns nextLessonSlug as firstLessonSlug for guests', function () {
    $course = Course::factory()->create();
    $first = Lesson::factory()->for($course)->create(['order' => 1]);
    Lesson::factory()->for($course)->create(['order' => 2]);

    get(route('learn.courses.show', $course))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('nextLessonSlug', $first->slug)
        );
});

describe('lesson progress', function () {
    test('show returns empty progress for authenticated user with no completed lessons', function () {
        /** @var User $user */
        $user = User::factory()->create();
        $course = Course::factory()->create();

        actingAs($user)
            ->get(route('learn.courses.show', $course))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('completedLessonIds', [])
            );
    });

    test('show returns completedLessonIds for user with completed lessons', function () {
        /** @var User $user */
        $user = User::factory()->create();
        $course = Course::factory()->create();

        $lessonA = Lesson::factory()->for($course)->create(['order' => 1]);
        $lessonB = Lesson::factory()->for($course)->create(['order' => 2]);
        Lesson::factory()->for($course)->create(['order' => 3]);

        LessonUser::factory()->completed()->create([
            'user_id' => $user->id,
            'lesson_id' => $lessonA->id,
        ]);
        LessonUser::factory()->completed()->create([
            'user_id' => $user->id,
            'lesson_id' => $lessonB->id,
        ]);

        actingAs($user)
            ->get(route('learn.courses.show', $course))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('completedLessonIds', [$lessonA->id, $lessonB->id])
                ->where('totalLessons', 3)
            );
    });

    test('show excludes lessons without completed_at from completedLessonIds', function () {
        /** @var User $user */
        $user = User::factory()->create();
        $course = Course::factory()->create();

        $lesson = Lesson::factory()->for($course)->create(['order' => 1]);

        LessonUser::factory()->started()->create([
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'completed_at' => null,
        ]);

        actingAs($user)
            ->get(route('learn.courses.show', $course))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('completedLessonIds', [])
            );
    });

    test('show returns nextLessonSlug as first incomplete lesson', function () {
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
            ->get(route('learn.courses.show', $course))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('nextLessonSlug', $lessonB->slug)
            );
    });

    test('show returns nextLessonSlug as firstLessonSlug when all lessons completed', function () {
        /** @var User $user */
        $user = User::factory()->create();
        $course = Course::factory()->create();

        $lessonA = Lesson::factory()->for($course)->create(['order' => 1]);
        $lessonB = Lesson::factory()->for($course)->create(['order' => 2]);

        LessonUser::factory()->completed()->create([
            'user_id' => $user->id,
            'lesson_id' => $lessonA->id,
        ]);
        LessonUser::factory()->completed()->create([
            'user_id' => $user->id,
            'lesson_id' => $lessonB->id,
        ]);

        actingAs($user)
            ->get(route('learn.courses.show', $course))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('nextLessonSlug', $lessonA->slug)
            );
    });
});
