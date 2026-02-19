<?php

namespace App\Http\Controllers\Staff\Courses;

use App\Enums\ExperienceLevel;
use App\Http\Controllers\BaseController;
use App\Http\Resources\Course\CourseResource;
use App\Http\Resources\TagResource;
use App\Models\Course\Course;
use App\Models\Tag;
use Inertia\Inertia;
use Inertia\Response;

class EditController extends BaseController
{
    public function __invoke(Course $course): Response
    {
        $this->authorize('view', $course);

        $course->load('tags', 'lessons');
        $course->loadCount('lessons');

        return Inertia::render('staff-area/courses/edit', [
            'course' => CourseResource::fromModel($course)
                ->include('description', 'learning_objectives', 'experience_level', 'publish_date', 'thumbnail_crops', 'lessons_count', 'tags', 'lessons')
                ->only(
                    'id',
                    'slug',
                    'title',
                    'tagline',
                    'description',
                    'learning_objectives',
                    'experience_level',
                    'allow_preview',
                    'is_previewable',
                    'is_scheduled',
                    'is_featured',
                    'publish_date',
                    'order',
                    'thumbnail_url',
                    'thumbnail_rect_strings',
                    'thumbnail_crops',
                    'lessons_count',
                    'tags',
                    'lessons',
                ),
            'experienceLevels' => array_map(
                fn (ExperienceLevel $level) => $level->forFrontend(),
                ExperienceLevel::cases()
            ),
            'availableTags' => TagResource::collect(
                Tag::query()->orderBy('type')->orderBy('name')->get()
            ),
        ]);
    }
}
