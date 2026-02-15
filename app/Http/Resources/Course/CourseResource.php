<?php

namespace App\Http\Resources\Course;

use App\Http\Resources\User\UserResource;
use App\Models\Course\Course;
use App\Services\Markdown\MarkdownService;
use App\ValueObjects\FrontendEnum;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Resource;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class CourseResource extends Resource
{
    public int $id;

    public string $slug;

    public string $title;

    public string $tagline;

    public Lazy|string $description;

    public Lazy|string $description_html;

    public Lazy|FrontendEnum|null $experience_level;

    public int $order;

    public Lazy|int $lessons_count;

    public Lazy|LessonResource $lessons;

    public Lazy|CourseTagResource $tags;

    public Lazy|UserResource|null $user;

    public Lazy|int $started_count;

    public Lazy|int $completed_count;

    public static function fromModel(Course $course): self
    {
        $markdown = app(abstract: MarkdownService::class);

        return self::from([
            'id' => $course->id,
            'slug' => $course->slug,
            'title' => $course->title,
            'tagline' => $course->tagline,
            'description' => Lazy::create(fn () => $course->description),
            'description_html' => Lazy::create(fn () => $markdown->render(
                markdown: $course->description,
                cacheKey: "course|{$course->id}|description",
            )),
            'experience_level' => Lazy::create(fn () => $course->experience_level?->forFrontend()),
            'order' => $course->order,
            'lessons_count' => Lazy::when(
                condition: fn () => $course->hasAttribute('lessons_count'),
                value: fn () => $course->lessons_count,
            ),
            'lessons' => Lazy::whenLoaded('lessons', $course, fn () => LessonResource::collect($course->lessons)),
            'tags' => Lazy::whenLoaded('tags', $course, fn () => CourseTagResource::collect($course->tags)),
            'user' => Lazy::whenLoaded('user', $course, fn () => $course->user !== null ? UserResource::from($course->user) : null),
            'started_count' => Lazy::create(fn () => $course->started_count),
            'completed_count' => Lazy::create(fn () => $course->completed_count),
        ]);
    }
}
