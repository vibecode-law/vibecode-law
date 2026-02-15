<?php

namespace App\Http\Controllers\Course\Public;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Course\CourseResource;
use App\Models\Course\Course;
use App\Services\Content\ContentService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\LaravelData\DataCollection;

class CourseIndexController extends BaseController
{
    public function __construct(
        private ContentService $contentService,
    ) {}

    public function __invoke(): Response
    {
        $courses = Course::query()
            ->with('user', 'tags')
            ->withCount('lessons')
            ->orderBy('order')
            ->get();

        // Get total unique users who have ever enrolled in any course
        $totalEnrolledUsers = DB::table(table: 'course_user')
            ->distinct()
            ->count(columns: 'user_id');

        $user = auth()->user();
        $courseProgress = [];

        // If user is authenticated, get their progress for each course
        if ($user !== null) {
            $enrollments = DB::table(table: 'course_user')
                ->where(column: 'user_id', operator: '=', value: $user->id)
                ->get()
                ->keyBy('course_id');

            foreach ($courses as $course) {
                $enrollment = $enrollments->get($course->id);

                if ($enrollment !== null) {
                    // Get completed lessons count for this course
                    $completedCount = DB::table(table: 'lesson_user')
                        ->join(table: 'lessons', first: 'lesson_user.lesson_id', operator: '=', second: 'lessons.id')
                        ->where(column: 'lessons.course_id', operator: '=', value: $course->id)
                        ->where(column: 'lesson_user.user_id', operator: '=', value: $user->id)
                        ->whereNotNull(columns: 'lesson_user.completed_at')
                        ->count();

                    $totalLessons = $course->lessons_count;
                    $progressPercentage = $totalLessons > 0
                        ? round(($completedCount / $totalLessons) * 100)
                        : 0;

                    $courseProgress[$course->id] = [
                        'isEnrolled' => true,
                        'progressPercentage' => $progressPercentage,
                        'isComplete' => $enrollment->completed_at !== null,
                    ];
                } else {
                    $courseProgress[$course->id] = [
                        'isEnrolled' => false,
                        'progressPercentage' => 0,
                        'isComplete' => false,
                    ];
                }
            }
        }

        return Inertia::render('learn/courses/index', [
            'courses' => CourseResource::collect($courses, DataCollection::class)
                ->include('experience_level', 'lessons_count', 'tags', 'user', 'started_count', 'duration_seconds')
                ->only('id', 'slug', 'title', 'tagline', 'experience_level', 'order', 'lessons_count', 'tags', 'user', 'started_count', 'duration_seconds', 'thumbnail_url', 'thumbnail_rect_strings'),
            'courseProgress' => $courseProgress,
            'guides' => $this->getGuides(),
            'totalEnrolledUsers' => $totalEnrolledUsers,
        ]);
    }

    /**
     * @return array<int, array{name: string, slug: string, summary: string, icon: string, route: string}>
     */
    private function getGuides(): array
    {
        /** @var array<int, array{title: string, slug: string, summary: string, icon: string}> $childrenConfig */
        $childrenConfig = Config::get(key: 'content.resources.children', default: []);

        return collect($childrenConfig)->map(fn (array $child): array => [
            'name' => $child['title'],
            'slug' => $child['slug'],
            'summary' => $child['summary'],
            'icon' => $child['icon'],
            'route' => route(name: 'resources.show', parameters: ['slug' => $child['slug']]),
        ])->all();
    }
}
