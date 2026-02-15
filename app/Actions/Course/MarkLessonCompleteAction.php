<?php

namespace App\Actions\Course;

use App\Models\Course\Lesson;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MarkLessonCompleteAction
{
    /**
     * Mark a lesson as complete for the given user.
     * This action is idempotent - it will not fail if the lesson is already complete.
     * If all lessons in the course are now complete, mark the course as complete too.
     */
    public function __invoke(Lesson $lesson, User $user): void
    {
        DB::transaction(function () use ($lesson, $user): void {
            // Check if lesson is already marked complete
            $lessonUser = DB::table(table: 'lesson_user')
                ->where(column: 'lesson_id', operator: '=', value: $lesson->id)
                ->where(column: 'user_id', operator: '=', value: $user->id)
                ->first();

            // If already complete, do nothing (idempotent)
            if ($lessonUser !== null && $lessonUser->completed_at !== null) {
                return;
            }

            // Mark lesson as complete
            if ($lessonUser !== null) {
                // Update existing record
                DB::table(table: 'lesson_user')
                    ->where(column: 'lesson_id', operator: '=', value: $lesson->id)
                    ->where(column: 'user_id', operator: '=', value: $user->id)
                    ->update(values: [
                        'completed_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
            } else {
                // Create new record (shouldn't normally happen, but handle it)
                DB::table(table: 'lesson_user')->insert(values: [
                    'lesson_id' => $lesson->id,
                    'user_id' => $user->id,
                    'viewed_at' => Carbon::now(),
                    'started_at' => Carbon::now(),
                    'completed_at' => Carbon::now(),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }

            // Check if all lessons in the course are now complete
            $course = $lesson->course;

            // Get total lessons in course
            $totalLessons = DB::table(table: 'lessons')
                ->where(column: 'course_id', operator: '=', value: $course->id)
                ->count();

            // Get completed lessons for this user in this course
            $completedLessons = DB::table(table: 'lesson_user')
                ->join(table: 'lessons', first: 'lesson_user.lesson_id', operator: '=', second: 'lessons.id')
                ->where(column: 'lessons.course_id', operator: '=', value: $course->id)
                ->where(column: 'lesson_user.user_id', operator: '=', value: $user->id)
                ->whereNotNull(columns: 'lesson_user.completed_at')
                ->count();

            // If all lessons complete, mark course as complete
            if ($completedLessons === $totalLessons) {
                $courseUser = DB::table(table: 'course_user')
                    ->where(column: 'course_id', operator: '=', value: $course->id)
                    ->where(column: 'user_id', operator: '=', value: $user->id)
                    ->first();

                // Only mark complete if not already complete
                if ($courseUser !== null && $courseUser->completed_at === null) {
                    DB::table(table: 'course_user')
                        ->where(column: 'course_id', operator: '=', value: $course->id)
                        ->where(column: 'user_id', operator: '=', value: $user->id)
                        ->update(values: [
                            'completed_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ]);

                    // Increment course completed_count
                    $course->increment(column: 'completed_count');
                }
            }
        });
    }
}
