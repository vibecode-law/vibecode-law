<?php

namespace App\Services\Auth\Linkedin;

use App\Models\User;

class FindOrCreateLinkedinUserResult
{
    public function __construct(
        public ?User $user = null,
        public bool $wasRecentlyCreated = false,
        public ?string $errorMessage = null,
    ) {}

    public static function error(string $message): self
    {
        return new self(errorMessage: $message);
    }

    public static function success(User $user, bool $wasRecentlyCreated): self
    {
        return new self(
            user: $user,
            wasRecentlyCreated: $wasRecentlyCreated,
        );
    }

    public function failed(): bool
    {
        return $this->errorMessage !== null;
    }
}
