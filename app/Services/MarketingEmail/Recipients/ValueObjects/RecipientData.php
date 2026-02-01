<?php

namespace App\Services\MarketingEmail\Recipients\ValueObjects;

use Illuminate\Support\Carbon;

readonly class RecipientData
{
    /**
     * @param  array<string, mixed>  $extraAttributes
     * @param  array<int, string>  $tags
     */
    public function __construct(
        public string $externalId,
        public string $email,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public array $extraAttributes = [],
        public array $tags = [],
        public ?Carbon $subscribedAt = null,
        public ?Carbon $unsubscribedAt = null,
        public ?Carbon $createdAt = null,
        public ?Carbon $updatedAt = null,
    ) {}

    public function isSubscribed(): bool
    {
        return $this->subscribedAt !== null && $this->unsubscribedAt === null;
    }

    public function isUnconfirmed(): bool
    {
        return $this->subscribedAt === null && $this->unsubscribedAt === null;
    }
}
