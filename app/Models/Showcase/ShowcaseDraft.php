<?php

namespace App\Models\Showcase;

use App\Enums\ShowcaseDraftStatus;
use App\Enums\SourceStatus;
use App\Models\PracticeArea;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

/**
 * @property array{x: int, y: int, width: int, height: int}|null $thumbnail_crop
 * @mixin IdeHelperShowcaseDraft
 */
class ShowcaseDraft extends Model
{
    /** @use HasFactory<\Database\Factories\Showcase\ShowcaseDraftFactory> */
    use HasFactory;

    protected $fillable = [
        'showcase_id',
        'title',
        'tagline',
        'description',
        'key_features',
        'help_needed',
        'url',
        'video_url',
        'source_status',
        'source_url',
        'thumbnail_extension',
        'thumbnail_crop',
        'status',
        'submitted_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => ShowcaseDraftStatus::class,
            'source_status' => SourceStatus::class,
            'thumbnail_crop' => 'array',
            'submitted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::deleted(function (ShowcaseDraft $draft) {
            // Delete draft thumbnail and images folder
            Storage::disk('public')->deleteDirectory("showcase-drafts/{$draft->id}");
        });
    }

    //
    // Relationships
    //

    public function showcase(): BelongsTo
    {
        return $this->belongsTo(Showcase::class);
    }

    public function practiceAreas(): BelongsToMany
    {
        return $this->belongsToMany(
            related: PracticeArea::class,
            table: 'practice_area_showcase_draft',
        )->withTimestamps();
    }

    public function images(): HasMany
    {
        return $this->hasMany(ShowcaseDraftImage::class)->orderBy('order');
    }

    //
    // Helpers
    //

    public function isDraft(): bool
    {
        return $this->status === ShowcaseDraftStatus::Draft;
    }

    public function isPending(): bool
    {
        return $this->status === ShowcaseDraftStatus::Pending;
    }

    public function isRejected(): bool
    {
        return $this->status === ShowcaseDraftStatus::Rejected;
    }

    public function canBeEdited(): bool
    {
        return $this->status === ShowcaseDraftStatus::Draft
            || $this->status === ShowcaseDraftStatus::Rejected;
    }

    public function canBeSubmitted(): bool
    {
        return $this->status === ShowcaseDraftStatus::Draft
            || $this->status === ShowcaseDraftStatus::Rejected;
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

                $path = "showcase-drafts/{$this->id}/thumbnail.{$this->thumbnail_extension}";

                if ($imageTransformBase === null) {
                    return Storage::disk('public')->url($path);
                }

                return $imageTransformBase.'/'.$path;
            }
        );
    }

    protected function thumbnailRectString(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                if ($this->thumbnail_crop === null) {
                    return null;
                }

                /** @var array{x: int, y: int, width: int, height: int} $crop */
                $crop = $this->thumbnail_crop;

                return sprintf(
                    'rect=%d,%d,%d,%d',
                    $crop['x'],
                    $crop['y'],
                    $crop['width'],
                    $crop['height']
                );
            }
        );
    }
}
