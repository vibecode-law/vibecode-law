<?php

namespace App\Http\Controllers\Course\Public;

use App\Actions\Course\MarkLessonCompleteAction;
use App\Http\Controllers\BaseController;
use App\Models\Course\Course;
use App\Models\Course\Lesson;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class LessonCompleteController extends BaseController
{
    public function __invoke(Course $course, Lesson $lesson, MarkLessonCompleteAction $markCompleteAction): JsonResponse
    {
        $user = Auth::user();

        if ($user === null) {
            abort(code: 401);
        }

        // Verify the lesson belongs to the course
        if ($lesson->course_id !== $course->id) {
            abort(code: 404);
        }

        // Mark the lesson as complete
        $markCompleteAction($lesson, $user);

        return response()->json(data: [
            'success' => true,
            'message' => 'Lesson marked as complete',
        ]);
    }
}
