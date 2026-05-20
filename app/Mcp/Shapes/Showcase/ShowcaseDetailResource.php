<?php

namespace App\Mcp\Shapes\Showcase;

use App\Mcp\Shapes\PracticeAreaResource;
use App\Models\PracticeArea;
use App\Models\Showcase\Showcase;
use App\Models\Showcase\ShowcaseImage;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Resource;

class ShowcaseDetailResource extends Resource
{
    public int $id;

    public string $slug;

    public string $title;

    public string $tagline;

    public string $description;

    public ?string $key_features;

    public ?string $help_needed;

    public ?string $url;

    public ?string $video_url;

    public string $source_status;

    public ?string $source_url;

    public string $status;

    public ?string $submitted_date;

    public int $view_count;

    public int $upvote_count;

    public ?string $thumbnail_url;

    /** @var array<int, string> */
    public array $image_urls;

    public ?int $user_id;

    /** @var DataCollection<int, PracticeAreaResource> */
    public DataCollection $practice_areas;

    public ?string $youtube_id;

    public string $created_at;

    public string $updated_at;

    public static function fromModel(Showcase $showcase): self
    {
        return self::from([
            'id' => $showcase->id,
            'slug' => $showcase->slug,
            'title' => $showcase->title,
            'tagline' => $showcase->tagline,
            'description' => $showcase->description,
            'key_features' => $showcase->key_features,
            'help_needed' => $showcase->help_needed,
            'url' => $showcase->url,
            'video_url' => $showcase->video_url,
            'source_status' => $showcase->source_status->name,
            'source_url' => $showcase->source_url,
            'status' => $showcase->status->name,
            'submitted_date' => $showcase->submitted_date?->toIso8601String(),
            'view_count' => (int) $showcase->view_count,
            'upvote_count' => (int) ($showcase->upvoters_count ?? $showcase->upvoters()->count()),
            'thumbnail_url' => $showcase->thumbnail_url,
            'image_urls' => $showcase->images->map(fn (ShowcaseImage $image): string => $image->url)->values()->all(),
            'user_id' => $showcase->user_id,
            'practice_areas' => PracticeAreaResource::collect(
                $showcase->practiceAreas->map(fn (PracticeArea $area): PracticeAreaResource => PracticeAreaResource::from($area)),
                DataCollection::class,
            ),
            'youtube_id' => $showcase->youtube_id,
            'created_at' => $showcase->created_at?->toIso8601String() ?? '',
            'updated_at' => $showcase->updated_at?->toIso8601String() ?? '',
        ]);
    }
}
