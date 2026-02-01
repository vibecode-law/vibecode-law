<?php

namespace App\Services\MarketingEmail\Recipients\ValueObjects;

readonly class UpdateRecipientData
{
    /**
     * @param  array<string, mixed>|null  $extraAttributes
     * @param  array<int, string>|null  $tags
     */
    public function __construct(
        public ?string $email = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?array $extraAttributes = null,
        public ?array $tags = null,
    ) {}
}
