<?php

namespace App\Http\Controllers\Course\Public;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Course\CourseResource;
use App\Models\Course\Course;
use Inertia\Inertia;
use Inertia\Response;

class CourseShowController extends BaseController
{
    public function __invoke(Course $course): Response
    {
        $course->load('lessons', 'tags', 'user');

        // Get first lesson slug
        $firstLessonSlug = $course->lessons->sortBy('order')->first()?->slug;

        return Inertia::render('learn/courses/show', [
            'course' => CourseResource::from($course)
                ->include('description', 'description_html', 'learning_objectives', 'duration_seconds', 'experience_level', 'publish_date', 'lessons', 'tags', 'user', 'started_count', 'completed_count'),
            'firstLessonSlug' => $firstLessonSlug,
        ]);
    }
}
