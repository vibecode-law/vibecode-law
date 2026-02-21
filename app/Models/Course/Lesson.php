<?php

namespace App\Models\Course;

use App\Enums\MarkdownProfile;
use App\Enums\VideoHost;
use App\Models\User;
use App\Services\Markdown\MarkdownService;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'asset_id',
        'playback_id',
        'host',
        'learning_objectives',
        'duration_seconds',
        'gated',
        'order',
        'course_id',
        'allow_preview',
        'publish_date',
        'thumbnail_filename',
        'thumbnail_crops',
    ];

    protected function casts(): array
    {
        return [
            'host' => VideoHost::class,
            'gated' => 'boolean',
            'duration_seconds' => 'integer',
            'order' => 'integer',
            'allow_preview' => 'boolean',
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
    // Scopes
    //

    #[Scope]
    protected function published(Builder $query): void
    {
        $query->whereNotNull('publish_date')->where('publish_date', '<=', now());
    }

    #[Scope]
    protected function visible(Builder $query): void
    {
        $query->where(function (Builder $q): void {
            $q->where(function (Builder $q): void {
                $q->whereNotNull('publish_date')->where('publish_date', '<=', now());
            })->orWhere('allow_preview', true);
        });
    }

    //
    // Attributes
    //

    protected function thumbnailUrl(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                if ($this->thumbnail_filename === null) {
                    return null;
                }

                $imageTransformBase = Config::get('services.image-transform.base_url');

                $path = "lesson/{$this->id}/{$this->thumbnail_filename}";

                if ($imageTransformBase === null) {
                    return Storage::disk('public')->url($path);
                }

                return $imageTransformBase.'/'.$path;
            }
        );
    }

    protected function transcriptVtt(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => once(fn () => Storage::get("lessons/{$this->id}/transcript.vtt")),
        );
    }

    protected function transcriptTxt(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => once(fn () => Storage::get("lessons/{$this->id}/transcript.txt")),
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

    public function instructors(): BelongsToMany
    {
        return $this->belongsToMany(related: User::class, table: 'instructor_lesson');
    }

    public function transcriptLines(): HasMany
    {
        return $this->hasMany(related: LessonTranscriptLine::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(related: \App\Models\Tag::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(related: User::class)
            ->using(LessonUser::class)
            ->withPivot('viewed_at', 'started_at', 'completed_at', 'playback_time_seconds')
            ->withTimestamps();
    }
}
