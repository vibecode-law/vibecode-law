<?php

namespace App\Http\Controllers\Learn;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Course\CourseResource;
use App\Http\Resources\Course\LessonResource;
use App\Models\Course\Course;
use App\Models\Course\Lesson;
use App\Models\Course\LessonUser;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class LessonShowController extends BaseController
{
    public function __invoke(Course $course, Lesson $lesson): Response
    {
        $course->load('lessons', 'tags');

        /** @var User|null $user */
        $user = Auth::user();

        $completedLessonIds = $user !== null
            ? $this->getCompletedLessonIds(course: $course, user: $user)
            : [];

        [$previousLesson, $nextLesson] = $this->getSiblingLessons(course: $course, lesson: $lesson);

        return Inertia::render('learn/courses/lessons/show', [
            'lesson' => LessonResource::from($lesson)
                ->include('description_html', 'copy_html', 'learning_objectives_html', 'duration_seconds', 'transcript', 'gated'),
            'course' => CourseResource::from($course)
                ->include('lessons', 'tags')
                ->only('id', 'slug', 'title', 'tagline', 'lessons', 'tags'),
            'previousLesson' => $previousLesson,
            'nextLesson' => $nextLesson,
            'completedLessonIds' => $completedLessonIds,
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
     * @return array{0: array{slug: string, title: string}|null, 1: array{slug: string, title: string}|null}
     */
    private function getSiblingLessons(Course $course, Lesson $lesson): array
    {
        $lessons = $course->lessons->sortBy('order')->values();

        $currentIndex = $lessons->search(fn (Lesson $l) => $l->id === $lesson->id);

        $previousLesson = $currentIndex > 0 ? $lessons[$currentIndex - 1] : null;
        $nextLesson = $currentIndex < $lessons->count() - 1 ? $lessons[$currentIndex + 1] : null;

        return [
            $previousLesson ? ['slug' => $previousLesson->slug, 'title' => $previousLesson->title] : null,
            $nextLesson ? ['slug' => $nextLesson->slug, 'title' => $nextLesson->title] : null,
        ];
    }
}
