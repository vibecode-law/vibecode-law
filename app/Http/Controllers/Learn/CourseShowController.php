<?php

namespace App\Http\Controllers\Learn;

use App\Actions\Course\LogCourseViewAction;
use App\Http\Controllers\BaseController;
use App\Http\Resources\Course\CourseResource;
use App\Models\Course\Course;
use App\Models\Course\Lesson;
use App\Models\Course\LessonUser;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class CourseShowController extends BaseController
{
    private bool $isAdminPreview = false;

    public function __invoke(Course $course, LogCourseViewAction $logCourseView): Response|HttpResponse
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (Config::get('app.learn_enabled') === false && $user?->is_admin !== true) {
            abort(404);
        }

        $this->abortUnlessAccessible(course: $course, user: $user);

        if ($this->isAdminPreview === false && $user !== null) {
            $logCourseView->handle(course: $course, user: $user);
        }

        $course->load('visibleLessons.instructors', 'tags');
        $course->setRelation('lessons', $course->visibleLessons);

        $publishedLessons = $course->visibleLessons
            ->filter(fn (Lesson $lesson) => $lesson->publish_date !== null && $lesson->publish_date->isPast());

        $completedLessonIds = $user !== null
            ? $this->getCompletedLessonIds(publishedLessons: $publishedLessons, user: $user)
            : [];

        return Inertia::render('learn/courses/show', [
            'course' => CourseResource::from($course)
                ->include('description_html', 'learning_objectives_html', 'duration_seconds', 'experience_level', 'publish_date', 'lessons', 'lessons.instructors', 'tags', 'instructors', 'started_count'),
            'nextLessonSlug' => $this->getNextLessonSlug(publishedLessons: $publishedLessons, completedLessonIds: $completedLessonIds),
            'completedLessonIds' => $completedLessonIds,
        ]);
    }

    private function abortUnlessAccessible(Course $course, ?User $user): void
    {
        $isPublished = $course->publish_date !== null && $course->publish_date->isPast();
        $isPreviewable = $course->allow_preview === true && $isPublished === false;

        if ($isPublished === false && $isPreviewable === false && $user?->is_admin !== true) {
            abort(404);
        }

        $this->isAdminPreview = $user?->is_admin === true && $isPublished === false && $isPreviewable === false;
    }

    /**
     * @param  Collection<int, Lesson>  $publishedLessons
     * @return array<int, int>
     */
    private function getCompletedLessonIds(Collection $publishedLessons, User $user): array
    {
        return LessonUser::query()
            ->where(column: 'user_id', operator: '=', value: $user->id)
            ->whereIn(column: 'lesson_id', values: $publishedLessons->pluck('id'))
            ->whereNotNull(columns: 'completed_at')
            ->pluck(column: 'lesson_id')
            ->toArray();
    }

    /**
     * @param  Collection<int, Lesson>  $publishedLessons
     * @param  array<int, int>  $completedLessonIds
     */
    private function getNextLessonSlug(Collection $publishedLessons, array $completedLessonIds): ?string
    {
        $firstLessonSlug = $publishedLessons->first()?->slug;

        if (count($completedLessonIds) === 0) {
            return $firstLessonSlug;
        }

        $nextLesson = $publishedLessons
            ->first(fn (Lesson $lesson) => in_array($lesson->id, $completedLessonIds, true) === false);

        return $nextLesson->slug ?? $firstLessonSlug;
    }
}
