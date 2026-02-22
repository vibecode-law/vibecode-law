<?php

namespace App\Models\Course;

use App\Concerns\ClearsMarkdownCache;
use App\Enums\ExperienceLevel;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

/**
 * @property array<string, array{x: int, y: int, width: int, height: int}>|null $thumbnail_crops
 *
 * @mixin IdeHelperCourse
 */
class Course extends Model
{
    /** @use HasFactory<\Database\Factories\Course\CourseFactory> */
    use ClearsMarkdownCache, HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'tagline',
        'description',
        'learning_objectives',
        'duration_seconds',
        'order',
        'experience_level',
        'allow_preview',
        'is_featured',
        'publish_date',
        'thumbnail_filename',
        'thumbnail_crops',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'duration_seconds' => 'integer',
            'experience_level' => ExperienceLevel::class,
            'allow_preview' => 'boolean',
            'is_featured' => 'boolean',
            'publish_date' => 'date',
            'started_count' => 'integer',
            'completed_count' => 'integer',
            'thumbnail_crops' => 'array',
        ];
    }

    /**
     * @return array<int, string>
     */
    public function getCachedFields(): array
    {
        return ['description', 'learning_objectives'];
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

                $path = "course/{$this->id}/{$this->thumbnail_filename}";

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

    protected function startedCount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->users()->whereNotNull('started_at')->count()
        );
    }

    protected function completedCount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->users()->whereNotNull('completed_at')->count()
        );
    }

    //
    // Relationships
    //

    public function lessons(): HasMany
    {
        return $this->hasMany(related: Lesson::class)->orderBy('order');
    }

    public function visibleLessons(): HasMany
    {
        return $this->hasMany(related: Lesson::class)->visible()->orderBy('order');
    }

    public function publishedLessons(): HasMany
    {
        return $this->hasMany(related: Lesson::class)->published()->orderBy('order');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(related: \App\Models\Tag::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(related: User::class)
            ->using(CourseUser::class)
            ->withPivot('viewed_at', 'started_at', 'completed_at')
            ->withTimestamps();
    }
}
