<?php

namespace App\Http\Controllers\Staff\Courses\Lessons;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Staff\LessonPublishDateRequest;
use App\Models\Course\Course;
use App\Models\Course\Lesson;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class PublishDateController extends BaseController
{
    public function __invoke(LessonPublishDateRequest $request, Course $course, Lesson $lesson): RedirectResponse
    {
        $lesson->update([
            'publish_date' => $request->validated('publish_date'),
        ]);

        $message = $request->validated('publish_date') !== null
            ? 'Publish date set successfully.'
            : 'Publish date cleared.';

        return Redirect::route('staff.academy.courses.lessons.edit', [$course, $lesson])
            ->with('flash', [
                'message' => ['message' => $message, 'type' => 'success'],
            ]);
    }
}
