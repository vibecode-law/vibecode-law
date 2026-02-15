<?php

namespace App\Http\Controllers\Staff\Courses;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Course\CourseResource;
use App\Models\Course\Course;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\LaravelData\DataCollection;

class IndexController extends BaseController
{
    public function __invoke(Request $request): Response
    {
        $this->authorize('viewAny', Course::class);

        return Inertia::render('staff-area/courses/index', [
            'courses' => $this->getCourses(),
        ]);
    }

    /**
     * @return DataCollection<int, CourseResource>
     */
    private function getCourses(): DataCollection
    {
        $courses = Course::query()
            ->withCount('lessons')
            ->orderBy('order')
            ->get();

        return CourseResource::collect($courses, DataCollection::class)
            ->include('lessons_count')
            ->only(
                'id',
                'slug',
                'title',
                'tagline',
                'visible',
                'is_featured',
                'order',
                'lessons_count',
                'thumbnail_url',
            );
    }
}
