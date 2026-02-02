<?php

namespace App\Services\MarketingEmail\Recipients;

use App\Services\MarketingEmail\Recipients\Contracts\RecipientService;
use App\Services\MarketingEmail\Recipients\ValueObjects\CreateRecipientData;
use App\Services\MarketingEmail\Recipients\ValueObjects\RecipientData;
use App\Services\MarketingEmail\Recipients\ValueObjects\UpdateRecipientData;
use Illuminate\Support\Carbon;
use Spatie\MailcoachSdk\Facades\Mailcoach;
use Spatie\MailcoachSdk\Resources\Subscriber;

class MailcoachRecipientService implements RecipientService
{
    public function createRecipient(CreateRecipientData $data, bool $skipConfirmation = false): string
    {
        $subscriber = Mailcoach::createSubscriber(
            emailListUuid: $data->listId,
            attributes: array_filter(
                [
                    'email' => $data->email,
                    'first_name' => $data->firstName,
                    'last_name' => $data->lastName,
                    'extra_attributes' => $data->extraAttributes ?: null,
                    'tags' => $data->tags ?: null,
                    'skip_confirmation' => $skipConfirmation ?: null,
                ],
                fn ($value) => $value !== null,
            ),
        );

        return $subscriber->uuid;
    }

    public function updateRecipient(string $externalId, UpdateRecipientData $data): void
    {
        $payload = array_filter(
            [
                'email' => $data->email,
                'first_name' => $data->firstName,
                'last_name' => $data->lastName,
                'extra_attributes' => $data->extraAttributes,
                'tags' => $data->tags,
            ],
            fn ($value) => $value !== null,
        );

        Mailcoach::updateSubscriber(subscriberUuid: $externalId, attributes: $payload);
    }

    public function deleteRecipient(string $externalId): void
    {
        Mailcoach::deleteSubscriber(subscriberUuid: $externalId);
    }

    public function getRecipient(string $externalId): RecipientData
    {
        $subscriber = Mailcoach::subscriber(uuid: $externalId);

        return $this->mapToRecipientData(subscriber: $subscriber);
    }

    public function findRecipientByEmail(string $email, string $listId): ?RecipientData
    {
        $subscriber = Mailcoach::findByEmail(emailListUuid: $listId, email: $email);

        if ($subscriber === null) {
            return null;
        }

        return $this->mapToRecipientData(subscriber: $subscriber);
    }

    public function confirmRecipient(string $externalId): void
    {
        Mailcoach::confirmSubscriber(subscriberUuid: $externalId);
    }

    public function unsubscribeRecipient(string $externalId): void
    {
        Mailcoach::unsubscribeSubscriber(subscriberUuid: $externalId);
    }

    public function resubscribeRecipient(string $externalId): void
    {
        Mailcoach::resubscribeSubscriber(subscriberUuid: $externalId);
    }

    /**
     * @param  array<int, string>  $tags
     */
    public function addTags(string $externalId, array $tags): void
    {
        $subscriber = Mailcoach::subscriber(uuid: $externalId);
        $subscriber->addTags($tags);
    }

    /**
     * @param  array<int, string>  $tags
     */
    public function removeTags(string $externalId, array $tags): void
    {
        $subscriber = Mailcoach::subscriber(uuid: $externalId);
        $subscriber->removeTags($tags);
    }

    private function mapToRecipientData(Subscriber $subscriber): RecipientData
    {
        return new RecipientData(
            externalId: $subscriber->uuid,
            email: $subscriber->email,
            firstName: $subscriber->firstName,
            lastName: $subscriber->lastName,
            extraAttributes: $subscriber->extraAttributes,
            tags: $subscriber->tags,
            subscribedAt: $subscriber->subscribedAt !== null
                ? Carbon::parse($subscriber->subscribedAt)
                : null,
            unsubscribedAt: $subscriber->unsubscribedAt !== null
                ? Carbon::parse($subscriber->unsubscribedAt)
                : null,
            createdAt: $subscriber->createdAt !== null
                ? Carbon::parse($subscriber->createdAt)
                : null,
            updatedAt: $subscriber->updatedAt !== null
                ? Carbon::parse($subscriber->updatedAt)
                : null,
        );
    }
}
