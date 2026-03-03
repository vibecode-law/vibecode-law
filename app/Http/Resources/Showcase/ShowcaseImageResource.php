<?php

namespace App\Http\Resources\Showcase;

use App\Models\Showcase\ShowcaseImage;
use App\ValueObjects\ImageCrop;
use Spatie\LaravelData\Resource;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class ShowcaseImageResource extends Resource
{
    public int $id;

    public string $filename;

    public int $order;

    public ?string $alt_text;

    public string $url;

    /** @var array<string, ImageCrop>|null */
    public ?array $crops;

    public static function fromModel(ShowcaseImage $image): self
    {
        return self::from([
            'id' => $image->id,
            'filename' => $image->filename,
            'order' => $image->order,
            'alt_text' => $image->alt_text,
            'url' => $image->url,
            'crops' => $image->crops,
        ]);
    }
}
