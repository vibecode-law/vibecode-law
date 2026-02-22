<?php

use App\Actions\Course\LogCourseViewAction;
use App\Models\Course\Course;
use App\Models\Course\Lesson;
use App\Models\Course\LessonUser;
use App\Models\Tag;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\mock;

test('show returns 200 for guests with valid published course slug', function () {
    $course = Course::factory()->published()->create();

    get(route('learn.courses.show', $course))
        ->assertOk();
});

test('show returns 404 for nonexistent course', function () {
    get(route('learn.courses.show', ['course' => 'nonexistent-slug']))
        ->assertNotFound();
});

test('show renders the correct inertia component', function () {
    $course = Course::factory()->published()->create();

    get(route('learn.courses.show', $course))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('learn/courses/show')
        );
});

test('show includes course details with description_html and learning_objectives_html', function () {
    $instructor = User::factory()->create();
    $course = Course::factory()->published()->create([
        'description' => "Hello World\n\nThis is **bold** text.",
        'learning_objectives' => 'Learn **important** things.',
    ])->fresh();
    $lesson = Lesson::factory()->published()->for($course)->create()->fresh();
    $lesson->instructors()->attach($instructor);
    $tag = Tag::factory()->create();
    $course->tags()->attach($tag);

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
                ->where('allow_preview', $course->allow_preview)
                ->where('is_previewable', false)
                ->where('is_scheduled', false)
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
                    ->where('allow_preview', $lesson->allow_preview)
                    ->where('is_previewable', false)
                    ->where('is_scheduled', false)
                    ->where('publish_date', $lesson->publish_date?->format('Y-m-d'))
                    ->where('order', $lesson->order)
                    ->has('instructors', 1, fn (AssertableInertia $u) => $u
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
                ->has('tags', 1, fn (AssertableInertia $t) => $t
                    ->where('id', $tag->id)
                    ->where('name', $tag->name)
                    ->where('slug', $tag->slug)
                    ->where('type', $tag->type->forFrontend()->toArray())
                )
                ->has('instructors', 1, fn (AssertableInertia $u) => $u
                    ->where('first_name', $instructor->first_name)
                    ->where('last_name', $instructor->last_name)
                    ->where('handle', $instructor->handle)
                    ->where('organisation', $instructor->organisation)
                    ->where('job_title', $instructor->job_title)
                    ->where('avatar', $instructor->avatar)
                    ->where('linkedin_url', $instructor->linkedin_url)
                    ->where('team_role', $instructor->team_role)
                )
                ->missing('description')
                ->missing('learning_objectives')
                ->missing('lessons_count')
                ->missing('completed_count')
                ->missing('thumbnail_crops')
            )
        );
});

test('show includes ordered lessons list', function () {
    $course = Course::factory()->published()->create();
    $second = Lesson::factory()->published()->for($course)->create(['order' => 2]);
    $first = Lesson::factory()->published()->for($course)->create(['order' => 1]);

    get(route('learn.courses.show', $course))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('course.lessons', 2)
            ->where('course.lessons.0.id', $first->id)
            ->where('course.lessons.1.id', $second->id)
        );
});

test('show returns empty instructors when lessons have no instructors', function () {
    $course = Course::factory()->published()->create();
    Lesson::factory()->published()->for($course)->create();

    get(route('learn.courses.show', $course))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('course.instructors', [])
        );
});

test('show returns empty progress for unauthenticated user', function () {
    $course = Course::factory()->published()->create();
    Lesson::factory()->published()->for($course)->create(['order' => 1]);

    get(route('learn.courses.show', $course))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('completedLessonIds', [])
        );
});

test('show returns nextLessonSlug as firstLessonSlug for guests', function () {
    $course = Course::factory()->published()->create();
    $first = Lesson::factory()->published()->for($course)->create(['order' => 1]);
    Lesson::factory()->published()->for($course)->create(['order' => 2]);

    get(route('learn.courses.show', $course))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('nextLessonSlug', $first->slug)
        );
});

describe('course access control', function () {
    test('show returns 404 for draft courses', function () {
        $course = Course::factory()->draft()->create();

        get(route('learn.courses.show', $course))
            ->assertNotFound();
    });

    test('show returns 200 for previewable courses with lessons', function () {
        $course = Course::factory()->previewable()->create();
        Lesson::factory()->previewable()->for($course)->create();

        get(route('learn.courses.show', $course))
            ->assertOk();
    });

    test('show returns 404 for previewable courses with no lessons', function () {
        $course = Course::factory()->previewable()->create();

        get(route('learn.courses.show', $course))
            ->assertNotFound();
    });

    test('show allows admin to access previewable courses with no lessons', function () {
        $course = Course::factory()->previewable()->create();

        /** @var User $admin */
        $admin = User::factory()->create(['is_admin' => true]);

        actingAs($admin)
            ->get(route('learn.courses.show', $course))
            ->assertOk();
    });

    test('show allows admin to access draft courses', function () {
        $course = Course::factory()->draft()->create();

        /** @var User $admin */
        $admin = User::factory()->create(['is_admin' => true]);

        actingAs($admin)
            ->get(route('learn.courses.show', $course))
            ->assertOk();
    });
});

describe('lesson filtering', function () {
    test('show only includes published and previewable lessons', function () {
        $course = Course::factory()->published()->create();
        $published = Lesson::factory()->published()->for($course)->create(['order' => 1]);
        $previewable = Lesson::factory()->previewable()->for($course)->create(['order' => 2]);
        Lesson::factory()->draft()->for($course)->create(['order' => 3]);

        get(route('learn.courses.show', $course))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('course.lessons', 2)
                ->where('course.lessons.0.id', $published->id)
                ->where('course.lessons.1.id', $previewable->id)
            );
    });

    test('show nextLessonSlug ignores previewable lessons', function () {
        $course = Course::factory()->published()->create();
        $published = Lesson::factory()->published()->for($course)->create(['order' => 1]);
        Lesson::factory()->previewable()->for($course)->create(['order' => 2]);

        get(route('learn.courses.show', $course))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('nextLessonSlug', $published->slug)
            );
    });

    test('show returns null nextLessonSlug when no published lessons exist', function () {
        $course = Course::factory()->published()->create();
        Lesson::factory()->previewable()->for($course)->create(['order' => 1]);

        get(route('learn.courses.show', $course))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('nextLessonSlug', null)
            );
    });
});

describe('lesson progress', function () {
    test('show returns empty progress for authenticated user with no completed lessons', function () {
        /** @var User $user */
        $user = User::factory()->create();
        $course = Course::factory()->published()->create();

        actingAs($user)
            ->get(route('learn.courses.show', $course))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('completedLessonIds', [])
            );
    });

    test('show returns completedLessonIds for user with completed lessons', function () {
        /** @var User $user */
        $user = User::factory()->create();
        $course = Course::factory()->published()->create();

        $lessonA = Lesson::factory()->published()->for($course)->create(['order' => 1]);
        $lessonB = Lesson::factory()->published()->for($course)->create(['order' => 2]);
        Lesson::factory()->published()->for($course)->create(['order' => 3]);

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
            );
    });

    test('show excludes lessons without completed_at from completedLessonIds', function () {
        /** @var User $user */
        $user = User::factory()->create();
        $course = Course::factory()->published()->create();

        $lesson = Lesson::factory()->published()->for($course)->create(['order' => 1]);

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
        $course = Course::factory()->published()->create();

        $lessonA = Lesson::factory()->published()->for($course)->create(['order' => 1]);
        $lessonB = Lesson::factory()->published()->for($course)->create(['order' => 2]);

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
        $course = Course::factory()->published()->create();

        $lessonA = Lesson::factory()->published()->for($course)->create(['order' => 1]);
        $lessonB = Lesson::factory()->published()->for($course)->create(['order' => 2]);

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

describe('course view logging', function () {
    test('show calls LogCourseViewAction for authenticated user', function () {
        /** @var User $user */
        $user = User::factory()->create();
        $course = Course::factory()->published()->create();

        $action = mock(LogCourseViewAction::class);
        $action->shouldReceive('handle')
            ->once()
            ->withArgs(fn (Course $c, User $u) => $c->is($course) && $u->is($user));

        actingAs($user)
            ->get(route('learn.courses.show', $course))
            ->assertOk();
    });

    test('show does not call LogCourseViewAction for guest', function () {
        $course = Course::factory()->published()->create();

        $action = mock(LogCourseViewAction::class);
        $action->shouldNotReceive('handle');

        get(route('learn.courses.show', $course))
            ->assertOk();
    });

    test('show does not call LogCourseViewAction for admin previewing draft course', function () {
        /** @var User $admin */
        $admin = User::factory()->create(['is_admin' => true]);
        $course = Course::factory()->draft()->create();

        $action = mock(LogCourseViewAction::class);
        $action->shouldNotReceive('handle');

        actingAs($admin)
            ->get(route('learn.courses.show', $course))
            ->assertOk();
    });

    test('show calls LogCourseViewAction for admin viewing published course', function () {
        /** @var User $admin */
        $admin = User::factory()->create(['is_admin' => true]);
        $course = Course::factory()->published()->create();

        $action = mock(LogCourseViewAction::class);
        $action->shouldReceive('handle')
            ->once()
            ->withArgs(fn (Course $c, User $u) => $c->is($course) && $u->is($admin));

        actingAs($admin)
            ->get(route('learn.courses.show', $course))
            ->assertOk();
    });
});
