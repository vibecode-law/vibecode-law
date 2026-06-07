<?php

namespace App\Models\Challenge;

use App\Models\Showcase\Showcase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperChallengeShowcase
 */
class ChallengeShowcase extends Pivot
{
    public $incrementing = false;

    protected $table = 'challenge_showcase';

    protected $fillable = [
        'sub_challenge_id',
    ];

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(Challenge::class);
    }

    public function showcase(): BelongsTo
    {
        return $this->belongsTo(Showcase::class);
    }

    public function subChallenge(): BelongsTo
    {
        return $this->belongsTo(SubChallenge::class);
    }
}
