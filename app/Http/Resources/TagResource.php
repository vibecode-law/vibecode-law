<?php

namespace App\Http\Resources;

use App\Models\Tag;
use App\ValueObjects\FrontendEnum;
use Spatie\LaravelData\Resource;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class TagResource extends Resource
{
    public int $id;

    public string $name;

    public string $slug;

    public FrontendEnum $type;

    public static function fromModel(Tag $tag): self
    {
        return self::from([
            'id' => $tag->id,
            'name' => $tag->name,
            'slug' => $tag->slug,
            'type' => $tag->type->forFrontend(),
        ]);
    }
}
