<?php

namespace App\Http\Resources\Course;

use App\Models\Course\Lesson;
use App\Services\Markdown\MarkdownService;
use App\ValueObjects\FrontendEnum;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Resource;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class LessonResource extends Resource
{
    public int $id;

    public string $slug;

    public string $title;

    public string $tagline;

    public Lazy|string $description;

    public Lazy|string $description_html;

    public Lazy|string|null $copy;

    public Lazy|string|null $copy_html;

    public Lazy|string|null $transcript;

    public Lazy|string $embed;

    public Lazy|FrontendEnum $host;

    public bool $gated;

    public int $order;

    public Lazy|CourseResource $course;

    public static function fromModel(Lesson $lesson): self
    {
        $markdown = app(abstract: MarkdownService::class);

        return self::from([
            'id' => $lesson->id,
            'slug' => $lesson->slug,
            'title' => $lesson->title,
            'tagline' => $lesson->tagline,
            'description' => Lazy::create(fn () => $lesson->description),
            'description_html' => Lazy::create(fn () => $markdown->render(
                markdown: $lesson->description,
                cacheKey: "lesson|{$lesson->id}|description",
            )),
            'copy' => Lazy::create(fn () => $lesson->copy),
            'copy_html' => Lazy::create(fn () => $lesson->copy !== null ? $markdown->render(
                markdown: $lesson->copy,
                cacheKey: "lesson|{$lesson->id}|copy",
            ) : null),
            'transcript' => Lazy::create(fn () => $lesson->transcript),
            'embed' => Lazy::create(fn () => $lesson->embed),
            'host' => Lazy::create(fn () => $lesson->host->forFrontend()),
            'gated' => $lesson->gated,
            'order' => $lesson->order,
            'course' => Lazy::whenLoaded('course', $lesson, fn () => CourseResource::from($lesson->course)),
        ]);
    }
}
