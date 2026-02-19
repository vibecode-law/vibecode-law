<?php

namespace App\Listeners;

use App\Actions\Course\LogLessonProgressAction;
use App\Models\Course\Lesson;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Session;

class SyncGuestLessonProgressOnLogin
{
    public function __construct(public LogLessonProgressAction $logProgress) {}

    public function handle(Login $event): void
    {
        $user = $event->user;

        if ($user instanceof User === false) {
            return;
        }

        $guestProgress = Session::get('lesson_progress', []);

        if ($guestProgress === []) {
            return;
        }

        $lessons = $this->fetchLessons($guestProgress);

        foreach ($guestProgress as $lessonId => $progress) {
            $lesson = $lessons->get($lessonId);

            if ($lesson === null) {
                continue;
            }

            $this->syncLessonProgress(user: $user, lesson: $lesson, progress: $progress);
        }

        Session::forget('lesson_progress');
    }

    /**
     * @param  array<int, array<string, mixed>>  $guestProgress
     * @return Collection<int, Lesson>
     */
    private function fetchLessons(array $guestProgress): Collection
    {
        return Lesson::query()
            ->whereIn('id', array_keys($guestProgress))
            ->get()
            ->keyBy('id');
    }

    /**
     * @param  array<string, mixed>  $progress
     */
    private function syncLessonProgress(User $user, Lesson $lesson, array $progress): void
    {
        $this->logProgress->handle(
            lesson: $lesson,
            user: $user,
            viewedAt: isset($progress['viewed_at']) ? Carbon::parse($progress['viewed_at']) : null,
            startedAt: isset($progress['started_at']) ? Carbon::parse($progress['started_at']) : null,
            playbackTimeSeconds: $progress['playback_time_seconds'] ?? null,
            completedAt: isset($progress['completed_at']) ? Carbon::parse($progress['completed_at']) : null,
        );
    }
}
