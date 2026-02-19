<?php

namespace App\Http\Controllers\Learn;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Course\CourseResource;
use App\Models\Course\Course;
use App\Models\Course\CourseUser;
use App\Models\Course\Lesson;
use App\Models\Course\LessonUser;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\LaravelData\DataCollection;

class LearnIndexController extends BaseController
{
    public function __invoke(): Response
    {
        $learnEnabled = Config::get('app.learn_enabled') === true || Auth::user()?->is_admin === true;

        $courses = collect();
        $totalEnrolledUsers = 0;

        if ($learnEnabled === true) {
            $courses = Course::query()
                ->select('id', 'slug', 'title', 'tagline', 'order', 'experience_level', 'duration_seconds', 'allow_preview', 'is_featured', 'publish_date', 'thumbnail_filename', 'thumbnail_crops')
                ->visible()
                ->withCount('visibleLessons as lessons_count')
                ->orderBy('order')
                ->get();

            $totalEnrolledUsers = CourseUser::query()
                ->distinct()
                ->count(columns: 'user_id');
        }

        return Inertia::render('learn/courses/index', [
            'courses' => $learnEnabled === true
                ? CourseResource::collect($courses, DataCollection::class)
                    ->include('experience_level', 'lessons_count', 'started_count', 'duration_seconds')
                    ->only('id', 'slug', 'title', 'tagline', 'experience_level', 'order', 'lessons_count', 'started_count', 'duration_seconds', 'thumbnail_url', 'thumbnail_rect_strings', 'is_previewable')
                : [],
            'courseProgress' => $learnEnabled === true ? $this->getCourseProgress($courses) : [],
            'guides' => $this->getGuides(),
            'totalEnrolledUsers' => $totalEnrolledUsers,
        ]);
    }

    /**
     * @param  Collection<int, Course>  $courses
     * @return array<int, array{progressPercentage: int|float}>
     */
    private function getCourseProgress(Collection $courses): array
    {
        /** @var User|null $user */
        $user = Auth::user();

        if ($user === null) {
            return [];
        }

        $completedCounts = LessonUser::query()
            ->join(table: 'lessons', first: 'lesson_user.lesson_id', operator: '=', second: 'lessons.id')
            ->where(column: 'lesson_user.user_id', operator: '=', value: $user->id)
            ->whereNotNull(columns: 'lesson_user.completed_at')
            ->whereIn(column: 'lesson_user.lesson_id', values: Lesson::query()->visible()->select('id'))
            ->selectRaw('lessons.course_id, COUNT(*) as completed_count')
            ->groupBy('lessons.course_id')
            ->pluck('completed_count', 'lessons.course_id');

        $courseProgress = [];

        foreach ($courses as $course) {
            $completedCount = $completedCounts->get($course->id, 0);
            $totalLessons = $course->lessons_count;

            $courseProgress[$course->id] = [
                'progressPercentage' => $totalLessons > 0
                    ? round(($completedCount / $totalLessons) * 100)
                    : 0,
            ];
        }

        return $courseProgress;
    }

    /**
     * @return array<int, array{name: string, slug: string, summary: string, icon: string, route: string}>
     */
    private function getGuides(): array
    {
        /** @var array<int, array{title: string, slug: string, summary: string, icon: string}> $childrenConfig */
        $childrenConfig = Config::get(key: 'content.guides.children', default: []);

        return collect($childrenConfig)->map(fn (array $child): array => [
            'name' => $child['title'],
            'slug' => $child['slug'],
            'summary' => $child['summary'],
            'icon' => $child['icon'],
            'route' => route(name: 'learn.guides.show', parameters: ['slug' => $child['slug']]),
        ])->all();
    }
}
