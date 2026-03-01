<?php

namespace App\Models\Challenge;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperChallengeInviteCodeUser
 */
class ChallengeInviteCodeUser extends Pivot
{
    public $incrementing = true;

    protected $table = 'challenge_invite_code_user';

    public function challengeInviteCode(): BelongsTo
    {
        return $this->belongsTo(ChallengeInviteCode::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
