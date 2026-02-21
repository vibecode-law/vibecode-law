<?php

namespace App\Http\Controllers\Staff\Courses\Lessons;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Course\CourseResource;
use App\Http\Resources\Course\LessonResource;
use App\Models\Course\Course;
use App\Models\Course\Lesson;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\LaravelData\DataCollection;

class IndexController extends BaseController
{
    public function __invoke(Request $request, Course $course): Response
    {
        $this->authorize('viewAny', Lesson::class);

        return Inertia::render('staff-area/courses/lessons/index', [
            'course' => CourseResource::fromModel($course)
                ->only('id', 'slug', 'title'),
            'lessons' => $this->getLessons(course: $course),
        ]);
    }

    /**
     * @return DataCollection<int, LessonResource>
     */
    private function getLessons(Course $course): DataCollection
    {
        $lessons = $course->lessons()
            ->orderBy('order')
            ->get();

        return LessonResource::collect($lessons, DataCollection::class)
            ->only(
                'id',
                'slug',
                'title',
                'tagline',
                'gated',
                'is_previewable',
                'is_scheduled',
                'order',
                'thumbnail_url',
            );
    }
}
