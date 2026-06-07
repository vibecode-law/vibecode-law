<?php

namespace App\Mcp\Shapes\Challenge;

use App\Models\Challenge\SubChallenge;
use Spatie\LaravelData\Resource;

class SubChallengeResource extends Resource
{
    public int $id;

    public string $name;

    public string $tagline;

    public ?string $description;

    public int $order;

    public static function fromModel(SubChallenge $subChallenge): self
    {
        return self::from([
            'id' => $subChallenge->id,
            'name' => $subChallenge->name,
            'tagline' => $subChallenge->tagline,
            'description' => $subChallenge->description,
            'order' => (int) $subChallenge->order,
        ]);
    }
}
