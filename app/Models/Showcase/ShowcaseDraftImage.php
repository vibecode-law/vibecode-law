<?php

namespace App\Models\Showcase;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

/**
 * @property array<string, array{x: int, y: int, width: int, height: int}>|null $crops
 *
 * @mixin IdeHelperShowcaseDraftImage
 */
class ShowcaseDraftImage extends Model
{
    /** @use HasFactory<\Database\Factories\Showcase\ShowcaseDraftImageFactory> */
    use HasFactory;

    public const ACTION_KEEP = 'keep';

    public const ACTION_ADD = 'add';

    public const ACTION_REMOVE = 'remove';

    protected static function booted(): void
    {
        static::deleted(function (ShowcaseDraftImage $image) {
            // Only delete the file if this is a new image (has a path)
            if ($image->path !== null) {
                Storage::disk('public')->delete($image->path);
            }
        });
    }

    protected $fillable = [
        'showcase_draft_id',
        'original_image_id',
        'action',
        'path',
        'filename',
        'alt_text',
        'order',
        'crops',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'crops' => 'array',
        ];
    }

    //
    // Relationships
    //

    public function showcaseDraft(): BelongsTo
    {
        return $this->belongsTo(ShowcaseDraft::class);
    }

    public function originalImage(): BelongsTo
    {
        return $this->belongsTo(ShowcaseImage::class, 'original_image_id');
    }

    //
    // Helpers
    //

    public function isKeep(): bool
    {
        return $this->action === self::ACTION_KEEP;
    }

    public function isAdd(): bool
    {
        return $this->action === self::ACTION_ADD;
    }

    public function isRemove(): bool
    {
        return $this->action === self::ACTION_REMOVE;
    }

    //
    // Attributes
    //

    protected function url(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                // For kept images, return the original image URL
                if ($this->isKeep() && $this->original_image_id !== null) {
                    /** @var ShowcaseImage|null $originalImage */
                    $originalImage = $this->originalImage;

                    return $originalImage?->url;
                }

                // For new images, return the draft image URL
                if ($this->path !== null) {
                    $imageTransformBase = Config::get('services.image-transform.base_url');

                    return $imageTransformBase
                        ? $imageTransformBase.'/'.$this->path
                        : Storage::disk('public')->url($this->path);
                }

                return null;
            }
        );
    }
}
