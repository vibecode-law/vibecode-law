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

class LessonPlayerEventController extends BaseController
{
    public function __invoke(LessonPlayerEventRequest $request, Course $course, Lesson $lesson, LogLessonProgressAction $logProgress): JsonResponse
    {
        $event = VideoPlayerEvent::from($request->validated('event'));

        /** @var User|null $user */
        $user = Auth::user();

        if ($this->isAdminPreview(course: $course, lesson: $lesson, user: $user) === false) {
            match ($event) {
                VideoPlayerEvent::Playing => $logProgress->handle(lesson: $lesson, user: $user, startedAt: now()),
                VideoPlayerEvent::TimeUpdate => $logProgress->handle(lesson: $lesson, user: $user, playbackTimeSeconds: (int) $request->validated('current_time')),
                VideoPlayerEvent::Ended => $logProgress->handle(lesson: $lesson, user: $user, completedAt: now()),
            };
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
}
