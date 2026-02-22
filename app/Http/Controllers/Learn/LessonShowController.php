<?php

namespace App\Http\Controllers\Learn;

use App\Actions\Course\LogLessonProgressAction;
use App\Http\Controllers\BaseController;
use App\Http\Resources\Course\CourseResource;
use App\Http\Resources\Course\LessonResource;
use App\Models\Course\Course;
use App\Models\Course\Lesson;
use App\Models\Course\LessonUser;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class LessonShowController extends BaseController
{
    private ?User $user;

    private Course $course;

    private Lesson $lesson;

    /** @var Collection<int, Lesson> */
    private Collection $publishedLessons;

    public function __invoke(Course $course, Lesson $lesson, LogLessonProgressAction $logProgress): Response|HttpResponse
    {
        $this->user = Auth::user();

        $this->course = $course;
        $this->lesson = $lesson;

        if ($this->abortUnlessAccessible() === false) {
            $this->logView($logProgress);
        }

        $this->loadRelationships();

        $isGatedForUser = $this->lesson->gated === true && $this->user === null;

        if ($isGatedForUser === false) {
            $this->lesson->load(['transcriptLines' => fn ($query) => $query->orderBy('order')]);
        }

        if ($isGatedForUser === true) {
            Session::put('url.intended', url()->current());
        }

        [$previousLesson, $nextLesson] = $this->getSiblingLessons();

        return Inertia::render('learn/courses/lessons/show', [
            'lesson' => LessonResource::from($this->lesson)
                ->include(...$this->getLessonIncludes(isGatedForUser: $isGatedForUser)),
            'course' => CourseResource::from($this->course)
                ->include('lessons')
                ->only('id', 'slug', 'title', 'tagline', 'lessons'),
            'previousLesson' => $previousLesson,
            'nextLesson' => $nextLesson,
            'completedLessonIds' => $this->getCompletedLessonIds(),
            'lessonProgress' => $this->getLessonProgress(),
            'isGatedForUser' => $isGatedForUser,
        ]);
    }

    /**
     * @return bool Whether this is an admin preview of unpublished content.
     */
    private function abortUnlessAccessible(): bool
    {
        $isCoursePublished = $this->course->publish_date !== null && $this->course->publish_date->isPast();
        $isLessonPublished = $this->lesson->publish_date !== null && $this->lesson->publish_date->isPast();

        $isUnpublished = $isCoursePublished === false || $isLessonPublished === false;

        if ($isUnpublished === true && $this->user?->is_admin !== true) {
            abort(404);
        }

        return $isUnpublished;
    }

    private function logView(LogLessonProgressAction $logProgress): void
    {
        if ($this->user !== null) {
            $logProgress->handle(lesson: $this->lesson, user: $this->user);

            return;
        }

        $sessionKey = "lesson_progress.{$this->lesson->id}";

        /** @var array<string, mixed> $progress */
        $progress = Session::get($sessionKey, []);

        if (isset($progress['viewed_at']) === false) {
            $progress['viewed_at'] = now()->toIso8601String();
            Session::put($sessionKey, $progress);
        }
    }

    private function loadRelationships(): void
    {
        $this->course->load('visibleLessons');
        $this->course->setRelation('lessons', $this->course->visibleLessons);
        $this->lesson->load('tags', 'instructors');

        $this->publishedLessons = $this->course->visibleLessons
            ->filter(fn (Lesson $l) => $l->publish_date !== null && $l->publish_date->isPast());
    }

    /**
     * @return array<int, string>
     */
    private function getLessonIncludes(bool $isGatedForUser): array
    {
        $includes = ['description_html', 'learning_objectives_html', 'duration_seconds', 'gated', 'tags', 'instructors'];

        if ($isGatedForUser === false) {
            $includes = [...$includes, 'copy_html', 'playback_id', 'host', 'playback_tokens', 'transcript_lines'];
        }

        return $includes;
    }

    /**
     * @return array<int, int>
     */
    private function getCompletedLessonIds(): array
    {
        if ($this->user === null) {
            return [];
        }

        return LessonUser::query()
            ->where(column: 'user_id', operator: '=', value: $this->user->id)
            ->whereIn(column: 'lesson_id', values: $this->publishedLessons->pluck('id'))
            ->whereNotNull(columns: 'completed_at')
            ->pluck(column: 'lesson_id')
            ->toArray();
    }

    /**
     * @return array{started: bool, completed: bool, playback_time_seconds: int|null}
     */
    private function getLessonProgress(): array
    {
        if ($this->user !== null) {
            $pivot = LessonUser::query()
                ->where(column: 'lesson_id', operator: '=', value: $this->lesson->id)
                ->where(column: 'user_id', operator: '=', value: $this->user->id)
                ->first();

            return [
                'started' => $pivot?->started_at !== null,
                'completed' => $pivot?->completed_at !== null,
                'playback_time_seconds' => $pivot?->playback_time_seconds,
            ];
        }

        /** @var array<string, mixed> $progress */
        $progress = Session::get("lesson_progress.{$this->lesson->id}", []);

        return [
            'started' => isset($progress['started_at']),
            'completed' => isset($progress['completed_at']),
            'playback_time_seconds' => $progress['playback_time_seconds'] ?? null,
        ];
    }

    /**
     * @return array{0: array{slug: string, title: string}|null, 1: array{slug: string, title: string}|null}
     */
    private function getSiblingLessons(): array
    {
        $lessons = $this->publishedLessons->sortBy('order')->values();

        $currentIndex = $lessons->search(fn (Lesson $l) => $l->id === $this->lesson->id);

        if ($currentIndex === false) {
            return [null, null];
        }

        $previousLesson = $currentIndex > 0 ? $lessons[$currentIndex - 1] : null;
        $nextLesson = $currentIndex < $lessons->count() - 1 ? $lessons[$currentIndex + 1] : null;

        return [
            $previousLesson ? ['slug' => $previousLesson->slug, 'title' => $previousLesson->title] : null,
            $nextLesson ? ['slug' => $nextLesson->slug, 'title' => $nextLesson->title] : null,
        ];
    }
}
