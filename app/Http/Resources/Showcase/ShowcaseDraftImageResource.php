<?php

namespace App\Http\Resources\Showcase;

use App\Models\Showcase\ShowcaseDraftImage;
use App\Models\Showcase\ShowcaseImage;
use App\ValueObjects\ImageCrop;
use Spatie\LaravelData\Resource;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class ShowcaseDraftImageResource extends Resource
{
    public int $id;

    public ?int $original_image_id;

    public string $action;

    public ?string $filename;

    public int $order;

    public ?string $alt_text;

    public ?string $url;

    /** @var array<string, ImageCrop>|null */
    public ?array $crops;

    public static function fromModel(ShowcaseDraftImage $image): self
    {
        /** @var ShowcaseImage|null $originalImage */
        $originalImage = $image->originalImage;

        $filename = $image->filename ?? $originalImage?->filename;
        $crops = $image->crops ?? $originalImage?->crops;

        return self::from([
            'id' => $image->id,
            'original_image_id' => $image->original_image_id,
            'action' => $image->action,
            'filename' => $filename,
            'order' => $image->order,
            'alt_text' => $image->alt_text,
            'url' => $image->url,
            'crops' => $crops,
        ]);
    }
}
