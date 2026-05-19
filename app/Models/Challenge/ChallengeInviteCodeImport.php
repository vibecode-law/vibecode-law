<?php

namespace App\Models\Challenge;

use App\Enums\ChallengeInviteCodeImportStatus;
use App\Models\User;
use Database\Factories\Challenge\ChallengeInviteCodeImportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperChallengeInviteCodeImport
 */
class ChallengeInviteCodeImport extends Model
{
    /** @use HasFactory<ChallengeInviteCodeImportFactory> */
    use HasFactory;

    protected $fillable = [
        'challenge_invite_code_id',
        'user_id',
        'status',
        'custom_message',
        'total_rows',
        'imported_count',
        'skipped_count',
        'skipped_rows',
    ];

    protected function casts(): array
    {
        return [
            'status' => ChallengeInviteCodeImportStatus::class,
            'skipped_rows' => 'array',
            'total_rows' => 'integer',
            'imported_count' => 'integer',
            'skipped_count' => 'integer',
        ];
    }

    //
    // Relationships
    //

    public function inviteCode(): BelongsTo
    {
        return $this->belongsTo(ChallengeInviteCode::class, 'challenge_invite_code_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
