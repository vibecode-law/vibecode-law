<?php

namespace App\ValueObjects;

use App\Models\Challenge\Challenge;

class ResolvedSubmissionChallenge
{
    public function __construct(
        public readonly ?Challenge $challenge = null,
        public readonly ?string $warning = null,
    ) {}

    public static function none(): self
    {
        return new self;
    }

    public static function warning(string $warning): self
    {
        return new self(warning: $warning);
    }

    public static function selected(Challenge $challenge): self
    {
        return new self(challenge: $challenge);
    }
}
