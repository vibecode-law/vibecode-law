<?php

namespace App\Http\Resources\Course;

use App\Http\Resources\User\UserResource;
use App\Models\Course\Course;
use App\Services\Markdown\MarkdownService;
use App\ValueObjects\FrontendEnum;
use App\ValueObjects\ImageCrop;
use Illuminate\Support\Carbon;
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

    public Lazy|string|null $learning_objectives;

    public Lazy|string|null $learning_objectives_html;

    public Lazy|int|null $duration_seconds;

    public Lazy|FrontendEnum|null $experience_level;

    public ?string $thumbnail_url;

    /** @var array<string, string>|null */
    public ?array $thumbnail_rect_strings;

    /** @var array<string, ImageCrop>|null */
    public Lazy|array|null $thumbnail_crops;

    public bool $visible;

    public bool $is_featured;

    public Lazy|string|null $publish_date;

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
            'thumbnail_url' => $course->thumbnail_url,
            'thumbnail_rect_strings' => $course->thumbnail_rect_strings,
            'thumbnail_crops' => Lazy::create(fn () => $course->thumbnail_crops !== null
                ? array_map(
                    fn (array $crop) => ImageCrop::fromArray($crop),
                    $course->thumbnail_crops
                )
                : null),
            'learning_objectives' => Lazy::create(fn () => $course->learning_objectives),
            'learning_objectives_html' => Lazy::create(fn () => $course->learning_objectives !== null ? $markdown->render(
                markdown: $course->learning_objectives,
                cacheKey: "course|{$course->id}|learning_objectives",
            ) : null),
            'duration_seconds' => Lazy::create(fn () => $course->duration_seconds),
            'experience_level' => Lazy::create(fn () => $course->experience_level?->forFrontend()),
            'visible' => $course->visible,
            'is_featured' => $course->is_featured,
            'publish_date' => Lazy::create(fn () => $course->publish_date instanceof Carbon ? $course->publish_date->toDateString() : null),
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
