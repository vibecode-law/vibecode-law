<?php

namespace App\Services\MarketingEmail\Recipients\Contracts;

use App\Services\MarketingEmail\Recipients\ValueObjects\CreateRecipientData;
use App\Services\MarketingEmail\Recipients\ValueObjects\RecipientData;
use App\Services\MarketingEmail\Recipients\ValueObjects\UpdateRecipientData;

interface RecipientService
{
    /**
     * @return string The external recipient ID
     */
    public function createRecipient(CreateRecipientData $data): string;

    public function updateRecipient(string $externalId, UpdateRecipientData $data): void;

    public function deleteRecipient(string $externalId): void;

    public function getRecipient(string $externalId): RecipientData;

    public function findRecipientByEmail(string $email, string $listId): ?RecipientData;

    public function confirmRecipient(string $externalId): void;

    public function unsubscribeRecipient(string $externalId): void;

    public function resubscribeRecipient(string $externalId): void;
}
