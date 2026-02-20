<?php

use App\Actions\Course\LogLessonProgressAction;
use App\Models\Course\Course;
use App\Models\Course\Lesson;
use App\Models\Course\LessonUser;
use App\Models\User;
use Illuminate\Support\Carbon;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\mock;
use function Pest\Laravel\postJson;

test('accepts a valid playing event', function () {
    $course = Course::factory()->published()->create();
    $lesson = Lesson::factory()->published()->for($course)->create();

    postJson(route('api.learn.courses.lessons.player-event', [$course, $lesson]), [
        'event' => 'playing',
    ])->assertSuccessful();
});

test('accepts a valid timeupdate event with current_time', function () {
    $course = Course::factory()->published()->create();
    $lesson = Lesson::factory()->published()->for($course)->create();

    postJson(route('api.learn.courses.lessons.player-event', [$course, $lesson]), [
        'event' => 'timeupdate',
        'current_time' => 42.5,
    ])->assertSuccessful();
});

test('accepts a valid ended event', function () {
    $course = Course::factory()->published()->create();
    $lesson = Lesson::factory()->published()->for($course)->create();

    postJson(route('api.learn.courses.lessons.player-event', [$course, $lesson]), [
        'event' => 'ended',
    ])->assertSuccessful();
});

test('returns 404 when lesson does not belong to course', function () {
    $courseA = Course::factory()->published()->create();
    $courseB = Course::factory()->published()->create();
    $lesson = Lesson::factory()->published()->for($courseB)->create();

    postJson(route('api.learn.courses.lessons.player-event', [$courseA, $lesson]), [
        'event' => 'playing',
    ])->assertNotFound();
});

describe('validation', function () {
    it('rejects invalid data', function (array $data, string $invalid) {
        $course = Course::factory()->published()->create();
        $lesson = Lesson::factory()->published()->for($course)->create();

        postJson(route('api.learn.courses.lessons.player-event', [$course, $lesson]), $data)
            ->assertUnprocessable()
            ->assertJsonValidationErrors($invalid);
    })->with([
        'missing event' => [['current_time' => 10], 'event'],
        'invalid event type' => [['event' => 'paused'], 'event'],
        'timeupdate without current_time' => [['event' => 'timeupdate'], 'current_time'],
        'negative current_time' => [['event' => 'timeupdate', 'current_time' => -1], 'current_time'],
        'non-numeric current_time' => [['event' => 'timeupdate', 'current_time' => 'abc'], 'current_time'],
    ]);
});

describe('authenticated user progress', function () {
    it('calls the action with startedAt on playing event', function () {
        /** @var User */
        $user = User::factory()->create();
        $course = Course::factory()->published()->create();
        $lesson = Lesson::factory()->published()->for($course)->create();

        $action = mock(LogLessonProgressAction::class);
        $action->shouldReceive('handle')
            ->once()
            ->withArgs(function (Lesson $l, User $u, ?Carbon $viewedAt = null, ?Carbon $startedAt = null, ?int $playbackTimeSeconds = null, ?Carbon $completedAt = null) use ($lesson, $user) {
                return $l->is($lesson)
                    && $u->is($user)
                    && $startedAt instanceof Carbon
                    && $playbackTimeSeconds === null
                    && $completedAt === null;
            });

        actingAs($user)
            ->postJson(route('api.learn.courses.lessons.player-event', [$course, $lesson]), [
                'event' => 'playing',
            ])->assertSuccessful();
    });

    it('calls the action with playbackTimeSeconds on timeupdate event', function () {
        /** @var User */
        $user = User::factory()->create();
        $course = Course::factory()->published()->create();
        $lesson = Lesson::factory()->published()->for($course)->create();

        $action = mock(LogLessonProgressAction::class);
        $action->shouldReceive('handle')
            ->once()
            ->withArgs(function (Lesson $l, User $u, ?Carbon $viewedAt = null, ?Carbon $startedAt = null, ?int $playbackTimeSeconds = null, ?Carbon $completedAt = null) use ($lesson, $user) {
                return $l->is($lesson)
                    && $u->is($user)
                    && $startedAt === null
                    && $playbackTimeSeconds === 42
                    && $completedAt === null;
            });

        actingAs($user)
            ->postJson(route('api.learn.courses.lessons.player-event', [$course, $lesson]), [
                'event' => 'timeupdate',
                'current_time' => 42.5,
            ])->assertSuccessful();
    });

    it('calls the action with completedAt on ended event', function () {
        /** @var User */
        $user = User::factory()->create();
        $course = Course::factory()->published()->create();
        $lesson = Lesson::factory()->published()->for($course)->create();

        $action = mock(LogLessonProgressAction::class);
        $action->shouldReceive('handle')
            ->once()
            ->withArgs(function (Lesson $l, User $u, ?Carbon $viewedAt = null, ?Carbon $startedAt = null, ?int $playbackTimeSeconds = null, ?Carbon $completedAt = null) use ($lesson, $user) {
                return $l->is($lesson)
                    && $u->is($user)
                    && $startedAt === null
                    && $playbackTimeSeconds === null
                    && $completedAt instanceof Carbon;
            });

        actingAs($user)
            ->postJson(route('api.learn.courses.lessons.player-event', [$course, $lesson]), [
                'event' => 'ended',
            ])->assertSuccessful();
    });
});

describe('admin preview skips logging', function () {
    it('does not call the action for admin on unpublished lesson', function () {
        /** @var User */
        $admin = User::factory()->create(['is_admin' => true]);
        $course = Course::factory()->published()->create();
        $lesson = Lesson::factory()->for($course)->create(['publish_date' => null]);

        $action = mock(LogLessonProgressAction::class);
        $action->shouldNotReceive('handle');

        actingAs($admin)
            ->postJson(route('api.learn.courses.lessons.player-event', [$course, $lesson]), [
                'event' => 'playing',
            ])->assertSuccessful();
    });

    it('does not call the action for admin on unpublished course', function () {
        /** @var User */
        $admin = User::factory()->create(['is_admin' => true]);
        $course = Course::factory()->draft()->create();
        $lesson = Lesson::factory()->published()->for($course)->create();

        $action = mock(LogLessonProgressAction::class);
        $action->shouldNotReceive('handle');

        actingAs($admin)
            ->postJson(route('api.learn.courses.lessons.player-event', [$course, $lesson]), [
                'event' => 'timeupdate',
                'current_time' => 30,
            ])->assertSuccessful();
    });

    it('calls the action for admin on published lesson and course', function () {
        /** @var User */
        $admin = User::factory()->create(['is_admin' => true]);
        $course = Course::factory()->published()->create();
        $lesson = Lesson::factory()->published()->for($course)->create();

        $action = mock(LogLessonProgressAction::class);
        $action->shouldReceive('handle')->once();

        actingAs($admin)
            ->postJson(route('api.learn.courses.lessons.player-event', [$course, $lesson]), [
                'event' => 'playing',
            ])->assertSuccessful();
    });
});

describe('guest progress in session', function () {
    it('stores started_at in session on playing event', function () {
        $course = Course::factory()->published()->create();
        $lesson = Lesson::factory()->published()->for($course)->create();

        postJson(route('api.learn.courses.lessons.player-event', [$course, $lesson]), [
            'event' => 'playing',
        ])
            ->assertSuccessful()
            ->assertSessionHas("lesson_progress.{$lesson->id}.started_at");
    });

    it('stores playback_time_seconds in session on timeupdate event', function () {
        $course = Course::factory()->published()->create();
        $lesson = Lesson::factory()->published()->for($course)->create();

        postJson(route('api.learn.courses.lessons.player-event', [$course, $lesson]), [
            'event' => 'timeupdate',
            'current_time' => 42.5,
        ])
            ->assertSuccessful()
            ->assertSessionHas("lesson_progress.{$lesson->id}.playback_time_seconds", 42);
    });

    it('stores completed_at in session on ended event', function () {
        $course = Course::factory()->published()->create();
        $lesson = Lesson::factory()->published()->for($course)->create();

        postJson(route('api.learn.courses.lessons.player-event', [$course, $lesson]), [
            'event' => 'ended',
        ])
            ->assertSuccessful()
            ->assertSessionHas("lesson_progress.{$lesson->id}.completed_at");
    });

    it('does not overwrite started_at in session when already set', function () {
        $course = Course::factory()->published()->create();
        $lesson = Lesson::factory()->published()->for($course)->create();

        $originalValue = now()->subMinutes(5)->toIso8601String();
        session(["lesson_progress.{$lesson->id}" => ['started_at' => $originalValue]]);

        postJson(route('api.learn.courses.lessons.player-event', [$course, $lesson]), [
            'event' => 'playing',
        ])->assertSuccessful();

        expect(session("lesson_progress.{$lesson->id}.started_at"))->toBe($originalValue);
    });

    it('does not overwrite completed_at in session when already set', function () {
        $course = Course::factory()->published()->create();
        $lesson = Lesson::factory()->published()->for($course)->create();

        $originalValue = now()->subMinutes(5)->toIso8601String();
        session(["lesson_progress.{$lesson->id}" => ['completed_at' => $originalValue]]);

        postJson(route('api.learn.courses.lessons.player-event', [$course, $lesson]), [
            'event' => 'ended',
        ])->assertSuccessful();

        expect(session("lesson_progress.{$lesson->id}.completed_at"))->toBe($originalValue);
    });

    it('updates playback_time_seconds in session only when new value is greater', function () {
        $course = Course::factory()->published()->create();
        $lesson = Lesson::factory()->published()->for($course)->create();

        session(["lesson_progress.{$lesson->id}" => ['playback_time_seconds' => 100]]);

        postJson(route('api.learn.courses.lessons.player-event', [$course, $lesson]), [
            'event' => 'timeupdate',
            'current_time' => 50,
        ])->assertSuccessful();

        expect(session("lesson_progress.{$lesson->id}.playback_time_seconds"))->toBe(100);
    });

    it('updates playback_time_seconds in session when new value is greater', function () {
        $course = Course::factory()->published()->create();
        $lesson = Lesson::factory()->published()->for($course)->create();

        session(["lesson_progress.{$lesson->id}" => ['playback_time_seconds' => 50]]);

        postJson(route('api.learn.courses.lessons.player-event', [$course, $lesson]), [
            'event' => 'timeupdate',
            'current_time' => 100,
        ])->assertSuccessful();

        expect(session("lesson_progress.{$lesson->id}.playback_time_seconds"))->toBe(100);
    });

    it('auto-completes in session at 90% for a medium lesson', function () {
        $course = Course::factory()->published()->create();
        $lesson = Lesson::factory()->published()->for($course)->create(['duration_seconds' => 200]);

        postJson(route('api.learn.courses.lessons.player-event', [$course, $lesson]), [
            'event' => 'timeupdate',
            'current_time' => 180,
        ])
            ->assertSuccessful()
            ->assertSessionHas("lesson_progress.{$lesson->id}.playback_time_seconds", 180)
            ->assertSessionHas("lesson_progress.{$lesson->id}.completed_at");
    });

    it('does not auto-complete in session before 90% for a medium lesson', function () {
        $course = Course::factory()->published()->create();
        $lesson = Lesson::factory()->published()->for($course)->create(['duration_seconds' => 200]);

        postJson(route('api.learn.courses.lessons.player-event', [$course, $lesson]), [
            'event' => 'timeupdate',
            'current_time' => 179,
        ])
            ->assertSuccessful()
            ->assertSessionMissing("lesson_progress.{$lesson->id}.completed_at");
    });

    it('auto-completes in session within 10 seconds of end for a short lesson', function () {
        $course = Course::factory()->published()->create();
        $lesson = Lesson::factory()->published()->for($course)->create(['duration_seconds' => 60]);

        postJson(route('api.learn.courses.lessons.player-event', [$course, $lesson]), [
            'event' => 'timeupdate',
            'current_time' => 50,
        ])
            ->assertSuccessful()
            ->assertSessionHas("lesson_progress.{$lesson->id}.completed_at");
    });

    it('does not auto-complete in session before 10 seconds of end for a short lesson', function () {
        $course = Course::factory()->published()->create();
        $lesson = Lesson::factory()->published()->for($course)->create(['duration_seconds' => 60]);

        postJson(route('api.learn.courses.lessons.player-event', [$course, $lesson]), [
            'event' => 'timeupdate',
            'current_time' => 49,
        ])
            ->assertSuccessful()
            ->assertSessionMissing("lesson_progress.{$lesson->id}.completed_at");
    });

    it('auto-completes in session within 30 seconds of end for a long lesson', function () {
        $course = Course::factory()->published()->create();
        $lesson = Lesson::factory()->published()->for($course)->create(['duration_seconds' => 600]);

        postJson(route('api.learn.courses.lessons.player-event', [$course, $lesson]), [
            'event' => 'timeupdate',
            'current_time' => 570,
        ])
            ->assertSuccessful()
            ->assertSessionHas("lesson_progress.{$lesson->id}.completed_at");
    });

    it('does not auto-complete in session before 30 seconds of end for a long lesson', function () {
        $course = Course::factory()->published()->create();
        $lesson = Lesson::factory()->published()->for($course)->create(['duration_seconds' => 600]);

        postJson(route('api.learn.courses.lessons.player-event', [$course, $lesson]), [
            'event' => 'timeupdate',
            'current_time' => 569,
        ])
            ->assertSuccessful()
            ->assertSessionMissing("lesson_progress.{$lesson->id}.completed_at");
    });

    it('does not auto-complete in session when already completed', function () {
        $course = Course::factory()->published()->create();
        $lesson = Lesson::factory()->published()->for($course)->create(['duration_seconds' => 300]);

        $originalValue = now()->subMinutes(5)->toIso8601String();
        session(["lesson_progress.{$lesson->id}" => ['completed_at' => $originalValue]]);

        postJson(route('api.learn.courses.lessons.player-event', [$course, $lesson]), [
            'event' => 'timeupdate',
            'current_time' => 295,
        ])->assertSuccessful();

        expect(session("lesson_progress.{$lesson->id}.completed_at"))->toBe($originalValue);
    });

    it('does not auto-complete in session when lesson has no duration', function () {
        $course = Course::factory()->published()->create();
        $lesson = Lesson::factory()->published()->for($course)->create(['duration_seconds' => null]);

        postJson(route('api.learn.courses.lessons.player-event', [$course, $lesson]), [
            'event' => 'timeupdate',
            'current_time' => 295,
        ])
            ->assertSuccessful()
            ->assertSessionMissing("lesson_progress.{$lesson->id}.completed_at");
    });

    it('does not create a pivot record for guest users', function () {
        $course = Course::factory()->published()->create();
        $lesson = Lesson::factory()->published()->for($course)->create();

        postJson(route('api.learn.courses.lessons.player-event', [$course, $lesson]), [
            'event' => 'playing',
        ])->assertSuccessful();

        expect(LessonUser::query()->count())->toBe(0);
    });
});
