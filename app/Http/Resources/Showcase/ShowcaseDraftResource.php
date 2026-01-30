<?php

namespace App\Http\Resources\Showcase;

use App\Http\Resources\PracticeAreaResource;
use App\Http\Resources\User\UserResource;
use App\Models\Showcase\Showcase;
use App\Models\Showcase\ShowcaseDraft;
use App\ValueObjects\FrontendEnum;
use App\ValueObjects\ImageCrop;
use Carbon\CarbonInterface;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Resource;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class ShowcaseDraftResource extends Resource
{
    public int $id;

    public int $showcase_id;

    public string $showcase_slug;

    public string $showcase_title;

    public string $title;

    public string $tagline;

    public Lazy|string $description;

    public Lazy|string|null $key_features;

    public Lazy|string|null $help_needed;

    public ?string $url;

    public ?string $video_url;

    public FrontendEnum $source_status;

    public ?string $source_url;

    public FrontendEnum $status;

    #[WithCast(DateTimeInterfaceCast::class)]
    public CarbonInterface $created_at;

    #[WithCast(DateTimeInterfaceCast::class)]
    public CarbonInterface $updated_at;

    public Lazy|PracticeAreaResource $practiceAreas;

    public ?string $thumbnail_url;

    public ?string $thumbnail_rect_string;

    public Lazy|ImageCrop|null $thumbnail_crop;

    public Lazy|ShowcaseDraftImageResource $images;

    #[WithCast(DateTimeInterfaceCast::class)]
    public ?CarbonInterface $submitted_at;

    public Lazy|string|null $rejection_reason;

    public Lazy|UserResource $user;

    public static function fromModel(ShowcaseDraft $draft): self
    {
        /** @var Showcase $showcase */
        $showcase = $draft->showcase;

        return self::from([
            'id' => $draft->id,
            'showcase_id' => $draft->showcase_id,
            'showcase_slug' => $showcase->slug,
            'showcase_title' => $showcase->title,
            'title' => $draft->title,
            'tagline' => $draft->tagline,
            'description' => Lazy::create(fn () => $draft->description)->defaultIncluded(),
            'key_features' => Lazy::create(fn () => $draft->key_features)->defaultIncluded(),
            'help_needed' => Lazy::create(fn () => $draft->help_needed)->defaultIncluded(),
            'url' => $draft->url,
            'video_url' => $draft->video_url,
            'source_status' => $draft->source_status->forFrontend(),
            'source_url' => $draft->source_url,
            'status' => $draft->status->forFrontend(),
            'created_at' => $draft->created_at,
            'updated_at' => $draft->updated_at,
            'practiceAreas' => Lazy::whenLoaded('practiceAreas', $draft, fn () => PracticeAreaResource::collect($draft->practiceAreas)),
            'thumbnail_url' => $draft->thumbnail_url,
            'thumbnail_crop' => Lazy::create(fn () => $draft->thumbnail_crop !== null
                ? ImageCrop::fromArray($draft->thumbnail_crop)
                : null),
            'thumbnail_rect_string' => $draft->thumbnail_rect_string,
            'images' => Lazy::whenLoaded('images', $draft, fn () => ShowcaseDraftImageResource::collect($draft->images)),
            'submitted_at' => $draft->submitted_at,
            'rejection_reason' => Lazy::create(fn () => $draft->rejection_reason)->defaultIncluded(),
            'user' => Lazy::whenLoaded('user', $showcase, fn () => UserResource::from($showcase->user)),
        ]);
    }
}
