<?php

namespace App\Http\Resources;

use App\Models\PressCoverage;
use App\ValueObjects\ImageCrop;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Resource;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class PressCoverageResource extends Resource
{
    public int $id;

    public string $title;

    public string $publication_name;

    public string $publication_date;

    public string $url;

    public ?string $excerpt;

    public Lazy|string|null $thumbnail_extension;

    public ?string $thumbnail_url;

    public ?string $thumbnail_rect_string;

    public Lazy|ImageCrop|null $thumbnail_crop;

    public bool $is_published;

    public int $display_order;

    public static function fromModel(PressCoverage $pressCoverage): self
    {
        return self::from([
            'id' => $pressCoverage->id,
            'title' => $pressCoverage->title,
            'publication_name' => $pressCoverage->publication_name,
            'publication_date' => $pressCoverage->publication_date->format('Y-m-d'),
            'url' => $pressCoverage->url,
            'excerpt' => $pressCoverage->excerpt,
            'thumbnail_extension' => Lazy::create(fn () => $pressCoverage->thumbnail_extension),
            'thumbnail_url' => $pressCoverage->thumbnail_url,
            'thumbnail_rect_string' => $pressCoverage->thumbnail_rect_string,
            'thumbnail_crop' => Lazy::create(fn () => $pressCoverage->thumbnail_crop !== null
                ? ImageCrop::fromArray($pressCoverage->thumbnail_crop)
                : null),
            'is_published' => $pressCoverage->is_published,
            'display_order' => $pressCoverage->display_order,
        ]);
    }
}
