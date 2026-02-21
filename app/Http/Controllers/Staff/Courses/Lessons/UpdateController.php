<?php

namespace App\Http\Controllers\Staff\Courses\Lessons;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Staff\LessonUpdateRequest;
use App\Models\Course\Course;
use App\Models\Course\Lesson;
use App\Services\Course\LessonThumbnailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class UpdateController extends BaseController
{
    public function __invoke(LessonUpdateRequest $request, Course $course, Lesson $lesson): RedirectResponse
    {
        $this->authorize('update', $lesson);

        $data = $request->safe()->except(['thumbnail', 'thumbnail_crops', 'remove_thumbnail', 'tags', 'instructor_ids']);

        $lesson->update($data);

        $lesson->tags()->sync($request->validated('tags') ?? []);
        $lesson->instructors()->sync($request->validated('instructor_ids') ?? []);

        $this->handleThumbnail(request: $request, lesson: $lesson);

        return Redirect::route('staff.academy.courses.lessons.edit', [$course, $lesson])
            ->with('flash', [
                'message' => ['message' => 'Lesson updated successfully.', 'type' => 'success'],
            ]);
    }

    private function handleThumbnail(LessonUpdateRequest $request, Lesson $lesson): void
    {
        $thumbnailService = new LessonThumbnailService(lesson: $lesson);

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
