<?php

namespace App\Http\Controllers\Course\Public;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Course\CourseResource;
use App\Models\Course\Course;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CourseShowController extends BaseController
{
    public function __invoke(Course $course): Response
    {
        $course->load('lessons', 'tags', 'user');

        // Get first lesson slug
        $firstLessonSlug = $course->lessons->sortBy('order')->first()?->slug;

        $user = auth()->user();
        $isEnrolled = false;
        $completedLessonIds = [];
        $totalLessons = $course->lessons->count();
        $completedLessonsCount = 0;
        $nextLessonSlug = $firstLessonSlug;

        if ($user !== null) {
            $enrollment = DB::table(table: 'course_user')
                ->where(column: 'course_id', operator: '=', value: $course->id)
                ->where(column: 'user_id', operator: '=', value: $user->id)
                ->first();

            $isEnrolled = $enrollment !== null;

            if ($isEnrolled === true) {
                $completedLessonIds = DB::table(table: 'lesson_user')
                    ->where(column: 'user_id', operator: '=', value: $user->id)
                    ->whereIn(column: 'lesson_id', values: $course->lessons->pluck('id'))
                    ->whereNotNull(columns: 'completed_at')
                    ->pluck(column: 'lesson_id')
                    ->toArray();

                $completedLessonsCount = count($completedLessonIds);

                // Find next incomplete lesson
                $nextLesson = $course->lessons
                    ->sortBy('order')
                    ->first(fn ($lesson) => ! in_array($lesson->id, $completedLessonIds, true));

                $nextLessonSlug = $nextLesson?->slug ?? $firstLessonSlug;
            }
        }

        return Inertia::render('learn/courses/show', [
            'course' => CourseResource::from($course)
                ->include('description', 'description_html', 'learning_objectives', 'duration_seconds', 'experience_level', 'publish_date', 'lessons', 'tags', 'user', 'started_count', 'completed_count'),
            'firstLessonSlug' => $firstLessonSlug,
            'nextLessonSlug' => $nextLessonSlug,
            'isEnrolled' => $isEnrolled,
            'completedLessonIds' => $completedLessonIds,
            'totalLessons' => $totalLessons,
            'completedLessonsCount' => $completedLessonsCount,
        ]);
    }
}
