<?php

namespace App\Http\Controllers\Staff\Courses;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Staff\CoursePublishDateRequest;
use App\Models\Course\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class PublishDateController extends BaseController
{
    public function __invoke(CoursePublishDateRequest $request, Course $course): RedirectResponse
    {
        $data = [];
        $messages = [];

        if ($request->has('publish_date')) {
            $data['publish_date'] = $request->validated('publish_date');
            $messages[] = $request->validated('publish_date') !== null
                ? 'Publish date set successfully.'
                : 'Publish date cleared.';
        }

        if ($request->has('allow_preview')) {
            $data['allow_preview'] = $request->boolean('allow_preview');
            $messages[] = $request->boolean('allow_preview') === true
                ? 'Preview enabled.'
                : 'Preview disabled.';
        }

        $course->update($data);

        return Redirect::route('staff.academy.courses.edit', $course)
            ->with('flash', [
                'message' => ['message' => implode(' ', $messages), 'type' => 'success'],
            ]);
    }
}
