<?php

namespace App\Http\Resources\Organisation;

use App\Models\Organisation\Organisation;
use App\ValueObjects\ImageCrop;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Resource;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class OrganisationResource extends Resource
{
    public int $id;

    public string $name;

    public ?string $tagline;

    public ?string $thumbnail_url;

    /** @var array<string, string>|null */
    public ?array $thumbnail_rect_strings;

    /** @var array<string, ImageCrop>|null */
    public Lazy|array|null $thumbnail_crops;

    public Lazy|string|null $about;

    public static function fromModel(Organisation $organisation): self
    {
        return self::from([
            'id' => $organisation->id,
            'name' => $organisation->name,
            'tagline' => $organisation->tagline,
            'thumbnail_url' => $organisation->thumbnail_url,
            'thumbnail_rect_strings' => $organisation->thumbnail_rect_strings,
            'thumbnail_crops' => Lazy::create(fn () => $organisation->thumbnail_crops !== null
                ? array_map(
                    fn (array $crop) => ImageCrop::fromArray($crop),
                    $organisation->thumbnail_crops
                )
                : null),
            'about' => Lazy::create(fn () => $organisation->about),
        ]);
    }
}
