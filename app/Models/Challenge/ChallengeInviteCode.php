<?php

namespace App\Models\Challenge;

use App\Enums\InviteCodeScope;
use App\Models\User;
use Database\Factories\Challenge\ChallengeInviteCodeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @mixin IdeHelperChallengeInviteCode
 */
class ChallengeInviteCode extends Model
{
    /** @use HasFactory<ChallengeInviteCodeFactory> */
    use HasFactory;

    protected $fillable = [
        'challenge_id',
        'code',
        'label',
        'scope',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'scope' => InviteCodeScope::class,
            'is_active' => 'boolean',
        ];
    }

    //
    // Relationships
    //

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(Challenge::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->using(ChallengeInviteCodeUser::class)
            ->withTimestamps();
    }
}
