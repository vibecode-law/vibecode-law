<?php

namespace App\Models\Showcase;

use App\Enums\MarkdownProfile;
use App\Enums\ShowcaseStatus;
use App\Enums\SourceStatus;
use App\Models\PracticeArea;
use App\Models\User;
use App\Services\Markdown\MarkdownService;
use App\Services\YoutubeIdExtractionService;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

/**
 * @property array{x: int, y: int, width: int, height: int}|null $thumbnail_crop
 *
 * @mixin IdeHelperShowcase
 */
class Showcase extends Model
{
    /** @use HasFactory<\Database\Factories\Showcase\ShowcaseFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'tagline',
        'description',
        'key_features',
        'help_needed',
        'url',
        'video_url',
        'source_status',
        'source_url',
        'thumbnail_extension',
        'thumbnail_crop',
        'status',
        'launch_date',
        'submitted_date',
        'approved_at',
        'approval_celebrated_at',
        'approved_by',
        'rejection_reason',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'status' => ShowcaseStatus::class,
            'submitted_date' => 'datetime',
            'approved_at' => 'datetime',
            'approval_celebrated_at' => 'datetime',
            'launch_date' => 'date',
            'view_count' => 'integer',
            'is_featured' => 'boolean',
            'source_status' => SourceStatus::class,
            'thumbnail_crop' => 'array',
        ];
    }

    protected static function booted(): void
    {
        $clearMarkdownCache = function (Showcase $showcase): void {
            $markdownService = app(MarkdownService::class);

            foreach ($showcase->getMarkdownCacheKeys() as $cacheKey) {
                $markdownService->clearCacheByKey(
                    cacheKey: $cacheKey,
                    profile: MarkdownProfile::Basic
                );
            }
        };

        static::updated($clearMarkdownCache);
        static::deleted($clearMarkdownCache);
    }

    /**
     * @return array<int, string>
     */
    public function getMarkdownCacheKeys(): array
    {
        return [
            "showcase|{$this->id}|description",
            "showcase|{$this->id}|help_needed",
            "showcase|{$this->id}|key_features",
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    //
    // Relationships
    //

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function practiceAreas(): BelongsToMany
    {
        return $this->belongsToMany(related: PracticeArea::class)->withTimestamps();
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ShowcaseImage::class)->orderBy('order');
    }

    public function upvoters(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'showcase_upvotes')->withTimestamps();
    }

    public function draft(): HasOne
    {
        return $this->hasOne(ShowcaseDraft::class);
    }

    //
    // Scopes
    //

    #[Scope]
    protected function approved(Builder $query): void
    {
        $query->where('status', ShowcaseStatus::Approved);
    }

    #[Scope]
    protected function pending(Builder $query): void
    {
        $query->where('status', ShowcaseStatus::Pending);
    }

    #[Scope]
    protected function featured(Builder $query): void
    {
        $query->where('is_featured', true);
    }

    #[Scope]
    protected function publiclyVisible(Builder $query): void
    {
        $query->where('status', ShowcaseStatus::Approved)->whereNotNull('submitted_date');
    }

    //
    // Helpers
    //

    public function isApproved(): bool
    {
        return $this->status === ShowcaseStatus::Approved;
    }

    public function isPending(): bool
    {
        return $this->status === ShowcaseStatus::Pending;
    }

    public function isDraft(): bool
    {
        return $this->status === ShowcaseStatus::Draft;
    }

    public function isRejected(): bool
    {
        return $this->status === ShowcaseStatus::Rejected;
    }

    public function hasOwnerCelebratedApproval(): bool
    {
        return $this->approval_celebrated_at !== null;
    }

    public function hasPendingDraft(): bool
    {
        /** @var ShowcaseDraft|null $draft */
        $draft = $this->draft;

        return $draft !== null && $draft->isPending();
    }

    public function hasDraft(): bool
    {
        return $this->draft !== null;
    }

    protected function thumbnailUrl(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                if ($this->thumbnail_extension === null) {
                    return null;
                }

                $imageTransformBase = Config::get('services.image-transform.base_url');

                $path = "showcase/{$this->id}/thumbnail.{$this->thumbnail_extension}";

                if ($imageTransformBase === null) {
                    return Storage::disk('public')->url($path);
                }

                return $imageTransformBase.'/'.$path;
            }
        );
    }

    protected function thumbnailRectString(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                if ($this->thumbnail_crop === null) {
                    return null;
                }

                /** @var array{x: int, y: int, width: int, height: int} $crop */
                $crop = $this->thumbnail_crop;

                return sprintf(
                    'rect=%d,%d,%d,%d',
                    $crop['x'],
                    $crop['y'],
                    $crop['width'],
                    $crop['height']
                );
            }
        );
    }

    protected function youtubeId(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                if ($this->video_url === null) {
                    return null;
                }

                return YoutubeIdExtractionService::from(url: $this->video_url)->get();
            }
        );
    }
}
