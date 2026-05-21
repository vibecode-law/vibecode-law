<?php

namespace App\Mcp\Shapes\Challenge;

use App\Models\Challenge\Challenge;
use Spatie\LaravelData\Resource;

/**
 * A minimal challenge reference (id and title) for embedding in other
 * resources, such as the challenges a showcase is attached to.
 */
class ChallengeReferenceResource extends Resource
{
    public int $id;

    public string $title;

    public static function fromModel(Challenge $challenge): self
    {
        return self::from([
            'id' => $challenge->id,
            'title' => $challenge->title,
        ]);
    }
}
