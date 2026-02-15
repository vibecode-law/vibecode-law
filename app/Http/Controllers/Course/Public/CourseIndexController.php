<?php

namespace App\Http\Controllers\Course\Public;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Course\CourseResource;
use App\Models\Course\Course;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\LaravelData\DataCollection;

class CourseIndexController extends BaseController
{
    public function __invoke(): Response
    {
        $courses = Course::query()
            ->with('tags')
            ->withCount('lessons')
            ->orderBy('order')
            ->get();

        return Inertia::render('learn/courses/index', [
            'courses' => CourseResource::collect($courses, DataCollection::class)
                ->include('experience_level', 'lessons_count', 'tags', 'started_count', 'completed_count')
                ->only('id', 'slug', 'title', 'tagline', 'experience_level', 'order', 'lessons_count', 'tags', 'started_count', 'completed_count'),
        ]);
    }
}
