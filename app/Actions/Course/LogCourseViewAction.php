<?php

namespace App\Actions\Course;

use App\Models\Course\Course;
use App\Models\Course\CourseUser;
use App\Models\User;

class LogCourseViewAction
{
    public function handle(Course $course, User $user): void
    {
        $existing = CourseUser::query()
            ->where(column: 'course_id', operator: '=', value: $course->id)
            ->where(column: 'user_id', operator: '=', value: $user->id)
            ->first();

        if ($existing === null) {
            CourseUser::query()->create([
                'course_id' => $course->id,
                'user_id' => $user->id,
                'viewed_at' => now(),
            ]);

            return;
        }

        if ($existing->viewed_at === null) {
            $existing->viewed_at = now();
            $existing->save();
        }
    }
}
