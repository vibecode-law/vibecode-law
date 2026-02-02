<?php

namespace App\Services\MarketingEmail\Recipients;

use App\Services\MarketingEmail\Recipients\Contracts\RecipientService;
use App\Services\MarketingEmail\Recipients\ValueObjects\CreateRecipientData;
use App\Services\MarketingEmail\Recipients\ValueObjects\RecipientData;
use App\Services\MarketingEmail\Recipients\ValueObjects\UpdateRecipientData;
use Illuminate\Support\Str;

/**
 * Null implementation of RecipientService for testing and environments without Mailcoach.
 */
class NullRecipientService implements RecipientService
{
    public function createRecipient(CreateRecipientData $data, bool $skipConfirmation = false): string
    {
        return Str::uuid()->toString();
    }

    public function updateRecipient(string $externalId, UpdateRecipientData $data): void {}

    public function deleteRecipient(string $externalId): void {}

    public function getRecipient(string $externalId): RecipientData
    {
        return new RecipientData(
            externalId: $externalId,
            email: '',
            firstName: null,
            lastName: null,
            extraAttributes: [],
            tags: [],
            subscribedAt: null,
            unsubscribedAt: null,
            createdAt: null,
            updatedAt: null,
        );
    }

    public function findRecipientByEmail(string $email, string $listId): ?RecipientData
    {
        return null;
    }

    public function confirmRecipient(string $externalId): void {}

    public function unsubscribeRecipient(string $externalId): void {}

    public function resubscribeRecipient(string $externalId): void {}

    public function addTags(string $externalId, array $tags): void {}

    public function removeTags(string $externalId, array $tags): void {}
}
