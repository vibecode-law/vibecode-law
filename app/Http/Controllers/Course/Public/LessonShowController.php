<?php

namespace App\Http\Controllers\Course\Public;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Course\CourseResource;
use App\Http\Resources\Course\LessonResource;
use App\Models\Course\Course;
use App\Models\Course\Lesson;
use Inertia\Inertia;
use Inertia\Response;

class LessonShowController extends BaseController
{
    public function __invoke(Course $course, Lesson $lesson): Response
    {
        $lesson->load('course');
        $course->load('lessons', 'tags');

        return Inertia::render('learn/courses/lessons/show', [
            'lesson' => LessonResource::from($lesson)
                ->include('description', 'description_html', 'copy', 'copy_html', 'learning_objectives', 'duration_seconds', 'embed', 'host', 'transcript'),
            'course' => CourseResource::from($course)
                ->include('lessons', 'tags')
                ->only('id', 'slug', 'title', 'tagline', 'lessons', 'tags'),
        ]);
    }
}
