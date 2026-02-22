<?php

namespace App\Models\Challenge;

use App\Concerns\ClearsMarkdownCache;
use App\Models\Organisation\Organisation;
use App\Models\Showcase\Showcase;
use App\Models\Showcase\ShowcaseUpvote;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

/**
 * @property array<string, array{x: int, y: int, width: int, height: int}>|null $thumbnail_crops
 * @property-read int|null $total_upvotes_count
 *
 * @mixin IdeHelperChallenge
 */
class Challenge extends Model
{
    /** @use HasFactory<\Database\Factories\Challenge\ChallengeFactory> */
    use ClearsMarkdownCache, HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'tagline',
        'description',
        'starts_at',
        'ends_at',
        'is_active',
        'is_featured',
        'organisation_id',
        'thumbnail_extension',
        'thumbnail_crops',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'thumbnail_crops' => 'array',
        ];
    }

    /**
     * @return array<int, string>
     */
    public function getCachedFields(): array
    {
        return ['description'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    //
    // Relationships
    //

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function showcases(): BelongsToMany
    {
        return $this->belongsToMany(related: Showcase::class)
            ->using(ChallengeShowcase::class)
            ->withTimestamps();
    }

    /**
     * @param  Builder<Challenge>  $query
     */
    #[Scope]
    protected function withTotalUpvotesCount(Builder $query): void
    {
        $query->addSelect([
            'total_upvotes_count' => ShowcaseUpvote::query()
                ->selectRaw('count(*)')
                ->join('challenge_showcase', 'showcase_upvotes.showcase_id', '=', 'challenge_showcase.showcase_id')
                ->whereColumn('challenge_showcase.challenge_id', 'challenges.id'),
        ]);
    }

    //
    // Attributes
    //

    protected function thumbnailUrl(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                if ($this->thumbnail_extension === null) {
                    return null;
                }

                $imageTransformBase = Config::get('services.image-transform.base_url');

                $path = "challenge/{$this->id}/thumbnail.{$this->thumbnail_extension}";

                if ($imageTransformBase === null) {
                    return Storage::disk('public')->url($path);
                }

                return $imageTransformBase.'/'.$path;
            }
        );
    }

    /**
     * @return Attribute<array<string, string>|null, never>
     */
    protected function thumbnailRectStrings(): Attribute
    {
        return Attribute::make(
            get: function (): ?array {
                if ($this->thumbnail_crops === null) {
                    return null;
                }

                /** @var array<string, array{x: int, y: int, width: int, height: int}> $crops */
                $crops = $this->thumbnail_crops;

                $result = [];

                foreach ($crops as $key => $crop) {
                    $result[$key] = sprintf(
                        'rect=%d,%d,%d,%d',
                        $crop['x'],
                        $crop['y'],
                        $crop['width'],
                        $crop['height']
                    );
                }

                return $result;
            }
        );
    }
}
