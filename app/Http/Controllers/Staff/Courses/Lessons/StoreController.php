<?php

namespace App\Http\Controllers\Staff\Courses\Lessons;

use App\Enums\VideoHost;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Staff\LessonStoreRequest;
use App\Models\Course\Course;
use App\Models\Course\Lesson;
use App\Services\Course\LessonThumbnailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class StoreController extends BaseController
{
    public function __invoke(LessonStoreRequest $request, Course $course): RedirectResponse
    {
        $this->authorize('create', Lesson::class);

        $data = $request->safe()->except(['thumbnail', 'thumbnail_crops']);
        $data['course_id'] = $course->id;

        if (isset($data['asset_id']) && $data['asset_id'] !== '') {
            $data['host'] = VideoHost::Mux;
        }

        $lesson = Lesson::create($data);

        $this->handleThumbnail(request: $request, lesson: $lesson);

        return Redirect::route('staff.courses.lessons.edit', [$course, $lesson])
            ->with('flash', [
                'message' => ['message' => 'Lesson created successfully.', 'type' => 'success'],
            ]);
    }

    private function handleThumbnail(LessonStoreRequest $request, Lesson $lesson): void
    {
        if ($request->hasFile('thumbnail') === false) {
            return;
        }

        new LessonThumbnailService(lesson: $lesson)
            ->fromUploadedFile(
                file: $request->file('thumbnail'),
                crops: $request->validated('thumbnail_crops'),
            );
    }
}
