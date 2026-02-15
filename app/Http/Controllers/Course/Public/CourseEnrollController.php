<?php

namespace App\Http\Controllers\Course\Public;

use App\Actions\Course\EnrollUserInCourseAction;
use App\Http\Controllers\BaseController;
use App\Models\Course\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class CourseEnrollController extends BaseController
{
    public function __invoke(Course $course, EnrollUserInCourseAction $enrollAction): RedirectResponse
    {
        $user = auth()->user();

        if ($user === null) {
            abort(code: 401);
        }

        // Enroll the user in the course
        $enrollAction($course, $user);

        // Redirect to the first lesson
        $firstLesson = $course->lessons()->orderBy(column: 'order')->first();

        if ($firstLesson === null) {
            // If no lessons, redirect back to course page
            return Redirect::route(
                route: 'learn.courses.show',
                parameters: ['course' => $course->slug]
            )->with(key: 'flash', value: [
                'success' => 'You have been enrolled in the course!',
            ]);
        }

        /** @var \App\Models\Course\Lesson $firstLesson */

        return Redirect::route(
            route: 'learn.courses.lessons.show',
            parameters: [
                'course' => $course->slug,
                'lesson' => $firstLesson->slug,
            ]
        )->with(key: 'flash', value: [
            'success' => 'You have been enrolled in the course!',
        ]);
    }
}
