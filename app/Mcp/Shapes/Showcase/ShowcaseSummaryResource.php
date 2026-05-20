<?php

namespace App\Mcp\Shapes\Showcase;

use App\Models\Showcase\Showcase;
use Spatie\LaravelData\Resource;

class ShowcaseSummaryResource extends Resource
{
    public int $id;

    public string $slug;

    public string $title;

    public string $tagline;

    public string $status;

    public ?string $submitted_date;

    public static function fromModel(Showcase $showcase): self
    {
        return self::from([
            'id' => $showcase->id,
            'slug' => $showcase->slug,
            'title' => $showcase->title,
            'tagline' => $showcase->tagline,
            'status' => $showcase->status->name,
            'submitted_date' => $showcase->submitted_date?->toIso8601String(),
        ]);
    }
}
