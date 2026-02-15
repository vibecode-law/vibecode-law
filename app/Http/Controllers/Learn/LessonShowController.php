<?php

namespace App\Http\Controllers\Learn;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Course\CourseResource;
use App\Http\Resources\Course\LessonResource;
use App\Models\Course\Course;
use App\Models\Course\Lesson;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class LessonShowController extends BaseController
{
    public function __invoke(Course $course, Lesson $lesson): Response|RedirectResponse
    {
        $lesson->load('course');
        $course->load('lessons', 'tags');

        $user = auth()->user();

        // Check if lesson is gated and user is not enrolled
        if ($lesson->gated === true && $user === null) {
            return Redirect::route(
                route: 'learn.courses.show',
                parameters: ['course' => $course->slug]
            )->with(key: 'flash', value: [
                'error' => 'Please enroll in the course to access this lesson.',
            ]);
        }

        $isEnrolled = false;

        if ($user !== null) {
            $enrollment = DB::table(table: 'course_user')
                ->where(column: 'course_id', operator: '=', value: $course->id)
                ->where(column: 'user_id', operator: '=', value: $user->id)
                ->first();

            $isEnrolled = $enrollment !== null;

            // Check if gated and not enrolled
            if ($lesson->gated === true && $isEnrolled === false) {
                return Redirect::route(
                    route: 'learn.courses.show',
                    parameters: ['course' => $course->slug]
                )->with(key: 'flash', value: [
                    'error' => 'Please enroll in the course to access this lesson.',
                ]);
            }

            // Auto-mark viewed_at and started_at if enrolled
            if ($isEnrolled === true) {
                // Mark lesson as viewed/started
                $lessonUserRecord = DB::table(table: 'lesson_user')
                    ->where(column: 'user_id', operator: '=', value: $user->id)
                    ->where(column: 'lesson_id', operator: '=', value: $lesson->id)
                    ->first();

                if ($lessonUserRecord === null) {
                    DB::table(table: 'lesson_user')->insert([
                        'user_id' => $user->id,
                        'lesson_id' => $lesson->id,
                        'viewed_at' => Carbon::now(),
                        'started_at' => Carbon::now(),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                } else {
                    DB::table(table: 'lesson_user')
                        ->where(column: 'user_id', operator: '=', value: $user->id)
                        ->where(column: 'lesson_id', operator: '=', value: $lesson->id)
                        ->update([
                            'viewed_at' => $lessonUserRecord->viewed_at ?? Carbon::now(),
                            'started_at' => $lessonUserRecord->started_at ?? Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ]);
                }

                // Mark course as viewed/started if not already
                $courseUserRecord = DB::table(table: 'course_user')
                    ->where(column: 'course_id', operator: '=', value: $course->id)
                    ->where(column: 'user_id', operator: '=', value: $user->id)
                    ->first();

                if ($courseUserRecord !== null) {
                    $updates = [];

                    if ($courseUserRecord->viewed_at === null) {
                        $updates['viewed_at'] = Carbon::now();
                    }

                    if ($courseUserRecord->started_at === null) {
                        $updates['started_at'] = Carbon::now();
                    }

                    if (count($updates) > 0) {
                        DB::table(table: 'course_user')
                            ->where(column: 'course_id', operator: '=', value: $course->id)
                            ->where(column: 'user_id', operator: '=', value: $user->id)
                            ->update(values: $updates);
                    }
                }
            }
        }

        // Get previous and next lessons
        $lessons = $course->lessons->sortBy('order')->values();
        $currentIndex = $lessons->search(function ($l) use ($lesson) {
            return $l->id === $lesson->id;
        });

        $previousLesson = $currentIndex > 0 ? $lessons[$currentIndex - 1] : null;
        $nextLesson = $currentIndex < $lessons->count() - 1 ? $lessons[$currentIndex + 1] : null;

        // Get completed lesson IDs for progress indicators
        $completedLessonIds = [];

        if ($user !== null && $isEnrolled === true) {
            $completedLessonIds = DB::table(table: 'lesson_user')
                ->where(column: 'user_id', operator: '=', value: $user->id)
                ->whereNotNull(columns: 'completed_at')
                ->pluck(column: 'lesson_id')
                ->toArray();
        }

        return Inertia::render('learn/courses/lessons/show', [
            'lesson' => LessonResource::from($lesson)
                ->include('description', 'description_html', 'copy', 'copy_html', 'learning_objectives', 'duration_seconds', 'asset_id', 'playback_id', 'host', 'transcript', 'gated'),
            'course' => CourseResource::from($course)
                ->include('lessons', 'tags')
                ->only('id', 'slug', 'title', 'tagline', 'lessons', 'tags'),
            'previousLesson' => $previousLesson ? [
                'slug' => $previousLesson->slug,
                'title' => $previousLesson->title,
            ] : null,
            'nextLesson' => $nextLesson ? [
                'slug' => $nextLesson->slug,
                'title' => $nextLesson->title,
            ] : null,
            'isEnrolled' => $isEnrolled,
            'completedLessonIds' => $completedLessonIds,
        ]);
    }
}
