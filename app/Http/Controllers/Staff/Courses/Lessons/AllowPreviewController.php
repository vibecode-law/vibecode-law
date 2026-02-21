<?php

namespace App\Http\Controllers\Staff\Courses\Lessons;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Staff\LessonAllowPreviewRequest;
use App\Models\Course\Course;
use App\Models\Course\Lesson;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class AllowPreviewController extends BaseController
{
    public function __invoke(LessonAllowPreviewRequest $request, Course $course, Lesson $lesson): RedirectResponse
    {
        $lesson->update([
            'allow_preview' => $request->boolean('allow_preview'),
        ]);

        $message = $request->boolean('allow_preview') === true
            ? 'Preview enabled.'
            : 'Preview disabled.';

        return Redirect::route('staff.academy.courses.lessons.edit', [$course, $lesson])
            ->with('flash', [
                'message' => ['message' => $message, 'type' => 'success'],
            ]);
    }
}
