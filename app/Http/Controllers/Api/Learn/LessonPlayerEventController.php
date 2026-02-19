<?php

namespace App\Http\Controllers\Api\Learn;

use App\Actions\Course\LogLessonProgressAction;
use App\Enums\VideoPlayerEvent;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Api\Learn\LessonPlayerEventRequest;
use App\Models\Course\Course;
use App\Models\Course\Lesson;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;

class LessonPlayerEventController extends BaseController
{
    public function __invoke(LessonPlayerEventRequest $request, Course $course, Lesson $lesson, LogLessonProgressAction $logProgress): JsonResponse
    {
        $event = VideoPlayerEvent::from($request->validated('event'));

        /** @var User|null $user */
        $user = Auth::user();

        if ($this->isAdminPreview(course: $course, lesson: $lesson, user: $user) === false) {
            if ($user !== null) {
                $this->logForUser(action: $logProgress, lesson: $lesson, user: $user, event: $event, request: $request);
            } else {
                $this->logForGuest(request: $request, lesson: $lesson, event: $event);
            }
        }

        return Response::json(data: ['ok' => true]);
    }

    private function isAdminPreview(Course $course, Lesson $lesson, ?User $user): bool
    {
        if ($user?->is_admin !== true) {
            return false;
        }

        $isCoursePublished = $course->publish_date !== null && $course->publish_date->isPast();
        $isLessonPublished = $lesson->publish_date !== null && $lesson->publish_date->isPast();

        return $isCoursePublished === false || $isLessonPublished === false;
    }

    private function logForUser(LogLessonProgressAction $action, Lesson $lesson, User $user, VideoPlayerEvent $event, LessonPlayerEventRequest $request): void
    {
        match ($event) {
            VideoPlayerEvent::Playing => $action->handle(lesson: $lesson, user: $user, startedAt: now()),
            VideoPlayerEvent::TimeUpdate => $action->handle(lesson: $lesson, user: $user, playbackTimeSeconds: (int) $request->validated('current_time')),
            VideoPlayerEvent::Ended => $action->handle(lesson: $lesson, user: $user, completedAt: now()),
        };
    }

    private function logForGuest(LessonPlayerEventRequest $request, Lesson $lesson, VideoPlayerEvent $event): void
    {
        $sessionKey = "lesson_progress.{$lesson->id}";
        $progress = Session::get($sessionKey, []);

        match ($event) {
            VideoPlayerEvent::Playing => $progress = $this->setIfNull(data: $progress, key: 'started_at', value: now()->toIso8601String()),
            VideoPlayerEvent::TimeUpdate => $progress = $this->setIfGreater(data: $progress, key: 'playback_time_seconds', value: (int) $request->validated('current_time')),
            VideoPlayerEvent::Ended => $progress = $this->setIfNull(data: $progress, key: 'completed_at', value: now()->toIso8601String()),
        };

        if (
            isset($progress['completed_at']) === false
            && isset($progress['playback_time_seconds'])
            && $lesson->duration_seconds !== null
            && $progress['playback_time_seconds'] >= $lesson->duration_seconds - 10
        ) {
            $progress['completed_at'] = now()->toIso8601String();
        }

        Session::put($sessionKey, $progress);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function setIfNull(array $data, string $key, mixed $value): array
    {
        if (isset($data[$key]) === false) {
            $data[$key] = $value;
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function setIfGreater(array $data, string $key, int $value): array
    {
        if (isset($data[$key]) === false || $value > $data[$key]) {
            $data[$key] = $value;
        }

        return $data;
    }
}
