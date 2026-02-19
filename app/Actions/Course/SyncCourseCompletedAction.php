<?php

namespace App\Actions\Course;

use App\Jobs\MarketingEmail\AddTagToSubscriberJob;
use App\Models\Course\CourseUser;
use App\Models\Course\Lesson;
use App\Models\Course\LessonUser;
use App\Models\User;

class SyncCourseCompletedAction
{
    public function handle(Lesson $lesson, User $user): void
    {
        $course = $lesson->course;

        $visibleLessonIds = $course->visibleLessons()->pluck('id');

        $completedCount = LessonUser::query()
            ->where(column: 'user_id', operator: '=', value: $user->id)
            ->whereIn(column: 'lesson_id', values: $visibleLessonIds)
            ->whereNotNull(columns: 'completed_at')
            ->count();

        if ($completedCount < $visibleLessonIds->count()) {
            return;
        }

        $courseUser = CourseUser::query()
            ->where(column: 'course_id', operator: '=', value: $course->id)
            ->where(column: 'user_id', operator: '=', value: $user->id)
            ->first();

        if ($courseUser === null) {
            CourseUser::query()->create([
                'course_id' => $course->id,
                'user_id' => $user->id,
                'completed_at' => now(),
            ]);

            $this->dispatchTagJob(courseSlug: $course->slug, user: $user);

            return;
        }

        if ($courseUser->completed_at === null) {
            $courseUser->completed_at = now();
            $courseUser->save();

            $this->dispatchTagJob(courseSlug: $course->slug, user: $user);
        }
    }

    private function dispatchTagJob(string $courseSlug, User $user): void
    {
        if ($user->external_subscriber_uuid === null) {
            return;
        }

        AddTagToSubscriberJob::dispatch(
            externalSubscriberUuid: $user->external_subscriber_uuid,
            tag: "completedCourse:{$courseSlug}",
        );
    }
}
