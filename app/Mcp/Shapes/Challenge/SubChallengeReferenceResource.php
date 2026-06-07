<?php

namespace App\Mcp\Shapes\Challenge;

use App\Models\Challenge\SubChallenge;
use Spatie\LaravelData\Resource;

/**
 * A minimal sub-challenge reference (id and name) for embedding in other
 * resources, such as the sub-challenge a showcase is entered under for a
 * given challenge.
 */
class SubChallengeReferenceResource extends Resource
{
    public int $id;

    public string $name;

    public static function fromModel(SubChallenge $subChallenge): self
    {
        return self::from([
            'id' => $subChallenge->id,
            'name' => $subChallenge->name,
        ]);
    }
}
