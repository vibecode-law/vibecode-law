<?php

namespace App\Actions\Course;

use App\Jobs\MarketingEmail\AddTagToSubscriberJob;
use App\Models\Course\CourseUser;
use App\Models\Course\Lesson;
use App\Models\User;
use Illuminate\Support\Carbon;

class SyncCourseStartedAction
{
    public function handle(Lesson $lesson, User $user, ?Carbon $startedAt): void
    {
        if ($startedAt === null) {
            return;
        }

        $courseUser = CourseUser::query()
            ->where(column: 'course_id', operator: '=', value: $lesson->course_id)
            ->where(column: 'user_id', operator: '=', value: $user->id)
            ->first();

        if ($courseUser === null) {
            CourseUser::query()->create([
                'course_id' => $lesson->course_id,
                'user_id' => $user->id,
                'started_at' => $startedAt,
            ]);

            $this->dispatchTagJob(lesson: $lesson, user: $user);

            return;
        }

        if ($courseUser->started_at === null) {
            $courseUser->started_at = $startedAt;
            $courseUser->save();

            $this->dispatchTagJob(lesson: $lesson, user: $user);
        }
    }

    private function dispatchTagJob(Lesson $lesson, User $user): void
    {
        if ($user->external_subscriber_uuid === null) {
            return;
        }

        AddTagToSubscriberJob::dispatch(
            externalSubscriberUuid: $user->external_subscriber_uuid,
            tag: "startedCourse:{$lesson->course->slug}",
        );
    }
}
