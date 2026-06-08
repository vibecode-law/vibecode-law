<?php

namespace App\Actions\Challenge;

use App\Jobs\MarketingEmail\AddTagToSubscriberJob;
use App\Jobs\MarketingEmail\RemoveTagFromSubscriberJob;
use App\Models\Challenge\Challenge;
use App\Models\Challenge\ChallengeInviteCode;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class AcceptChallengeInviteCodeAction
{
    public function accept(ChallengeInviteCode $inviteCode, User $user): Challenge
    {
        if ($this->alreadyHasSufficientAccess(inviteCode: $inviteCode, user: $user) === false) {
            $replaced = $this->replaceLowerScopedInvites(inviteCode: $inviteCode, user: $user);
            $inviteCode->users()->syncWithoutDetaching($user->id);

            $this->syncMarketingTags(user: $user, accepted: $inviteCode, replaced: $replaced);
        }

        return $inviteCode->challenge;
    }

    public function alreadyHasSufficientAccess(ChallengeInviteCode $inviteCode, User $user): bool
    {
        return $user->acceptedChallengeInviteCodes()
            ->where('challenge_id', $inviteCode->challenge_id)
            ->whereIn('scope', $inviteCode->scope->satisfiedBy())
            ->exists();
    }

    /**
     * @return Collection<int, ChallengeInviteCode>
     */
    private function replaceLowerScopedInvites(ChallengeInviteCode $inviteCode, User $user): Collection
    {
        /** @var Collection<int, ChallengeInviteCode> $lowerScoped */
        $lowerScoped = $user->acceptedChallengeInviteCodes()
            ->where('challenge_id', $inviteCode->challenge_id)
            ->with('challenge')
            ->get();

        if ($lowerScoped->isNotEmpty() === true) {
            $user->acceptedChallengeInviteCodes()->detach($lowerScoped->pluck('id'));
        }

        return $lowerScoped;
    }

    /**
     * @param  Collection<int, ChallengeInviteCode>  $replaced
     */
    private function syncMarketingTags(User $user, ChallengeInviteCode $accepted, Collection $replaced): void
    {
        if ($user->external_subscriber_uuid === null) {
            return;
        }

        foreach ($replaced as $replacedInviteCode) {
            RemoveTagFromSubscriberJob::dispatch(
                externalSubscriberUuid: $user->external_subscriber_uuid,
                tag: $this->tagFor($replacedInviteCode),
            );
        }

        AddTagToSubscriberJob::dispatch(
            externalSubscriberUuid: $user->external_subscriber_uuid,
            tag: $this->tagFor($accepted),
        );
    }

    public function tagFor(ChallengeInviteCode $inviteCode): string
    {
        return "challengeInvite:{$inviteCode->challenge->slug}:".Str::slug($inviteCode->label).":{$inviteCode->code}";
    }
}
