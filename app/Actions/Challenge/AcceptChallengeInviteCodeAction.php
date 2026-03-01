<?php

namespace App\Actions\Challenge;

use App\Models\Challenge\Challenge;
use App\Models\Challenge\ChallengeInviteCode;
use App\Models\User;

class AcceptChallengeInviteCodeAction
{
    public function accept(ChallengeInviteCode $inviteCode, User $user): Challenge
    {
        if ($this->alreadyHasSufficientAccess(inviteCode: $inviteCode, user: $user) === false) {
            $this->replaceLowerScopedInvites(inviteCode: $inviteCode, user: $user);
            $inviteCode->users()->syncWithoutDetaching($user->id);
        }

        return $inviteCode->challenge;
    }

    private function alreadyHasSufficientAccess(ChallengeInviteCode $inviteCode, User $user): bool
    {
        return $user->acceptedChallengeInviteCodes()
            ->where('challenge_id', $inviteCode->challenge_id)
            ->whereIn('scope', $inviteCode->scope->satisfiedBy())
            ->exists();
    }

    private function replaceLowerScopedInvites(ChallengeInviteCode $inviteCode, User $user): void
    {
        $lowerScopedIds = $user->acceptedChallengeInviteCodes()
            ->where('challenge_id', $inviteCode->challenge_id)
            ->pluck('challenge_invite_codes.id');

        if ($lowerScopedIds->isNotEmpty() === true) {
            $user->acceptedChallengeInviteCodes()->detach($lowerScopedIds);
        }
    }
}
