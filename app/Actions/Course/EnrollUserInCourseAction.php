<?php

namespace App\Actions\Course;

use App\Models\Course\Course;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EnrollUserInCourseAction
{
    /**
     * Enroll a user in a course.
     * This action is idempotent - it will not fail if the user is already enrolled.
     */
    public function __invoke(Course $course, User $user): void
    {
        DB::transaction(function () use ($course, $user): void {
            // Check if user is already enrolled
            $existingEnrollment = DB::table(table: 'course_user')
                ->where(column: 'course_id', operator: '=', value: $course->id)
                ->where(column: 'user_id', operator: '=', value: $user->id)
                ->first();

            // If already enrolled, do nothing (idempotent)
            if ($existingEnrollment !== null) {
                return;
            }

            // Create course_user record with started_at timestamp
            DB::table(table: 'course_user')->insert(values: [
                'course_id' => $course->id,
                'user_id' => $user->id,
                'started_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            // Increment courses.started_count
            $course->increment(column: 'started_count');
        });
    }
}
