<?php

namespace App\Actions\Showcase;

use App\Enums\InviteCodeScope;
use App\Models\Challenge\Challenge;
use App\Models\User;
use App\ValueObjects\ResolvedSubmissionChallenge;
use Illuminate\Database\Eloquent\Collection;

class ResolveSubmissionChallengeAction
{
    /**
     * Resolve the challenge a showcase submission should be pre-filled with,
     * based on the query string. Returns a warning instead when the requested
     * challenge is closed or off-limits.
     *
     * @param  Collection<int, Challenge>  $availableChallenges
     */
    public function resolve(?string $challengeSlug, User $user, Collection $availableChallenges): ResolvedSubmissionChallenge
    {
        if ($challengeSlug === null) {
            return ResolvedSubmissionChallenge::none();
        }

        $challenge = Challenge::query()
            ->where('slug', $challengeSlug)
            ->where('is_active', true)
            ->first();

        if ($challenge === null) {
            return ResolvedSubmissionChallenge::none();
        }

        if ($challenge->hasStarted() === false) {
            return ResolvedSubmissionChallenge::warning(
                "The {$challenge->title} challenge is not open for submissions yet.",
            );
        }

        if ($challenge->requiresInviteToSubmit() === true
            && $user->hasChallengeAccess($challenge, InviteCodeScope::ViewAndSubmit) === false) {
            return ResolvedSubmissionChallenge::warning(
                "You don't have permission to submit to the {$challenge->title} challenge. An invite code with submit access is required.",
            );
        }

        /** @var Challenge|null $availableChallenge */
        $availableChallenge = $availableChallenges->firstWhere('id', $challenge->id);

        if ($availableChallenge === null) {
            return ResolvedSubmissionChallenge::none();
        }

        return ResolvedSubmissionChallenge::selected(challenge: $availableChallenge);
    }
}
