<?php

namespace App\Http\Resources\Challenge;

use App\Http\Resources\Organisation\OrganisationResource;
use App\Models\Challenge\Challenge;
use App\Services\Markdown\MarkdownService;
use App\ValueObjects\ImageCrop;
use Carbon\CarbonInterface;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Resource;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class ChallengeResource extends Resource
{
    public int $id;

    public string $slug;

    public string $title;

    public string $tagline;

    public Lazy|string $description;

    public Lazy|string $description_html;

    #[WithCast(DateTimeInterfaceCast::class)]
    public ?CarbonInterface $starts_at;

    #[WithCast(DateTimeInterfaceCast::class)]
    public ?CarbonInterface $ends_at;

    public bool $is_active;

    public bool $is_featured;

    public ?string $thumbnail_url;

    /** @var array<string, string>|null */
    public ?array $thumbnail_rect_strings;

    /** @var array<string, ImageCrop>|null */
    public Lazy|array|null $thumbnail_crops;

    public Lazy|OrganisationResource|null $organisation;

    public Lazy|int|null $showcases_count;

    public Lazy|int|null $total_upvotes_count;

    public static function fromModel(Challenge $challenge): self
    {
        $markdown = app(abstract: MarkdownService::class);

        return self::from([
            'id' => $challenge->id,
            'slug' => $challenge->slug,
            'title' => $challenge->title,
            'tagline' => $challenge->tagline,
            'description' => Lazy::create(fn () => $challenge->description)->defaultIncluded(),
            'description_html' => Lazy::create(fn () => $markdown->render(
                markdown: $challenge->description,
                cacheKey: "challenge|{$challenge->id}|description",
            )),
            'starts_at' => $challenge->starts_at,
            'ends_at' => $challenge->ends_at,
            'is_active' => $challenge->is_active,
            'is_featured' => $challenge->is_featured,
            'thumbnail_url' => $challenge->thumbnail_url,
            'thumbnail_rect_strings' => $challenge->thumbnail_rect_strings,
            'thumbnail_crops' => Lazy::create(fn () => $challenge->thumbnail_crops !== null
                ? array_map(
                    fn (array $crop) => ImageCrop::fromArray($crop),
                    $challenge->thumbnail_crops
                )
                : null),
            'organisation' => Lazy::whenLoaded('organisation', $challenge, fn () => OrganisationResource::fromModel($challenge->organisation)),
            'showcases_count' => Lazy::create(fn () => $challenge->showcases_count),
            'total_upvotes_count' => Lazy::create(fn () => $challenge->total_upvotes_count),
        ]);
    }
}
