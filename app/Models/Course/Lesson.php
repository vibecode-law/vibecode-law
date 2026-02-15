<?php

namespace App\Models\Course;

use App\Enums\MarkdownProfile;
use App\Enums\VideoHost;
use App\Models\User;
use App\Services\Markdown\MarkdownService;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

/**
 * @property array<string, array{x: int, y: int, width: int, height: int}>|null $thumbnail_crops
 *
 * @mixin IdeHelperLesson
 */
class Lesson extends Model
{
    /** @use HasFactory<\Database\Factories\Course\LessonFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'tagline',
        'description',
        'copy',
        'transcript',
        'caption_track_id',
        'asset_id',
        'playback_id',
        'host',
        'learning_objectives',
        'duration_seconds',
        'gated',
        'order',
        'course_id',
        'visible',
        'publish_date',
        'thumbnail_extension',
        'thumbnail_crops',
    ];

    protected function casts(): array
    {
        return [
            'host' => VideoHost::class,
            'gated' => 'boolean',
            'duration_seconds' => 'integer',
            'order' => 'integer',
            'visible' => 'boolean',
            'publish_date' => 'date',
            'thumbnail_crops' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::updated(function (Lesson $lesson): void {
            $cached = $lesson->getCachedFields();

            foreach ($lesson->changes as $field => $value) {
                if (in_array($field, $cached) === false) {
                    continue;
                }

                app(MarkdownService::class)->clearCacheByKey(
                    cacheKey: "lesson|{$lesson->id}|$field",
                    profile: MarkdownProfile::Basic
                );
            }
        });

        static::deleted(function (Lesson $lesson): void {
            $markdownService = app(MarkdownService::class);

            foreach ($lesson->getCachedFields() as $cacheKey) {
                $markdownService->clearCacheByKey(
                    cacheKey: "lesson|{$lesson->id}|$cacheKey",
                    profile: MarkdownProfile::Basic
                );
            }
        });
    }

    /**
     * @return array<int, string>
     */
    public function getCachedFields(): array
    {
        return ['description', 'learning_objectives', 'copy'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    //
    // Attributes
    //

    protected function thumbnailUrl(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                if ($this->thumbnail_extension === null) {
                    return null;
                }

                $imageTransformBase = Config::get('services.image-transform.base_url');

                $path = "lesson/{$this->id}/thumbnail.{$this->thumbnail_extension}";

                if ($imageTransformBase === null) {
                    return Storage::disk('public')->url($path);
                }

                return $imageTransformBase.'/'.$path;
            }
        );
    }

    /**
     * @return Attribute<array<string, string>|null, never>
     */
    protected function thumbnailRectStrings(): Attribute
    {
        return Attribute::make(
            get: function (): ?array {
                if ($this->thumbnail_crops === null) {
                    return null;
                }

                /** @var array<string, array{x: int, y: int, width: int, height: int}> $crops */
                $crops = $this->thumbnail_crops;

                $result = [];

                foreach ($crops as $key => $crop) {
                    $result[$key] = sprintf(
                        'rect=%d,%d,%d,%d',
                        $crop['x'],
                        $crop['y'],
                        $crop['width'],
                        $crop['height']
                    );
                }

                return $result;
            }
        );
    }

    //
    // Relationships
    //

    public function course(): BelongsTo
    {
        return $this->belongsTo(related: Course::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(related: User::class)
            ->using(LessonUser::class)
            ->withPivot('viewed_at', 'started_at', 'completed_at', 'playback_time_milliseconds')
            ->withTimestamps();
    }
}
