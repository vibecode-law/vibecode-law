<?php

namespace App\Actions\Course;

use App\Models\Course\Lesson;
use App\Models\Course\LessonUser;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Session;

class LogLessonProgressAction
{
    private ?LessonUser $existing = null;

    public function __construct(
        private SyncCourseStartedAction $syncCourseStarted,
        private SyncCourseCompletedAction $syncCourseCompleted
    ) {}

    public function handle(Lesson $lesson, ?User $user = null, ?Carbon $viewedAt = null, ?Carbon $startedAt = null, ?int $playbackTimeSeconds = null, ?Carbon $completedAt = null): void
    {
        $completedAt = $this->resolveCompletedAt(lesson: $lesson, playbackTimeSeconds: $playbackTimeSeconds, completedAt: $completedAt);

        if ($user !== null) {
            $this->logForUser(
                lesson: $lesson,
                user: $user,
                viewedAt: $viewedAt,
                startedAt: $startedAt,
                playbackTimeSeconds: $playbackTimeSeconds,
                completedAt: $completedAt
            );
        } else {
            $this->logForGuest(
                lesson: $lesson,
                viewedAt: $viewedAt,
                startedAt: $startedAt,
                playbackTimeSeconds: $playbackTimeSeconds,
                completedAt: $completedAt
            );
        }
    }

    private function logForUser(Lesson $lesson, User $user, ?Carbon $viewedAt, ?Carbon $startedAt, ?int $playbackTimeSeconds, ?Carbon $completedAt): void
    {
        $this->existing = LessonUser::query()
            ->where(column: 'lesson_id', operator: '=', value: $lesson->id)
            ->where(column: 'user_id', operator: '=', value: $user->id)
            ->first();

        $wasAlreadyCompleted = $this->existing?->completed_at !== null;

        if ($this->existing === null) {
            LessonUser::query()->create([
                'lesson_id' => $lesson->id,
                'user_id' => $user->id,
                'viewed_at' => $viewedAt ?? now(),
                'started_at' => $startedAt,
                'completed_at' => $completedAt,
                'playback_time_seconds' => $playbackTimeSeconds,
            ]);
        } else {
            $this->syncStartedAt(startedAt: $startedAt);
            $this->syncCompletedAt(completedAt: $completedAt);
            $this->syncPlaybackTime(playbackTimeSeconds: $playbackTimeSeconds);

            $this->existing->save();
        }

        $this->syncCourseStarted->handle(lesson: $lesson, user: $user, startedAt: $startedAt);

        $isNewlyCompleted = $wasAlreadyCompleted === false && $completedAt !== null;

        if ($isNewlyCompleted === true) {
            $this->syncCourseCompleted->handle(lesson: $lesson, user: $user);
        }
    }

    private function logForGuest(Lesson $lesson, ?Carbon $viewedAt, ?Carbon $startedAt, ?int $playbackTimeSeconds, ?Carbon $completedAt): void
    {
        $sessionKey = "lesson_progress.{$lesson->id}";

        /** @var array<string, mixed> $progress */
        $progress = Session::get($sessionKey, []);

        if (isset($progress['viewed_at']) === false) {
            $progress['viewed_at'] = ($viewedAt ?? now())->toIso8601String();
        }

        if ($startedAt !== null && isset($progress['started_at']) === false) {
            $progress['started_at'] = $startedAt->toIso8601String();
        }

        if ($playbackTimeSeconds !== null && (isset($progress['playback_time_seconds']) === false || $playbackTimeSeconds > $progress['playback_time_seconds'])) {
            $progress['playback_time_seconds'] = $playbackTimeSeconds;
        }

        if ($completedAt !== null && isset($progress['completed_at']) === false) {
            $progress['completed_at'] = $completedAt->toIso8601String();
        }

        Session::put($sessionKey, $progress);
    }

    private function resolveCompletedAt(Lesson $lesson, ?int $playbackTimeSeconds, ?Carbon $completedAt): ?Carbon
    {
        if ($completedAt !== null) {
            return $completedAt;
        }

        if (
            $playbackTimeSeconds !== null
            && $lesson->duration_seconds !== null
            && $playbackTimeSeconds >= (int) min(
                max($lesson->duration_seconds * 0.9, $lesson->duration_seconds - 30),
                $lesson->duration_seconds - 10,
            )
        ) {
            return now();
        }

        return null;
    }

    private function syncStartedAt(?Carbon $startedAt): void
    {
        if ($startedAt !== null && $this->existing->started_at === null) {
            $this->existing->started_at = $startedAt;
        }
    }

    private function syncCompletedAt(?Carbon $completedAt): void
    {
        if ($completedAt !== null && $this->existing->completed_at === null) {
            $this->existing->completed_at = $completedAt;
        }
    }

    private function syncPlaybackTime(?int $playbackTimeSeconds): void
    {
        if ($playbackTimeSeconds !== null && ($this->existing->playback_time_seconds === null || $playbackTimeSeconds > $this->existing->playback_time_seconds)) {
            $this->existing->playback_time_seconds = $playbackTimeSeconds;
        }
    }
}
