<?php

use App\Enums\VideoHost;
use App\Models\Course\Course;
use App\Models\Course\Lesson;
use App\Models\Course\LessonTranscriptLine;
use App\Models\Course\LessonUser;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    Config::set('app.learn_enabled', true);
});

test('show returns 404 when learn is disabled', function () {
    Config::set('app.learn_enabled', false);

    $course = Course::factory()->published()->create();
    $lesson = Lesson::factory()->published()->for($course)->create();

    get(route('learn.courses.lessons.show', [$course, $lesson]))
        ->assertNotFound();
});

test('show allows admins when learn is disabled', function () {
    Config::set('app.learn_enabled', false);

    $course = Course::factory()->published()->create();
    $lesson = Lesson::factory()->published()->for($course)->create();
    $admin = User::factory()->admin()->create();

    actingAs($admin)
        ->get(route('learn.courses.lessons.show', [$course, $lesson]))
        ->assertOk();
});

test('show returns 200 for valid course and published lesson pair', function () {
    $course = Course::factory()->published()->create();
    $lesson = Lesson::factory()->published()->for($course)->create();

    get(route('learn.courses.lessons.show', [$course, $lesson]))
        ->assertOk();
});

test('show returns 404 for nonexistent lesson slug', function () {
    $course = Course::factory()->published()->create();

    get(route('learn.courses.lessons.show', [$course, 'nonexistent-slug']))
        ->assertNotFound();
});

test('show returns 404 when lesson does not belong to the course', function () {
    $courseA = Course::factory()->published()->create();
    $courseB = Course::factory()->published()->create();
    $lesson = Lesson::factory()->published()->for($courseB)->create();

    get(route('learn.courses.lessons.show', [$courseA, $lesson]))
        ->assertNotFound();
});

test('show renders the correct inertia component', function () {
    $course = Course::factory()->published()->create();
    $lesson = Lesson::factory()->published()->for($course)->create();

    get(route('learn.courses.lessons.show', [$course, $lesson]))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('learn/courses/lessons/show')
        );
});

test('show includes lesson details with rendered markdown', function () {
    $course = Course::factory()->published()->create();
    $instructor = User::factory()->create();
    $lesson = Lesson::factory()->published()->ungated()->for($course)->create([
        'description' => "Hello World\n\nThis is **bold** text.",
        'copy' => 'Some **copy** content',
        'learning_objectives' => 'Learn about **testing**',
        'playback_id' => 'play-me-back',
        'host' => VideoHost::Mux,
    ])->fresh();
    $lesson->instructors()->attach($instructor);
    $tag = Tag::factory()->create();
    $lesson->tags()->attach($tag);
    $transcriptLine = LessonTranscriptLine::factory()->for($lesson)->create([
        'start_seconds' => '10.500',
        'text' => 'Hello from the transcript',
        'order' => 1,
    ]);

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
                ->where('playback_id', 'play-me-back')
                ->where('host', $lesson->host->forFrontend()->toArray())
                ->where('playback_tokens', [])
                ->where('gated', $lesson->gated)
                ->where('allow_preview', $lesson->allow_preview)
                ->where('is_previewable', false)
                ->where('is_scheduled', false)
                ->where('publish_date', $lesson->publish_date?->format('Y-m-d'))
                ->where('order', $lesson->order)
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
                ->has('transcript_lines', 1, fn (AssertableInertia $tl) => $tl
                    ->where('id', $transcriptLine->id)
                    ->where('start_seconds', 10.5)
                    ->where('text', 'Hello from the transcript')
                )
                ->missing('description')
                ->missing('learning_objectives')
                ->missing('copy')
                ->missing('thumbnail_crops')
                ->missing('asset_id')
                ->missing('has_vtt_transcript')
                ->missing('course')
            )
        );
});

test('show returns empty instructors when lesson has no instructors', function () {
    $course = Course::factory()->published()->create();
    $lesson = Lesson::factory()->published()->for($course)->create();

    get(route('learn.courses.lessons.show', [$course, $lesson]))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('lesson.instructors', [])
        );
});

test('show returns null copy_html when lesson has no copy', function () {
    $course = Course::factory()->published()->create();
    $lesson = Lesson::factory()->published()->ungated()->for($course)->create([
        'copy' => null,
    ]);

    get(route('learn.courses.lessons.show', [$course, $lesson]))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('lesson.copy_html', null)
        );
});

test('show includes parent course with sibling lessons for navigation', function () {
    $course = Course::factory()->published()->create();
    $lessonA = Lesson::factory()->published()->for($course)->create(['order' => 1])->fresh();
    $lessonB = Lesson::factory()->published()->for($course)->create(['order' => 2])->fresh();

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
                    ->where('allow_preview', $lessonA->allow_preview)
                    ->where('is_previewable', false)
                    ->where('is_scheduled', false)
                    ->where('publish_date', $lessonA->publish_date?->format('Y-m-d'))
                    ->where('order', $lessonA->order)
                )
                ->missing('tags')
                ->missing('description')
                ->missing('description_html')
                ->missing('user')
            )
        );
});

test('show includes previousLesson and nextLesson for navigation', function () {
    $course = Course::factory()->published()->create();
    $first = Lesson::factory()->published()->for($course)->create(['order' => 1]);
    $second = Lesson::factory()->published()->for($course)->create(['order' => 2]);
    $third = Lesson::factory()->published()->for($course)->create(['order' => 3]);

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
    $course = Course::factory()->published()->create();
    $first = Lesson::factory()->published()->for($course)->create(['order' => 1]);
    $second = Lesson::factory()->published()->for($course)->create(['order' => 2]);

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
    $course = Course::factory()->published()->create();
    $first = Lesson::factory()->published()->for($course)->create(['order' => 1]);
    $last = Lesson::factory()->published()->for($course)->create(['order' => 2]);

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
    $course = Course::factory()->published()->create();
    $lesson = Lesson::factory()->published()->for($course)->create();

    get(route('learn.courses.lessons.show', [$course, $lesson]))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('completedLessonIds', [])
        );
});

describe('lesson access control', function () {
    test('show returns 404 for previewable lessons', function () {
        $course = Course::factory()->published()->create();
        $lesson = Lesson::factory()->previewable()->for($course)->create();

        get(route('learn.courses.lessons.show', [$course, $lesson]))
            ->assertNotFound();
    });

    test('show returns 404 for draft lessons', function () {
        $course = Course::factory()->published()->create();
        $lesson = Lesson::factory()->draft()->for($course)->create();

        get(route('learn.courses.lessons.show', [$course, $lesson]))
            ->assertNotFound();
    });

    test('show allows admin to access unpublished lessons', function () {
        $course = Course::factory()->published()->create();
        $lesson = Lesson::factory()->previewable()->for($course)->create();

        /** @var User $admin */
        $admin = User::factory()->create(['is_admin' => true]);

        actingAs($admin)
            ->get(route('learn.courses.lessons.show', [$course, $lesson]))
            ->assertOk();
    });

    test('show returns 404 when course is not published', function () {
        $course = Course::factory()->draft()->create();
        $lesson = Lesson::factory()->published()->for($course)->create();

        get(route('learn.courses.lessons.show', [$course, $lesson]))
            ->assertNotFound();
    });

    test('show returns 404 when course is previewable but not published', function () {
        $course = Course::factory()->previewable()->create();
        $lesson = Lesson::factory()->published()->for($course)->create();

        get(route('learn.courses.lessons.show', [$course, $lesson]))
            ->assertNotFound();
    });

    test('show allows admin to access lessons on unpublished course', function () {
        $course = Course::factory()->draft()->create();
        $lesson = Lesson::factory()->published()->for($course)->create();

        /** @var User $admin */
        $admin = User::factory()->create(['is_admin' => true]);

        actingAs($admin)
            ->get(route('learn.courses.lessons.show', [$course, $lesson]))
            ->assertOk();
    });
});

describe('gated lesson redirect', function () {
    test('show stores intended url in session when lesson is gated and user is unauthenticated', function () {
        $course = Course::factory()->published()->create();
        $lesson = Lesson::factory()->published()->for($course)->create([
            'gated' => true,
        ]);

        get(route('learn.courses.lessons.show', [$course, $lesson]))
            ->assertSessionHas('url.intended', route('learn.courses.lessons.show', [$course, $lesson]));
    });

    test('show does not store intended url when lesson is ungated', function () {
        $course = Course::factory()->published()->create();
        $lesson = Lesson::factory()->published()->ungated()->for($course)->create();

        get(route('learn.courses.lessons.show', [$course, $lesson]))
            ->assertSessionMissing('url.intended');
    });

    test('show does not store intended url when user is authenticated', function () {
        $course = Course::factory()->published()->create();
        $lesson = Lesson::factory()->published()->for($course)->create([
            'gated' => true,
        ]);

        /** @var User $user */
        $user = User::factory()->create();

        actingAs($user)
            ->get(route('learn.courses.lessons.show', [$course, $lesson]))
            ->assertSessionMissing('url.intended');
    });
});

describe('gated lesson content', function () {
    test('show strips sensitive fields and sets isGatedForUser true for gated lesson when unauthenticated', function () {
        $course = Course::factory()->published()->create();
        $lesson = Lesson::factory()->published()->for($course)->create([
            'gated' => true,
            'copy' => 'Some content',
        ])->fresh();

        get(route('learn.courses.lessons.show', [$course, $lesson]))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('isGatedForUser', true)
                ->has('lesson', fn (AssertableInertia $l) => $l
                    ->where('id', $lesson->id)
                    ->where('slug', $lesson->slug)
                    ->where('title', $lesson->title)
                    ->where('tagline', $lesson->tagline)
                    ->where('description_html', fn (string $html) => str_contains($html, '</p>'))
                    ->where('learning_objectives_html', $lesson->learning_objectives !== null ? fn (string $html) => str_contains($html, '</p>') : null)
                    ->where('thumbnail_url', $lesson->thumbnail_url)
                    ->where('thumbnail_rect_strings', $lesson->thumbnail_rect_strings)
                    ->where('duration_seconds', $lesson->duration_seconds)
                    ->where('gated', true)
                    ->where('allow_preview', $lesson->allow_preview)
                    ->where('is_previewable', false)
                    ->where('is_scheduled', false)
                    ->where('publish_date', $lesson->publish_date?->format('Y-m-d'))
                    ->where('order', $lesson->order)
                    ->where('tags', [])
                    ->where('instructors', [])
                    ->missing('copy_html')
                    ->missing('playback_id')
                    ->missing('host')
                    ->missing('playback_tokens')
                    ->missing('transcript_lines')
                )
            );
    });

    test('show includes all fields and sets isGatedForUser false for gated lesson when authenticated', function () {
        $course = Course::factory()->published()->create();
        $lesson = Lesson::factory()->published()->for($course)->create([
            'gated' => true,
            'copy' => 'Some content',
        ])->fresh();

        /** @var User $user */
        $user = User::factory()->create();

        actingAs($user)
            ->get(route('learn.courses.lessons.show', [$course, $lesson]))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('isGatedForUser', false)
                ->has('lesson', fn (AssertableInertia $l) => $l
                    ->where('id', $lesson->id)
                    ->where('slug', $lesson->slug)
                    ->where('title', $lesson->title)
                    ->where('tagline', $lesson->tagline)
                    ->where('description_html', fn (string $html) => str_contains($html, '</p>'))
                    ->where('copy_html', fn (string $html) => str_contains($html, 'Some content'))
                    ->where('learning_objectives_html', $lesson->learning_objectives !== null ? fn (string $html) => str_contains($html, '</p>') : null)
                    ->where('thumbnail_url', $lesson->thumbnail_url)
                    ->where('thumbnail_rect_strings', $lesson->thumbnail_rect_strings)
                    ->where('duration_seconds', $lesson->duration_seconds)
                    ->where('playback_id', $lesson->playback_id)
                    ->where('host', $lesson->host?->forFrontend()->toArray())
                    ->where('playback_tokens', [])
                    ->where('gated', true)
                    ->where('allow_preview', $lesson->allow_preview)
                    ->where('is_previewable', false)
                    ->where('is_scheduled', false)
                    ->where('publish_date', $lesson->publish_date?->format('Y-m-d'))
                    ->where('order', $lesson->order)
                    ->where('tags', [])
                    ->where('instructors', [])
                    ->where('transcript_lines', [])
                )
            );
    });

    test('show sets isGatedForUser false for ungated lesson when unauthenticated', function () {
        $course = Course::factory()->published()->create();
        $lesson = Lesson::factory()->published()->ungated()->for($course)->create()->fresh();

        get(route('learn.courses.lessons.show', [$course, $lesson]))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('isGatedForUser', false)
                ->has('lesson', fn (AssertableInertia $l) => $l
                    ->where('id', $lesson->id)
                    ->where('slug', $lesson->slug)
                    ->where('title', $lesson->title)
                    ->where('tagline', $lesson->tagline)
                    ->where('description_html', fn (?string $html) => $html === null || str_contains($html, '</p>'))
                    ->where('copy_html', $lesson->copy !== null ? fn (string $html) => str_contains($html, '</p>') : null)
                    ->where('learning_objectives_html', $lesson->learning_objectives !== null ? fn (string $html) => str_contains($html, '</p>') : null)
                    ->where('thumbnail_url', $lesson->thumbnail_url)
                    ->where('thumbnail_rect_strings', $lesson->thumbnail_rect_strings)
                    ->where('duration_seconds', $lesson->duration_seconds)
                    ->where('playback_id', $lesson->playback_id)
                    ->where('host', $lesson->host?->forFrontend()->toArray())
                    ->where('playback_tokens', [])
                    ->where('gated', false)
                    ->where('allow_preview', $lesson->allow_preview)
                    ->where('is_previewable', false)
                    ->where('is_scheduled', false)
                    ->where('publish_date', $lesson->publish_date?->format('Y-m-d'))
                    ->where('order', $lesson->order)
                    ->where('tags', [])
                    ->where('instructors', [])
                    ->where('transcript_lines', [])
                )
            );
    });
});

describe('sidebar lessons filtering', function () {
    test('show sidebar includes previewable lessons but not drafts', function () {
        $course = Course::factory()->published()->create();
        $published = Lesson::factory()->published()->for($course)->create(['order' => 1]);
        $previewable = Lesson::factory()->previewable()->for($course)->create(['order' => 2]);
        Lesson::factory()->draft()->for($course)->create(['order' => 3]);

        get(route('learn.courses.lessons.show', [$course, $published]))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('course.lessons', 2)
                ->where('course.lessons.0.id', $published->id)
                ->where('course.lessons.1.id', $previewable->id)
            );
    });
});

describe('navigation filtering', function () {
    test('show navigation only considers published lessons', function () {
        $course = Course::factory()->published()->create();
        $first = Lesson::factory()->published()->for($course)->create(['order' => 1]);
        Lesson::factory()->previewable()->for($course)->create(['order' => 2]);
        $third = Lesson::factory()->published()->for($course)->create(['order' => 3]);

        get(route('learn.courses.lessons.show', [$course, $first]))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('previousLesson', null)
                ->has('nextLesson', fn (AssertableInertia $l) => $l
                    ->where('slug', $third->slug)
                    ->where('title', $third->title)
                )
            );
    });
});

describe('lesson view logging', function () {
    test('show logs viewed_at for authenticated user on published lesson', function () {
        /** @var User $user */
        $user = User::factory()->create();
        $course = Course::factory()->published()->create();
        $lesson = Lesson::factory()->published()->for($course)->create();

        actingAs($user)
            ->get(route('learn.courses.lessons.show', [$course, $lesson]))
            ->assertOk();

        $pivot = LessonUser::query()
            ->where(column: 'user_id', operator: '=', value: $user->id)
            ->where(column: 'lesson_id', operator: '=', value: $lesson->id)
            ->first();

        expect($pivot)
            ->not->toBeNull()
            ->viewed_at->not->toBeNull();
    });

    test('show logs viewed_at in session for guest users', function () {
        $course = Course::factory()->published()->create();
        $lesson = Lesson::factory()->published()->for($course)->create();

        get(route('learn.courses.lessons.show', [$course, $lesson]))
            ->assertOk()
            ->assertSessionHas("lesson_progress.{$lesson->id}.viewed_at");

        expect(LessonUser::query()->count())->toBe(0);
    });

    test('show does not overwrite existing guest viewed_at in session', function () {
        $course = Course::factory()->published()->create();
        $lesson = Lesson::factory()->published()->for($course)->create();

        $originalViewedAt = now()->subDay()->toIso8601String();
        session(["lesson_progress.{$lesson->id}" => ['viewed_at' => $originalViewedAt]]);

        get(route('learn.courses.lessons.show', [$course, $lesson]))
            ->assertOk()
            ->assertSessionHas("lesson_progress.{$lesson->id}.viewed_at", $originalViewedAt);
    });

    test('show does not log viewed_at for admin previewing unpublished lesson', function () {
        /** @var User $admin */
        $admin = User::factory()->create(['is_admin' => true]);
        $course = Course::factory()->published()->create();
        $lesson = Lesson::factory()->previewable()->for($course)->create();

        actingAs($admin)
            ->get(route('learn.courses.lessons.show', [$course, $lesson]))
            ->assertOk();

        expect(LessonUser::query()->count())->toBe(0);
    });

    test('show does not log viewed_at for admin previewing lesson on unpublished course', function () {
        /** @var User $admin */
        $admin = User::factory()->create(['is_admin' => true]);
        $course = Course::factory()->draft()->create();
        $lesson = Lesson::factory()->published()->for($course)->create();

        actingAs($admin)
            ->get(route('learn.courses.lessons.show', [$course, $lesson]))
            ->assertOk();

        expect(LessonUser::query()->count())->toBe(0);
    });

    test('show does not overwrite existing viewed_at', function () {
        /** @var User $user */
        $user = User::factory()->create();
        $course = Course::factory()->published()->create();
        $lesson = Lesson::factory()->published()->for($course)->create();

        $originalViewedAt = now()->subDay();
        LessonUser::factory()->viewed()->create([
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'viewed_at' => $originalViewedAt,
        ]);

        actingAs($user)
            ->get(route('learn.courses.lessons.show', [$course, $lesson]))
            ->assertOk();

        $pivot = LessonUser::query()
            ->where(column: 'user_id', operator: '=', value: $user->id)
            ->where(column: 'lesson_id', operator: '=', value: $lesson->id)
            ->first();

        expect($pivot->viewed_at->timestamp)->toBe($originalViewedAt->timestamp);
    });
});

describe('lesson progress', function () {
    test('show returns completedLessonIds for user with completed lessons', function () {
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
            ->get(route('learn.courses.lessons.show', [$course, $lessonB]))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('completedLessonIds', [$lessonA->id])
            );
    });

    test('show returns default lessonProgress for guest with no session data', function () {
        $course = Course::factory()->published()->create();
        $lesson = Lesson::factory()->published()->for($course)->create();

        get(route('learn.courses.lessons.show', [$course, $lesson]))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('lessonProgress', [
                    'started' => false,
                    'completed' => false,
                    'playback_time_seconds' => null,
                ])
            );
    });

    test('show returns default lessonProgress for authenticated user with no progress record', function () {
        /** @var User $user */
        $user = User::factory()->create();
        $course = Course::factory()->published()->create();
        $lesson = Lesson::factory()->published()->for($course)->create();

        actingAs($user)
            ->get(route('learn.courses.lessons.show', [$course, $lesson]))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('lessonProgress', [
                    'started' => false,
                    'completed' => false,
                    'playback_time_seconds' => null,
                ])
            );
    });

    test('show returns lessonProgress reflecting started state for authenticated user', function () {
        /** @var User $user */
        $user = User::factory()->create();
        $course = Course::factory()->published()->create();
        $lesson = Lesson::factory()->published()->for($course)->create();

        LessonUser::factory()->viewed()->started()->create([
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'playback_time_seconds' => 42,
        ]);

        actingAs($user)
            ->get(route('learn.courses.lessons.show', [$course, $lesson]))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('lessonProgress', [
                    'started' => true,
                    'completed' => false,
                    'playback_time_seconds' => 42,
                ])
            );
    });

    test('show returns lessonProgress reflecting completed state for authenticated user', function () {
        /** @var User $user */
        $user = User::factory()->create();
        $course = Course::factory()->published()->create();
        $lesson = Lesson::factory()->published()->for($course)->create();

        LessonUser::factory()->viewed()->started()->completed()->create([
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'playback_time_seconds' => 300,
        ]);

        actingAs($user)
            ->get(route('learn.courses.lessons.show', [$course, $lesson]))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('lessonProgress', [
                    'started' => true,
                    'completed' => true,
                    'playback_time_seconds' => 300,
                ])
            );
    });

    test('show returns lessonProgress from session for guest with progress', function () {
        $course = Course::factory()->published()->create();
        $lesson = Lesson::factory()->published()->for($course)->create();

        session(["lesson_progress.{$lesson->id}" => [
            'started_at' => now()->toIso8601String(),
            'playback_time_seconds' => 120,
        ]]);

        get(route('learn.courses.lessons.show', [$course, $lesson]))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('lessonProgress', [
                    'started' => true,
                    'completed' => false,
                    'playback_time_seconds' => 120,
                ])
            );
    });

    test('show excludes lessons without completed_at from completedLessonIds', function () {
        /** @var User $user */
        $user = User::factory()->create();
        $course = Course::factory()->published()->create();
        $lesson = Lesson::factory()->published()->for($course)->create();

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
