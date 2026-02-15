<?php

namespace App\Models\Course;

use App\Enums\VideoHost;
use App\Models\User;
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
        'track_id',
        'embed',
        'host',
        'learning_objectives',
        'duration_seconds',
        'gated',
        'order',
        'course_id',
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
            'thumbnail_crops' => 'array',
        ];
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
