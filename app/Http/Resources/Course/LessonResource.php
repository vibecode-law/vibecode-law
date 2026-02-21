<?php

namespace App\Http\Resources\Course;

use App\Enums\MarkdownProfile;
use App\Http\Resources\TagResource;
use App\Http\Resources\User\UserResource;
use App\Models\Course\Lesson;
use App\Services\Markdown\MarkdownService;
use App\Services\VideoHost\Contracts\VideoHostService;
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

    public ?string $tagline;

    public Lazy|string|null $description;

    public Lazy|string|null $description_html;

    public Lazy|string|null $learning_objectives;

    public Lazy|string|null $learning_objectives_html;

    public Lazy|string|null $copy;

    public Lazy|string|null $copy_html;

    public Lazy|bool $has_transcript_lines;

    public ?string $thumbnail_url;

    /** @var array<string, string>|null */
    public ?array $thumbnail_rect_strings;

    /** @var array<string, ImageCrop>|null */
    public Lazy|array|null $thumbnail_crops;

    public Lazy|int|null $duration_seconds;

    public Lazy|string $asset_id;

    public Lazy|string $playback_id;

    public Lazy|bool $has_vtt_transcript;

    public Lazy|bool $has_txt_transcript;

    public Lazy|FrontendEnum|null $host;

    /** @var Lazy|array<string, string> */
    public Lazy|array $playback_tokens;

    public bool $gated;

    public bool $allow_preview;

    public bool $is_previewable;

    public bool $is_scheduled;

    public ?string $publish_date;

    public int $order;

    public Lazy|CourseResource $course;

    public Lazy|TagResource $tags;

    /** @var Lazy|LessonTranscriptLineResource[] */
    public Lazy|array $transcript_lines;

    /** @var Lazy|UserResource[] */
    public Lazy|array $instructors;

    public static function fromModel(Lesson $lesson): self
    {
        $markdown = app(abstract: MarkdownService::class);

        return self::from([
            'id' => $lesson->id,
            'slug' => $lesson->slug,
            'title' => $lesson->title,
            'tagline' => $lesson->tagline,
            'description' => Lazy::create(fn () => $lesson->description),
            'description_html' => Lazy::create(fn () => $lesson->description !== null ? $markdown->render(
                markdown: $lesson->description,
                cacheKey: "lesson|{$lesson->id}|description",
            ) : null),
            'learning_objectives' => Lazy::create(fn () => $lesson->learning_objectives),
            'learning_objectives_html' => Lazy::create(fn () => $lesson->learning_objectives !== null ? $markdown->render(
                markdown: $lesson->learning_objectives,
                cacheKey: "lesson|{$lesson->id}|learning_objectives",
            ) : null),
            'copy' => Lazy::create(fn () => $lesson->copy),
            'copy_html' => Lazy::create(fn () => $lesson->copy !== null ? $markdown->render(
                markdown: $lesson->copy,
                profile: MarkdownProfile::Full,
                cacheKey: "lesson|{$lesson->id}|copy",
            ) : null),
            'has_transcript_lines' => Lazy::create(fn () => $lesson->transcriptLines()->exists()),
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
            'has_vtt_transcript' => Lazy::create(fn () => $lesson->transcript_vtt !== null),
            'has_txt_transcript' => Lazy::create(fn () => $lesson->transcript_txt !== null),
            'host' => Lazy::create(fn () => $lesson->host?->forFrontend()),
            'playback_tokens' => Lazy::create(fn () => $lesson->playback_id !== null
                ? app(VideoHostService::class)->generatePlaybackTokens(playbackId: $lesson->playback_id)
                : []),
            'gated' => $lesson->gated,
            'allow_preview' => $lesson->allow_preview,
            'is_previewable' => $lesson->allow_preview === true && ($lesson->publish_date === null || $lesson->publish_date->isFuture()),
            'is_scheduled' => $lesson->allow_preview === false && ($lesson->publish_date === null || $lesson->publish_date->isFuture()),
            'publish_date' => $lesson->publish_date?->format('Y-m-d'),
            'order' => $lesson->order,
            'course' => Lazy::whenLoaded('course', $lesson, fn () => CourseResource::from($lesson->course)),
            'transcript_lines' => Lazy::whenLoaded('transcriptLines', $lesson, fn () => LessonTranscriptLineResource::collect($lesson->transcriptLines)),
            'tags' => Lazy::whenLoaded('tags', $lesson, fn () => TagResource::collect($lesson->tags)),
            'instructors' => Lazy::whenLoaded('instructors', $lesson, fn () => UserResource::collect($lesson->instructors)),
        ]);
    }
}
