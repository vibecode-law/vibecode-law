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
 * @mixin IdeHelperShowcaseImage
 */
class ShowcaseImage extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::deleted(function (ShowcaseImage $image) {
            Storage::disk('public')->delete($image->path);
        });
    }

    protected $fillable = [
        'showcase_id',
        'path',
        'filename',
        'order',
        'alt_text',
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

    public function showcase(): BelongsTo
    {
        return $this->belongsTo(Showcase::class);
    }

    //
    // Attributes
    //

    protected function url(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $imageTransformBase = Config::get('services.image-transform.base_url');

                return $imageTransformBase
                    ? $imageTransformBase.'/'.$this->path
                    : Storage::disk('public')->url($this->path);
            }
        );
    }
}
