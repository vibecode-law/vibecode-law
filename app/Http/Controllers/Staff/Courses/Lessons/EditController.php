<?php

namespace App\Http\Controllers\Staff\Courses\Lessons;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Course\CourseResource;
use App\Http\Resources\Course\LessonResource;
use App\Models\Course\Course;
use App\Models\Course\Lesson;
use Inertia\Inertia;
use Inertia\Response;

class EditController extends BaseController
{
    public function __invoke(Course $course, Lesson $lesson): Response
    {
        $this->authorize('view', $lesson);

        return Inertia::render('staff-area/courses/lessons/edit', [
            'course' => CourseResource::fromModel($course)
                ->only('id', 'slug', 'title'),
            'lesson' => LessonResource::fromModel($lesson)
                ->include('description', 'learning_objectives', 'copy', 'asset_id', 'thumbnail_crops')
                ->only(
                    'id',
                    'slug',
                    'title',
                    'tagline',
                    'description',
                    'learning_objectives',
                    'copy',
                    'gated',
                    'visible',
                    'publish_date',
                    'order',
                    'asset_id',
                    'thumbnail_url',
                    'thumbnail_rect_strings',
                    'thumbnail_crops',
                ),
        ]);
    }
}
