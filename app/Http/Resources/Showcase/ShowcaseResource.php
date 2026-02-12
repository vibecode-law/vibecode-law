<?php

namespace App\Http\Resources\Showcase;

use App\Http\Resources\PracticeAreaResource;
use App\Http\Resources\User\UserResource;
use App\Models\Showcase\Showcase;
use App\Services\Markdown\MarkdownService;
use App\ValueObjects\FrontendEnum;
use App\ValueObjects\ImageCrop;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Auth;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Resource;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class ShowcaseResource extends Resource
{
    public int $id;

    public string $slug;

    public string $title;

    public string $tagline;

    public Lazy|string $description;

    public Lazy|string $description_html;

    public Lazy|string|null $key_features;

    public Lazy|string|null $key_features_html;

    public Lazy|string|null $help_needed;

    public Lazy|string|null $help_needed_html;

    public ?string $url;

    public ?string $video_url;

    public FrontendEnum $source_status;

    public ?string $source_url;

    public FrontendEnum $status;

    public Lazy|int|null $view_count;

    #[WithCast(DateTimeInterfaceCast::class)]
    public CarbonInterface $created_at;

    #[WithCast(DateTimeInterfaceCast::class)]
    public CarbonInterface $updated_at;

    public Lazy|UserResource|null $user;

    public Lazy|PracticeAreaResource $practiceAreas;

    public ?string $thumbnail_url;

    public ?string $thumbnail_rect_string;

    public Lazy|ImageCrop|null $thumbnail_crop;

    public Lazy|ShowcaseImageResource $images;

    public Lazy|int $images_count;

    public Lazy|int $upvotes_count;

    public Lazy|bool $has_upvoted;

    #[WithCast(DateTimeInterfaceCast::class)]
    public ?CarbonInterface $submitted_date;

    public Lazy|string|null $rejection_reason;

    #[WithCast(DateTimeInterfaceCast::class)]
    public Lazy|CarbonInterface|null $approved_at;

    public Lazy|bool $is_featured;

    public Lazy|UserResource|null $approvedBy;

    public Lazy|bool $show_approval_celebration;

    public Lazy|string|null $linkedin_share_url;

    public Lazy|bool $has_draft;

    public Lazy|int|null $draft_id;

    public Lazy|FrontendEnum|null $draft_status;

    public Lazy|string|null $youtube_id;

    public static function fromModel(Showcase $showcase): self
    {
        $currentUser = Auth::user();

        $isOwner = $currentUser?->id === $showcase->user_id;
        $isAdmin = $currentUser?->is_admin === true;
        $canViewOwnerFields = $isOwner || $isAdmin;

        $markdown = app(abstract: MarkdownService::class);

        return self::from([
            'id' => $showcase->id,
            'slug' => $showcase->slug,
            'title' => $showcase->title,
            'tagline' => $showcase->tagline,
            'description' => Lazy::create(fn () => $showcase->description)->defaultIncluded(),
            'description_html' => Lazy::create(fn () => $markdown->render(
                markdown: $showcase->description,
                cacheKey: "showcase|{$showcase->id}|description",
            )),
            'key_features' => Lazy::create(fn () => $showcase->key_features)->defaultIncluded(),
            'key_features_html' => Lazy::create(fn () => $showcase->key_features !== null ? $markdown->render(
                markdown: $showcase->key_features,
                cacheKey: "showcase|{$showcase->id}|key_features",
            ) : null),
            'help_needed' => Lazy::create(fn () => $showcase->help_needed)->defaultIncluded(),
            'help_needed_html' => Lazy::create(fn () => $showcase->help_needed !== null ? $markdown->render(
                markdown: $showcase->help_needed,
                cacheKey: "showcase|{$showcase->id}|help_needed",
            ) : null),
            'url' => $showcase->url,
            'video_url' => $showcase->video_url,
            'source_status' => $showcase->source_status->forFrontend(),
            'source_url' => $showcase->source_url,
            'status' => $showcase->status->forFrontend(),
            'view_count' => Lazy::create(fn () => $showcase->view_count)->defaultIncluded(),
            'created_at' => $showcase->created_at,
            'updated_at' => $showcase->updated_at,
            'user' => Lazy::whenLoaded('user', $showcase, fn () => UserResource::from($showcase->user)),
            'practiceAreas' => Lazy::whenLoaded('practiceAreas', $showcase, fn () => PracticeAreaResource::collect($showcase->practiceAreas)),
            'thumbnail_url' => $showcase->thumbnail_url,
            'thumbnail_crop' => Lazy::create(fn () => $showcase->thumbnail_crop !== null
                ? ImageCrop::fromArray($showcase->thumbnail_crop)
                : null),
            'thumbnail_rect_string' => $showcase->thumbnail_rect_string,
            'images' => Lazy::whenLoaded('images', $showcase, fn () => ShowcaseImageResource::collect($showcase->images)),
            'images_count' => Lazy::when(
                condition: fn () => $showcase->hasAttribute('images_count'),
                value: fn () => $showcase->images_count,
            ),
            'upvotes_count' => Lazy::when(
                condition: fn () => $showcase->hasAttribute('upvoters_count'),
                value: fn () => $showcase->upvoters_count,
            ),
            'has_upvoted' => Lazy::when(
                condition: fn () => $currentUser !== null && $showcase->relationLoaded('upvoters'),
                value: fn () => $showcase->upvoters->contains('id', $currentUser->id),
            ),
            'submitted_date' => $showcase->submitted_date,
            'rejection_reason' => Lazy::when(
                condition: fn () => $canViewOwnerFields,
                value: fn () => $showcase->rejection_reason,
            ),
            'approved_at' => Lazy::when(
                condition: fn () => $isAdmin,
                value: fn () => $showcase->approved_at,
            ),
            'is_featured' => Lazy::when(
                condition: fn () => $isAdmin,
                value: fn () => $showcase->is_featured,
            ),
            'approvedBy' => Lazy::when(
                condition: fn () => $isAdmin && $showcase->relationLoaded('approvedBy'),
                value: fn () => $showcase->approvedBy !== null ? UserResource::from($showcase->approvedBy) : null,
            ),
            'show_approval_celebration' => Lazy::when(
                condition: fn () => $isOwner && $showcase->isApproved() && $showcase->hasOwnerCelebratedApproval() === false,
                value: fn () => true,
            ),
            'linkedin_share_url' => Lazy::create(fn () => 'https://www.linkedin.com/sharing/share-offsite/?url='.urlencode(route(name: 'showcase.show', parameters: $showcase))),
            'has_draft' => Lazy::when(
                condition: fn () => $isOwner && $showcase->isApproved() && $showcase->relationLoaded('draft'),
                value: fn () => $showcase->draft !== null,
            ),
            'draft_id' => Lazy::when(
                condition: fn () => $isOwner && $showcase->isApproved() && $showcase->relationLoaded('draft') && $showcase->draft !== null,
                value: fn () => $showcase->draft->id,
            ),
            'draft_status' => Lazy::when(
                condition: fn () => $isOwner && $showcase->isApproved() && $showcase->relationLoaded('draft') && $showcase->draft !== null,
                value: fn () => $showcase->draft->status->forFrontend(),
            ),
            'youtube_id' => Lazy::create(fn () => $showcase->youtube_id),
        ]);
    }
}
