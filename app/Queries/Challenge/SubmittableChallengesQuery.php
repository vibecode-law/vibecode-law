<?php

namespace App\Queries\Challenge;

use App\Enums\ChallengeVisibility;
use App\Enums\InviteCodeScope;
use App\Models\Challenge\Challenge;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class SubmittableChallengesQuery
{
    /**
     * Open challenges the user may submit to: public challenges, or those the
     * user holds a submit-scoped invite code for. Sub-challenges are eager
     * loaded for the submission selects.
     *
     * @return Collection<int, Challenge>
     */
    public function __invoke(User $user): Collection
    {
        $invitedChallengeIds = $user->acceptedChallengeInviteCodes()
            ->whereIn('scope', InviteCodeScope::ViewAndSubmit->satisfiedBy())
            ->pluck('challenge_id');

        return Challenge::query()
            ->where('is_active', true)
            ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->where(fn ($query) => $query
                ->where('visibility', ChallengeVisibility::Public)
                ->orWhereIn('id', $invitedChallengeIds))
            ->with('subChallenges')
            ->orderBy('title')
            ->get();
    }
}
