<?php

namespace App\Models\Challenge;

use Database\Factories\Challenge\SubChallengeFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperSubChallenge
 */
class SubChallenge extends Model
{
    /** @use HasFactory<SubChallengeFactory> */
    use HasFactory;

    protected $fillable = [
        'challenge_id',
        'name',
        'tagline',
        'description',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(Challenge::class);
    }

    /**
     * @param  Builder<SubChallenge>  $query
     */
    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('order');
    }
}
