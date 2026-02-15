<?php

namespace App\Http\Controllers\Staff\Courses;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Staff\CourseStoreRequest;
use App\Models\Course\Course;
use App\Services\Course\CourseThumbnailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class StoreController extends BaseController
{
    public function __invoke(CourseStoreRequest $request): RedirectResponse
    {
        $this->authorize('create', Course::class);

        $course = Course::create(
            $request->safe()->except(['thumbnail', 'thumbnail_crops'])
        );

        $this->handleThumbnail(request: $request, course: $course);

        return Redirect::route('staff.courses.edit', $course)
            ->with('flash', [
                'message' => ['message' => 'Course created successfully.', 'type' => 'success'],
            ]);
    }

    private function handleThumbnail(CourseStoreRequest $request, Course $course): void
    {
        if ($request->hasFile('thumbnail') === false) {
            return;
        }

        new CourseThumbnailService(course: $course)
            ->fromUploadedFile(
                file: $request->file('thumbnail'),
                crops: $request->validated('thumbnail_crops'),
            );
    }
}
