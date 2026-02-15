<?php

namespace App\Http\Resources\Course;

use App\Models\Course\Lesson;
use App\Services\Markdown\MarkdownService;
use App\ValueObjects\FrontendEnum;
use App\ValueObjects\ImageCrop;
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

    public Lazy|string|null $learning_objectives;

    public Lazy|string|null $copy;

    public Lazy|string|null $copy_html;

    public Lazy|string|null $transcript;

    public ?string $thumbnail_url;

    /** @var array<string, string>|null */
    public ?array $thumbnail_rect_strings;

    /** @var array<string, ImageCrop>|null */
    public Lazy|array|null $thumbnail_crops;

    public Lazy|int|null $duration_seconds;

    public Lazy|string $asset_id;

    public Lazy|string $playback_id;

    public Lazy|FrontendEnum $host;

    public bool $gated;

    public bool $visible;

    public ?string $publish_date;

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
            'learning_objectives' => Lazy::create(fn () => $lesson->learning_objectives),
            'copy' => Lazy::create(fn () => $lesson->copy),
            'copy_html' => Lazy::create(fn () => $lesson->copy !== null ? $markdown->render(
                markdown: $lesson->copy,
                cacheKey: "lesson|{$lesson->id}|copy",
            ) : null),
            'transcript' => Lazy::create(fn () => $lesson->transcript),
            'thumbnail_url' => $lesson->thumbnail_url,
            'thumbnail_rect_strings' => $lesson->thumbnail_rect_strings,
            'thumbnail_crops' => Lazy::create(fn () => $lesson->thumbnail_crops !== null
                ? array_map(
                    fn (array $crop) => ImageCrop::fromArray($crop),
                    $lesson->thumbnail_crops
                )
                : null),
            'duration_seconds' => Lazy::create(fn () => $lesson->duration_seconds),
            'asset_id' => Lazy::create(fn () => $lesson->asset_id),
            'playback_id' => Lazy::create(fn () => $lesson->playback_id),
            'host' => Lazy::create(fn () => $lesson->host->forFrontend()),
            'gated' => $lesson->gated,
            'visible' => $lesson->visible,
            'publish_date' => $lesson->publish_date?->format('Y-m-d'),
            'order' => $lesson->order,
            'course' => Lazy::whenLoaded('course', $lesson, fn () => CourseResource::from($lesson->course)),
        ]);
    }
}
