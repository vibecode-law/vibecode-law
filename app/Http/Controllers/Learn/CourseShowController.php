<?php

namespace App\Http\Controllers\Learn;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Course\CourseResource;
use App\Models\Course\Course;
use App\Models\Course\LessonUser;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CourseShowController extends BaseController
{
    public function __invoke(Course $course): Response
    {
        $course->load('lessons', 'user');

        /** @var User|null $user */
        $user = Auth::user();

        $completedLessonIds = $user !== null
            ? $this->getCompletedLessonIds(course: $course, user: $user)
            : [];

        return Inertia::render('learn/courses/show', [
            'course' => CourseResource::from($course)
                ->include('description_html', 'learning_objectives_html', 'duration_seconds', 'experience_level', 'publish_date', 'lessons', 'user', 'started_count'),
            'nextLessonSlug' => $this->getNextLessonSlug(course: $course, completedLessonIds: $completedLessonIds),
            'completedLessonIds' => $completedLessonIds,
            'totalLessons' => $course->lessons->count(),
        ]);
    }

    /**
     * @return array<int, int>
     */
    private function getCompletedLessonIds(Course $course, User $user): array
    {
        return LessonUser::query()
            ->where('user_id', $user->id)
            ->whereIn('lesson_id', $course->lessons->pluck('id'))
            ->whereNotNull('completed_at')
            ->pluck('lesson_id')
            ->toArray();
    }

    /**
     * @param  array<int, int>  $completedLessonIds
     */
    private function getNextLessonSlug(Course $course, array $completedLessonIds): ?string
    {
        $firstLessonSlug = $course->lessons->first()?->slug;

        if (count($completedLessonIds) === 0) {
            return $firstLessonSlug;
        }

        $nextLesson = $course->lessons
            ->first(fn ($lesson) => ! in_array($lesson->id, $completedLessonIds, true));

        return $nextLesson->slug ?? $firstLessonSlug;
    }
}
