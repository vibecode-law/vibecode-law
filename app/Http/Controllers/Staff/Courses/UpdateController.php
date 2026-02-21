<?php

namespace App\Http\Controllers\Staff\Courses;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Staff\CourseUpdateRequest;
use App\Models\Course\Course;
use App\Services\Course\CourseThumbnailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class UpdateController extends BaseController
{
    public function __invoke(CourseUpdateRequest $request, Course $course): RedirectResponse
    {
        $this->authorize('update', $course);

        $course->update(
            $request->safe()->except(['thumbnail', 'thumbnail_crops', 'remove_thumbnail', 'tags'])
        );

        $course->tags()->sync($request->validated('tags') ?? []);

        $this->handleThumbnail(request: $request, course: $course);

        return Redirect::route('staff.academy.courses.edit', $course)
            ->with('flash', [
                'message' => ['message' => 'Course updated successfully.', 'type' => 'success'],
            ]);
    }

    private function handleThumbnail(CourseUpdateRequest $request, Course $course): void
    {
        $thumbnailService = new CourseThumbnailService(course: $course);

        if ($request->boolean('remove_thumbnail') === true) {
            $thumbnailService->delete();

            return;
        }

        if ($request->hasFile('thumbnail') === true) {
            $thumbnailService->fromUploadedFile(
                file: $request->file('thumbnail'),
                crops: $request->validated('thumbnail_crops'),
            );

            return;
        }

        if ($request->has('thumbnail_crops') === true) {
            $thumbnailService->updateCrops(
                crops: $request->validated('thumbnail_crops'),
            );
        }
    }
}
