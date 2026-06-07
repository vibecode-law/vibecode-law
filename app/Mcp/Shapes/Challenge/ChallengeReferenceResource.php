<?php

namespace App\Mcp\Shapes\Challenge;

use App\Models\Challenge\Challenge;
use App\Models\Challenge\ChallengeShowcase;
use App\Models\Challenge\SubChallenge;
use Spatie\LaravelData\Resource;

/**
 * A minimal challenge reference (id and title) for embedding in other
 * resources, such as the challenges a showcase is attached to. When the
 * challenge is loaded through the showcase pivot, sub_challenge holds the
 * sub-challenge the showcase is entered under (or null when none applies).
 */
class ChallengeReferenceResource extends Resource
{
    public int $id;

    public string $title;

    public ?SubChallengeReferenceResource $sub_challenge;

    public static function fromModel(Challenge $challenge): self
    {
        return self::from([
            'id' => $challenge->id,
            'title' => $challenge->title,
            'sub_challenge' => self::resolveSubChallenge($challenge),
        ]);
    }

    private static function resolveSubChallenge(Challenge $challenge): ?SubChallengeReferenceResource
    {
        $pivot = $challenge->pivot;

        if (! $pivot instanceof ChallengeShowcase || $pivot->sub_challenge_id === null) {
            return null;
        }

        $subChallenge = $challenge->subChallenges->firstWhere('id', $pivot->sub_challenge_id);

        if (! $subChallenge instanceof SubChallenge) {
            return null;
        }

        return SubChallengeReferenceResource::from($subChallenge);
    }
}
