<?php

namespace App\Models\Challenge;

use Database\Factories\Challenge\ChallengePartnerLogoFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin IdeHelperChallengePartnerLogo
 */
class ChallengePartnerLogo extends Model
{
    /** @use HasFactory<ChallengePartnerLogoFactory> */
    use HasFactory;

    protected $fillable = [
        'challenge_id',
        'path',
        'filename',
        'href',
        'order',
        'invert_in_dark',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'invert_in_dark' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::deleted(function (ChallengePartnerLogo $logo) {
            Storage::disk('public')->delete($logo->path);
        });
    }

    //
    // Relationships
    //

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(Challenge::class);
    }

    /**
     * @param  Builder<ChallengePartnerLogo>  $query
     */
    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('order');
    }

    //
    // Attributes
    //

    protected function url(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $imageTransformBase = Config::get('services.image-transform.base_url');

                return $imageTransformBase !== null
                    ? $imageTransformBase.'/'.$this->path
                    : Storage::disk('public')->url($this->path);
            }
        );
    }
}
