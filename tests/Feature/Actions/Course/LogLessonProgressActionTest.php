<?php

use App\Actions\Course\LogLessonProgressAction;
use App\Actions\Course\SyncCourseCompletedAction;
use App\Actions\Course\SyncCourseStartedAction;
use App\Models\Course\Lesson;
use App\Models\Course\LessonUser;
use App\Models\User;

use function Pest\Laravel\mock;

beforeEach(function () {
    mock(SyncCourseStartedAction::class)->shouldIgnoreMissing();
    mock(SyncCourseCompletedAction::class)->shouldIgnoreMissing();
});

describe('new record', function () {
    it('creates a pivot record with viewed_at and started_at', function () {
        $user = User::factory()->create();
        $lesson = Lesson::factory()->create();

        app(LogLessonProgressAction::class)->handle(lesson: $lesson, user: $user, startedAt: now());

        $pivot = LessonUser::query()
            ->where(column: 'user_id', operator: '=', value: $user->id)
            ->where(column: 'lesson_id', operator: '=', value: $lesson->id)
            ->first();

        expect($pivot)
            ->not->toBeNull()
            ->viewed_at->not->toBeNull()
            ->started_at->not->toBeNull()
            ->completed_at->toBeNull()
            ->playback_time_seconds->toBeNull();
    });

    it('creates a pivot record with playback_time_seconds', function () {
        $user = User::factory()->create();
        $lesson = Lesson::factory()->create();

        app(LogLessonProgressAction::class)->handle(lesson: $lesson, user: $user, playbackTimeSeconds: 42);

        $pivot = LessonUser::query()
            ->where(column: 'user_id', operator: '=', value: $user->id)
            ->where(column: 'lesson_id', operator: '=', value: $lesson->id)
            ->first();

        expect($pivot)
            ->not->toBeNull()
            ->viewed_at->not->toBeNull()
            ->playback_time_seconds->toBe(42)
            ->started_at->toBeNull()
            ->completed_at->toBeNull();
    });

    it('creates a pivot record with completed_at', function () {
        $user = User::factory()->create();
        $lesson = Lesson::factory()->create();

        app(LogLessonProgressAction::class)->handle(lesson: $lesson, user: $user, completedAt: now());

        $pivot = LessonUser::query()
            ->where(column: 'user_id', operator: '=', value: $user->id)
            ->where(column: 'lesson_id', operator: '=', value: $lesson->id)
            ->first();

        expect($pivot)
            ->not->toBeNull()
            ->viewed_at->not->toBeNull()
            ->completed_at->not->toBeNull()
            ->started_at->toBeNull()
            ->playback_time_seconds->toBeNull();
    });
});

describe('existing record', function () {
    it('does not overwrite started_at when already set', function () {
        $user = User::factory()->create();
        $lesson = Lesson::factory()->create();

        $originalStartedAt = now()->subMinutes(5);
        LessonUser::factory()->create([
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'started_at' => $originalStartedAt,
        ]);

        app(LogLessonProgressAction::class)->handle(lesson: $lesson, user: $user, startedAt: now());

        $pivot = LessonUser::query()
            ->where(column: 'user_id', operator: '=', value: $user->id)
            ->where(column: 'lesson_id', operator: '=', value: $lesson->id)
            ->first();

        expect($pivot->started_at->timestamp)->toBe($originalStartedAt->timestamp);
    });

    it('does not overwrite completed_at when already set', function () {
        $user = User::factory()->create();
        $lesson = Lesson::factory()->create();

        $originalCompletedAt = now()->subMinutes(5);
        LessonUser::factory()->create([
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'completed_at' => $originalCompletedAt,
        ]);

        app(LogLessonProgressAction::class)->handle(lesson: $lesson, user: $user, completedAt: now());

        $pivot = LessonUser::query()
            ->where(column: 'user_id', operator: '=', value: $user->id)
            ->where(column: 'lesson_id', operator: '=', value: $lesson->id)
            ->first();

        expect($pivot->completed_at->timestamp)->toBe($originalCompletedAt->timestamp);
    });

    it('updates playback_time_seconds only when new value is greater', function () {
        $user = User::factory()->create();
        $lesson = Lesson::factory()->create();

        LessonUser::factory()->create([
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'playback_time_seconds' => 100,
        ]);

        app(LogLessonProgressAction::class)->handle(lesson: $lesson, user: $user, playbackTimeSeconds: 50);

        $pivot = LessonUser::query()
            ->where(column: 'user_id', operator: '=', value: $user->id)
            ->where(column: 'lesson_id', operator: '=', value: $lesson->id)
            ->first();

        expect($pivot->playback_time_seconds)->toBe(100);
    });

    it('updates playback_time_seconds when new value is greater than current', function () {
        $user = User::factory()->create();
        $lesson = Lesson::factory()->create();

        LessonUser::factory()->create([
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'playback_time_seconds' => 50,
        ]);

        app(LogLessonProgressAction::class)->handle(lesson: $lesson, user: $user, playbackTimeSeconds: 100);

        $pivot = LessonUser::query()
            ->where(column: 'user_id', operator: '=', value: $user->id)
            ->where(column: 'lesson_id', operator: '=', value: $lesson->id)
            ->first();

        expect($pivot->playback_time_seconds)->toBe(100);
    });
});

describe('auto-complete', function () {
    it('auto-completes at 90% for a medium lesson', function () {
        $user = User::factory()->create();
        $lesson = Lesson::factory()->create(['duration_seconds' => 200]);

        app(LogLessonProgressAction::class)->handle(lesson: $lesson, user: $user, playbackTimeSeconds: 180);

        $pivot = LessonUser::query()
            ->where(column: 'user_id', operator: '=', value: $user->id)
            ->where(column: 'lesson_id', operator: '=', value: $lesson->id)
            ->first();

        expect($pivot)
            ->playback_time_seconds->toBe(180)
            ->completed_at->not->toBeNull();
    });

    it('does not auto-complete before 90% for a medium lesson', function () {
        $user = User::factory()->create();
        $lesson = Lesson::factory()->create(['duration_seconds' => 200]);

        app(LogLessonProgressAction::class)->handle(lesson: $lesson, user: $user, playbackTimeSeconds: 179);

        $pivot = LessonUser::query()
            ->where(column: 'user_id', operator: '=', value: $user->id)
            ->where(column: 'lesson_id', operator: '=', value: $lesson->id)
            ->first();

        expect($pivot->completed_at)->toBeNull();
    });

    it('auto-completes within 10 seconds of end for a short lesson', function () {
        $user = User::factory()->create();
        $lesson = Lesson::factory()->create(['duration_seconds' => 60]);

        app(LogLessonProgressAction::class)->handle(lesson: $lesson, user: $user, playbackTimeSeconds: 50);

        $pivot = LessonUser::query()
            ->where(column: 'user_id', operator: '=', value: $user->id)
            ->where(column: 'lesson_id', operator: '=', value: $lesson->id)
            ->first();

        expect($pivot->completed_at)->not->toBeNull();
    });

    it('does not auto-complete before 10 seconds of end for a short lesson', function () {
        $user = User::factory()->create();
        $lesson = Lesson::factory()->create(['duration_seconds' => 60]);

        app(LogLessonProgressAction::class)->handle(lesson: $lesson, user: $user, playbackTimeSeconds: 49);

        $pivot = LessonUser::query()
            ->where(column: 'user_id', operator: '=', value: $user->id)
            ->where(column: 'lesson_id', operator: '=', value: $lesson->id)
            ->first();

        expect($pivot->completed_at)->toBeNull();
    });

    it('auto-completes within 30 seconds of end for a long lesson', function () {
        $user = User::factory()->create();
        $lesson = Lesson::factory()->create(['duration_seconds' => 600]);

        app(LogLessonProgressAction::class)->handle(lesson: $lesson, user: $user, playbackTimeSeconds: 570);

        $pivot = LessonUser::query()
            ->where(column: 'user_id', operator: '=', value: $user->id)
            ->where(column: 'lesson_id', operator: '=', value: $lesson->id)
            ->first();

        expect($pivot->completed_at)->not->toBeNull();
    });

    it('does not auto-complete before 30 seconds of end for a long lesson', function () {
        $user = User::factory()->create();
        $lesson = Lesson::factory()->create(['duration_seconds' => 600]);

        app(LogLessonProgressAction::class)->handle(lesson: $lesson, user: $user, playbackTimeSeconds: 569);

        $pivot = LessonUser::query()
            ->where(column: 'user_id', operator: '=', value: $user->id)
            ->where(column: 'lesson_id', operator: '=', value: $lesson->id)
            ->first();

        expect($pivot->completed_at)->toBeNull();
    });

    it('auto-completes when playback time exceeds lesson duration', function () {
        $user = User::factory()->create();
        $lesson = Lesson::factory()->create(['duration_seconds' => 300]);

        app(LogLessonProgressAction::class)->handle(lesson: $lesson, user: $user, playbackTimeSeconds: 305);

        $pivot = LessonUser::query()
            ->where(column: 'user_id', operator: '=', value: $user->id)
            ->where(column: 'lesson_id', operator: '=', value: $lesson->id)
            ->first();

        expect($pivot->completed_at)->not->toBeNull();
    });

    it('does not auto-complete when already completed', function () {
        $user = User::factory()->create();
        $lesson = Lesson::factory()->create(['duration_seconds' => 300]);

        $originalCompletedAt = now()->subMinutes(5);
        LessonUser::factory()->create([
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'completed_at' => $originalCompletedAt,
        ]);

        app(LogLessonProgressAction::class)->handle(lesson: $lesson, user: $user, playbackTimeSeconds: 295);

        $pivot = LessonUser::query()
            ->where(column: 'user_id', operator: '=', value: $user->id)
            ->where(column: 'lesson_id', operator: '=', value: $lesson->id)
            ->first();

        expect($pivot->completed_at->timestamp)->toBe($originalCompletedAt->timestamp);
    });

    it('does not auto-complete when lesson has no duration', function () {
        $user = User::factory()->create();
        $lesson = Lesson::factory()->create(['duration_seconds' => null]);

        app(LogLessonProgressAction::class)->handle(lesson: $lesson, user: $user, playbackTimeSeconds: 295);

        $pivot = LessonUser::query()
            ->where(column: 'user_id', operator: '=', value: $user->id)
            ->where(column: 'lesson_id', operator: '=', value: $lesson->id)
            ->first();

        expect($pivot->completed_at)->toBeNull();
    });
});

describe('guest progress', function () {
    it('stores viewed_at in session on first interaction', function () {
        $lesson = Lesson::factory()->create();

        app(LogLessonProgressAction::class)->handle(lesson: $lesson, startedAt: now());

        expect(session("lesson_progress.{$lesson->id}.viewed_at"))->not->toBeNull();
    });

    it('does not overwrite viewed_at when already set', function () {
        $lesson = Lesson::factory()->create();

        $originalValue = now()->subMinutes(5)->toIso8601String();
        session(["lesson_progress.{$lesson->id}" => ['viewed_at' => $originalValue]]);

        app(LogLessonProgressAction::class)->handle(lesson: $lesson, startedAt: now());

        expect(session("lesson_progress.{$lesson->id}.viewed_at"))->toBe($originalValue);
    });

    it('stores started_at in session', function () {
        $lesson = Lesson::factory()->create();

        app(LogLessonProgressAction::class)->handle(lesson: $lesson, startedAt: now());

        expect(session("lesson_progress.{$lesson->id}.started_at"))->not->toBeNull();
    });

    it('stores playback_time_seconds in session', function () {
        $lesson = Lesson::factory()->create();

        app(LogLessonProgressAction::class)->handle(lesson: $lesson, playbackTimeSeconds: 42);

        expect(session("lesson_progress.{$lesson->id}.playback_time_seconds"))->toBe(42);
    });

    it('stores completed_at in session', function () {
        $lesson = Lesson::factory()->create();

        app(LogLessonProgressAction::class)->handle(lesson: $lesson, completedAt: now());

        expect(session("lesson_progress.{$lesson->id}.completed_at"))->not->toBeNull();
    });

    it('does not overwrite started_at when already set', function () {
        $lesson = Lesson::factory()->create();

        $originalValue = now()->subMinutes(5)->toIso8601String();
        session(["lesson_progress.{$lesson->id}" => ['started_at' => $originalValue]]);

        app(LogLessonProgressAction::class)->handle(lesson: $lesson, startedAt: now());

        expect(session("lesson_progress.{$lesson->id}.started_at"))->toBe($originalValue);
    });

    it('does not overwrite completed_at when already set', function () {
        $lesson = Lesson::factory()->create();

        $originalValue = now()->subMinutes(5)->toIso8601String();
        session(["lesson_progress.{$lesson->id}" => ['completed_at' => $originalValue]]);

        app(LogLessonProgressAction::class)->handle(lesson: $lesson, completedAt: now());

        expect(session("lesson_progress.{$lesson->id}.completed_at"))->toBe($originalValue);
    });

    it('updates playback_time_seconds only when new value is greater', function () {
        $lesson = Lesson::factory()->create();

        session(["lesson_progress.{$lesson->id}" => ['playback_time_seconds' => 100]]);

        app(LogLessonProgressAction::class)->handle(lesson: $lesson, playbackTimeSeconds: 50);

        expect(session("lesson_progress.{$lesson->id}.playback_time_seconds"))->toBe(100);
    });

    it('updates playback_time_seconds when new value is greater', function () {
        $lesson = Lesson::factory()->create();

        session(["lesson_progress.{$lesson->id}" => ['playback_time_seconds' => 50]]);

        app(LogLessonProgressAction::class)->handle(lesson: $lesson, playbackTimeSeconds: 100);

        expect(session("lesson_progress.{$lesson->id}.playback_time_seconds"))->toBe(100);
    });

    it('auto-completes at 90% for a medium lesson', function () {
        $lesson = Lesson::factory()->create(['duration_seconds' => 200]);

        app(LogLessonProgressAction::class)->handle(lesson: $lesson, playbackTimeSeconds: 180);

        expect(session("lesson_progress.{$lesson->id}"))
            ->playback_time_seconds->toBe(180)
            ->completed_at->not->toBeNull();
    });

    it('does not auto-complete before 90% for a medium lesson', function () {
        $lesson = Lesson::factory()->create(['duration_seconds' => 200]);

        app(LogLessonProgressAction::class)->handle(lesson: $lesson, playbackTimeSeconds: 179);

        expect(session("lesson_progress.{$lesson->id}.completed_at"))->toBeNull();
    });

    it('does not auto-complete when already completed', function () {
        $lesson = Lesson::factory()->create(['duration_seconds' => 200]);

        $originalValue = now()->subMinutes(5)->toIso8601String();
        session(["lesson_progress.{$lesson->id}" => ['completed_at' => $originalValue]]);

        app(LogLessonProgressAction::class)->handle(lesson: $lesson, playbackTimeSeconds: 195);

        expect(session("lesson_progress.{$lesson->id}.completed_at"))->toBe($originalValue);
    });

    it('does not auto-complete when lesson has no duration', function () {
        $lesson = Lesson::factory()->create(['duration_seconds' => null]);

        app(LogLessonProgressAction::class)->handle(lesson: $lesson, playbackTimeSeconds: 295);

        expect(session("lesson_progress.{$lesson->id}.completed_at"))->toBeNull();
    });

    it('does not create a pivot record', function () {
        $lesson = Lesson::factory()->create();

        app(LogLessonProgressAction::class)->handle(lesson: $lesson, startedAt: now());

        expect(LessonUser::query()->count())->toBe(0);
    });

    it('does not call course sync actions', function () {
        $lesson = Lesson::factory()->create();

        mock(SyncCourseStartedAction::class)->shouldNotReceive('handle');
        mock(SyncCourseCompletedAction::class)->shouldNotReceive('handle');

        app(LogLessonProgressAction::class)->handle(lesson: $lesson, completedAt: now());
    });
});

describe('course started', function () {
    it('calls SyncCourseStartedAction with startedAt', function () {
        $user = User::factory()->create();
        $lesson = Lesson::factory()->create();
        $startedAt = now();

        mock(SyncCourseStartedAction::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (Lesson $l, User $u, $s) => $l->is($lesson) && $u->is($user) && $s->equalTo($startedAt));

        app(LogLessonProgressAction::class)->handle(lesson: $lesson, user: $user, startedAt: $startedAt);
    });

    it('calls SyncCourseStartedAction with null when startedAt is null', function () {
        $user = User::factory()->create();
        $lesson = Lesson::factory()->create();

        mock(SyncCourseStartedAction::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (Lesson $l, User $u, $s) => $l->is($lesson) && $u->is($user) && $s === null);

        app(LogLessonProgressAction::class)->handle(lesson: $lesson, user: $user, playbackTimeSeconds: 42);
    });
});

describe('course completed', function () {
    it('calls SyncCourseCompletedAction when lesson is newly completed', function () {
        $user = User::factory()->create();
        $lesson = Lesson::factory()->create();

        mock(SyncCourseCompletedAction::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (Lesson $l, User $u) => $l->is($lesson) && $u->is($user));

        app(LogLessonProgressAction::class)->handle(lesson: $lesson, user: $user, completedAt: now());
    });

    it('does not call SyncCourseCompletedAction when lesson was already completed', function () {
        $user = User::factory()->create();
        $lesson = Lesson::factory()->create();

        LessonUser::factory()->create([
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'completed_at' => now()->subMinutes(5),
        ]);

        mock(SyncCourseCompletedAction::class)
            ->shouldNotReceive('handle');

        app(LogLessonProgressAction::class)->handle(lesson: $lesson, user: $user, completedAt: now());
    });

    it('does not call SyncCourseCompletedAction when completedAt is null', function () {
        $user = User::factory()->create();
        $lesson = Lesson::factory()->create();

        mock(SyncCourseCompletedAction::class)
            ->shouldNotReceive('handle');

        app(LogLessonProgressAction::class)->handle(lesson: $lesson, user: $user, playbackTimeSeconds: 42);
    });

    it('calls SyncCourseCompletedAction when auto-completed', function () {
        $user = User::factory()->create();
        $lesson = Lesson::factory()->create(['duration_seconds' => 300]);

        mock(SyncCourseCompletedAction::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (Lesson $l, User $u) => $l->is($lesson) && $u->is($user));

        app(LogLessonProgressAction::class)->handle(lesson: $lesson, user: $user, playbackTimeSeconds: 295);
    });
});
