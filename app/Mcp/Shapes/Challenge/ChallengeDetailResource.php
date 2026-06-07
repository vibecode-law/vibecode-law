<?php

namespace App\Mcp\Shapes\Challenge;

use App\Models\Challenge\Challenge;
use App\Models\Challenge\SubChallenge;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Resource;

class ChallengeDetailResource extends Resource
{
    public int $id;

    public string $slug;

    public string $title;

    public string $tagline;

    public string $description;

    public ?string $starts_at;

    public ?string $ends_at;

    public bool $is_active;

    public bool $is_featured;

    public string $visibility;

    public ?int $organisation_id;

    public ?int $user_id;

    public ?string $thumbnail_url;

    public int $showcases_count;

    public int $total_upvotes_count;

    /** @var Lazy|DataCollection<int, SubChallengeResource> */
    public Lazy|DataCollection $sub_challenges;

    public string $created_at;

    public string $updated_at;

    public static function fromModel(Challenge $challenge): self
    {
        return self::from([
            'id' => $challenge->id,
            'slug' => $challenge->slug,
            'title' => $challenge->title,
            'tagline' => $challenge->tagline,
            'description' => $challenge->description,
            'starts_at' => $challenge->starts_at?->toIso8601String(),
            'ends_at' => $challenge->ends_at?->toIso8601String(),
            'is_active' => (bool) $challenge->is_active,
            'is_featured' => (bool) $challenge->is_featured,
            'visibility' => $challenge->visibility->name,
            'organisation_id' => $challenge->organisation_id,
            'user_id' => $challenge->user_id,
            'thumbnail_url' => $challenge->thumbnail_url,
            'showcases_count' => (int) ($challenge->showcases_count ?? $challenge->showcases()->count()),
            'total_upvotes_count' => (int) ($challenge->total_upvotes_count ?? 0),
            'sub_challenges' => Lazy::create(fn (): DataCollection => SubChallengeResource::collect(
                $challenge->subChallenges->map(fn (SubChallenge $subChallenge): SubChallengeResource => SubChallengeResource::from($subChallenge)),
                DataCollection::class,
            )),
            'created_at' => $challenge->created_at?->toIso8601String() ?? '',
            'updated_at' => $challenge->updated_at?->toIso8601String() ?? '',
        ]);
    }
}
