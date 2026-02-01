<?php

namespace App\Services\MarketingEmail\Recipients\ValueObjects;

readonly class CreateRecipientData
{
    /**
     * @param  array<string, mixed>  $extraAttributes
     * @param  array<int, string>  $tags
     */
    public function __construct(
        public string $email,
        public string $listId,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public array $extraAttributes = [],
        public array $tags = [],
    ) {}
}
