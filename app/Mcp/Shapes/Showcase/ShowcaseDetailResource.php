<?php

namespace App\Mcp\Shapes\Showcase;

use App\Mcp\Shapes\Challenge\ChallengeReferenceResource;
use App\Mcp\Shapes\PracticeAreaResource;
use App\Mcp\Shapes\User\UserSummaryResource;
use App\Models\Challenge\Challenge;
use App\Models\PracticeArea;
use App\Models\Showcase\Showcase;
use App\Models\Showcase\ShowcaseImage;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Resource;

/**
 * The summary fields (id, slug, title, tagline, status, submitted_date) are
 * always rendered. Every other field is lazy: it is only resolved when
 * explicitly included, so callers that don't request a relation-backed field
 * never load it, and callers that do request one fail loudly under
 * Model::shouldBeStrict() if the relation was not eager loaded.
 */
class ShowcaseDetailResource extends Resource
{
    public int $id;

    public string $slug;

    public string $title;

    public string $tagline;

    public string $status;

    public ?string $submitted_date;

    public ?int $user_id;

    public Lazy|string $description;

    public Lazy|string|null $key_features;

    public Lazy|string|null $help_needed;

    public Lazy|string|null $url;

    public Lazy|string|null $video_url;

    public Lazy|string $source_status;

    public Lazy|string|null $source_url;

    public Lazy|int $view_count;

    public Lazy|int $upvote_count;

    public Lazy|string|null $thumbnail_url;

    /** @var Lazy|array<int, string> */
    public Lazy|array $image_urls;

    public Lazy|UserSummaryResource|null $user;

    /** @var Lazy|DataCollection<int, PracticeAreaResource> */
    public Lazy|DataCollection $practice_areas;

    /** @var Lazy|DataCollection<int, ChallengeReferenceResource> */
    public Lazy|DataCollection $challenges;

    public Lazy|string|null $youtube_id;

    public Lazy|string $created_at;

    public Lazy|string $updated_at;

    public static function fromModel(Showcase $showcase): self
    {
        return self::from([
            'id' => $showcase->id,
            'slug' => $showcase->slug,
            'title' => $showcase->title,
            'tagline' => $showcase->tagline,
            'status' => $showcase->status->name,
            'submitted_date' => $showcase->submitted_date?->toIso8601String(),
            'user_id' => $showcase->user_id,
            'description' => Lazy::create(fn (): string => $showcase->description),
            'key_features' => Lazy::create(fn (): ?string => $showcase->key_features),
            'help_needed' => Lazy::create(fn (): ?string => $showcase->help_needed),
            'url' => Lazy::create(fn (): ?string => $showcase->url),
            'video_url' => Lazy::create(fn (): ?string => $showcase->video_url),
            'source_status' => Lazy::create(fn (): string => $showcase->source_status->name),
            'source_url' => Lazy::create(fn (): ?string => $showcase->source_url),
            'view_count' => Lazy::create(fn (): int => (int) $showcase->view_count),
            'upvote_count' => Lazy::create(fn (): int => (int) ($showcase->upvoters_count ?? $showcase->upvoters()->count())),
            'thumbnail_url' => Lazy::create(fn (): ?string => $showcase->thumbnail_url),
            'image_urls' => Lazy::create(fn (): array => $showcase->images->map(fn (ShowcaseImage $image): string => $image->url)->values()->all()),
            'user' => Lazy::create(fn (): ?UserSummaryResource => $showcase->user !== null ? UserSummaryResource::from($showcase->user) : null),
            'practice_areas' => Lazy::create(fn (): DataCollection => PracticeAreaResource::collect(
                $showcase->practiceAreas->map(fn (PracticeArea $area): PracticeAreaResource => PracticeAreaResource::from($area)),
                DataCollection::class,
            )),
            'challenges' => Lazy::create(fn (): DataCollection => ChallengeReferenceResource::collect(
                $showcase->challenges->map(fn (Challenge $challenge): ChallengeReferenceResource => ChallengeReferenceResource::from($challenge)),
                DataCollection::class,
            )),
            'youtube_id' => Lazy::create(fn (): ?string => $showcase->youtube_id),
            'created_at' => Lazy::create(fn (): string => $showcase->created_at?->toIso8601String() ?? ''),
            'updated_at' => Lazy::create(fn (): string => $showcase->updated_at?->toIso8601String() ?? ''),
        ]);
    }
}
