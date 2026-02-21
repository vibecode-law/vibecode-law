<?php

namespace App\Http\Controllers\Staff\Courses\Lessons;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Course\CourseResource;
use App\Http\Resources\TagResource;
use App\Models\Course\Course;
use App\Models\Course\Lesson;
use App\Models\Tag;
use Inertia\Inertia;
use Inertia\Response;

class CreateController extends BaseController
{
    public function __invoke(Course $course): Response
    {
        $this->authorize('create', Lesson::class);

        return Inertia::render('staff-area/courses/lessons/create', [
            'course' => CourseResource::fromModel($course)
                ->only('id', 'slug', 'title'),
            'availableTags' => TagResource::collect(
                Tag::query()->orderBy('type')->orderBy('name')->get()
            ),
        ]);
    }
}
