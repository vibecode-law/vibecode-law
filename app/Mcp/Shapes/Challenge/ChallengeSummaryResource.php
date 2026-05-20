<?php

namespace App\Mcp\Shapes\Challenge;

use App\Models\Challenge\Challenge;
use Spatie\LaravelData\Resource;

class ChallengeSummaryResource extends Resource
{
    public int $id;

    public string $slug;

    public string $title;

    public string $tagline;

    public ?string $starts_at;

    public ?string $ends_at;

    public bool $is_active;

    public string $visibility;

    public static function fromModel(Challenge $challenge): self
    {
        return self::from([
            'id' => $challenge->id,
            'slug' => $challenge->slug,
            'title' => $challenge->title,
            'tagline' => $challenge->tagline,
            'starts_at' => $challenge->starts_at?->toIso8601String(),
            'ends_at' => $challenge->ends_at?->toIso8601String(),
            'is_active' => (bool) $challenge->is_active,
            'visibility' => $challenge->visibility->name,
        ]);
    }
}
