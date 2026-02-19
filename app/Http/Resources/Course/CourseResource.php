<?php

namespace App\Http\Resources\Course;

use App\Http\Resources\TagResource;
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

    public ?string $tagline;

    public Lazy|string|null $description;

    public Lazy|string|null $description_html;

    public Lazy|string|null $learning_objectives;

    public Lazy|string|null $learning_objectives_html;

    public Lazy|int|null $duration_seconds;

    public Lazy|FrontendEnum|null $experience_level;

    public ?string $thumbnail_url;

    /** @var array<string, string>|null */
    public ?array $thumbnail_rect_strings;

    /** @var array<string, ImageCrop>|null */
    public Lazy|array|null $thumbnail_crops;

    public bool $allow_preview;

    public bool $is_previewable;

    public bool $is_scheduled;

    public bool $is_featured;

    public Lazy|string|null $publish_date;

    public int $order;

    public Lazy|int $lessons_count;

    public Lazy|LessonResource $lessons;

    public Lazy|TagResource $tags;

    /** @var Lazy|UserResource[] */
    public Lazy|array $instructors;

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
            'description_html' => Lazy::create(fn () => $course->description !== null ? $markdown->render(
                markdown: $course->description,
                cacheKey: "course|{$course->id}|description",
            ) : null),
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
            'allow_preview' => $course->allow_preview,
            'is_previewable' => $course->allow_preview === true && ($course->publish_date === null || $course->publish_date->isFuture()),
            'is_scheduled' => $course->allow_preview === false && ($course->publish_date === null || $course->publish_date->isFuture()),
            'is_featured' => $course->is_featured,
            'publish_date' => Lazy::create(fn () => $course->publish_date instanceof Carbon ? $course->publish_date->toDateString() : null),
            'order' => $course->order,
            'lessons_count' => Lazy::when(
                condition: fn () => $course->hasAttribute('lessons_count'),
                value: fn () => $course->lessons_count,
            ),
            'lessons' => Lazy::whenLoaded('lessons', $course, fn () => LessonResource::collect($course->lessons)),
            'tags' => Lazy::whenLoaded('tags', $course, fn () => TagResource::collect($course->tags)),
            'instructors' => Lazy::whenLoaded('lessons', $course, function () use ($course) {
                $uniqueInstructors = $course->lessons
                    ->flatMap(fn ($lesson) => $lesson->relationLoaded('instructors') ? $lesson->instructors : collect())
                    ->unique('id')
                    ->values();

                return UserResource::collect($uniqueInstructors);
            }),
            'started_count' => Lazy::create(fn () => $course->started_count),
            'completed_count' => Lazy::create(fn () => $course->completed_count),
        ]);
    }
}
