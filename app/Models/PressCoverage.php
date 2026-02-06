<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

/**
 * @property array{x: int, y: int, width: int, height: int}|null $thumbnail_crop
 *
 * @mixin IdeHelperPressCoverage
 */
class PressCoverage extends Model
{
    /** @use HasFactory<\Database\Factories\PressCoverageFactory> */
    use HasFactory;

    protected $table = 'press_coverage';

    protected $fillable = [
        'title',
        'publication_name',
        'publication_date',
        'url',
        'excerpt',
        'thumbnail_extension',
        'thumbnail_crop',
        'is_published',
        'display_order',
    ];

    protected function casts(): array
    {
        return [
            'publication_date' => 'date',
            'thumbnail_crop' => 'array',
            'is_published' => 'boolean',
            'display_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::deleted(function (PressCoverage $pressCoverage) {
            if ($pressCoverage->thumbnail_extension !== null) {
                $storagePath = "press-coverage/{$pressCoverage->id}";
                Storage::disk('public')->delete("{$storagePath}/thumbnail.{$pressCoverage->thumbnail_extension}");
            }
        });
    }

    // Scopes
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('is_published', true);
    }

    // Thumbnail URL - follows Showcase pattern exactly
    protected function thumbnailUrl(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                if ($this->thumbnail_extension === null) {
                    return null;
                }

                $imageTransformBase = Config::get('services.image-transform.base_url');
                $path = "press-coverage/{$this->id}/thumbnail.{$this->thumbnail_extension}";

                if ($imageTransformBase === null) {
                    return Storage::disk('public')->url($path);
                }

                return $imageTransformBase.'/'.$path;
            }
        );
    }

    // Thumbnail rect string for image transformation
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
