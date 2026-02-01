<?php

namespace App\Services\User;

use App\Models\User;
use App\Services\MarketingEmail\Recipients\Contracts\RecipientService;
use App\Services\MarketingEmail\Recipients\ValueObjects\CreateRecipientData;
use App\Services\MarketingEmail\Recipients\ValueObjects\UpdateRecipientData;
use Illuminate\Support\Facades\Config;

class ProfileService
{
    /**
     * Profile fields managed by this service.
     *
     * @var list<string>
     */
    private const PROFILE_FIELDS = [
        'first_name',
        'last_name',
        'handle',
        'organisation',
        'job_title',
        'linkedin_url',
        'bio',
        'email',
        'marketing_opt_out_at',
    ];

    public function __construct(
        private RecipientService $recipientService,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): User
    {
        $user = User::query()->create(
            $this->filterProfileData(data: $data)
        );

        $this->onEmailSet(user: $user);

        return $user;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, array $data): User
    {
        $emailChanged = isset($data['email']) && $data['email'] !== $user->email;
        $marketingChanged = $this->marketingPreferenceChanged(user: $user, data: $data);
        $wasSubscribed = $user->isSubscribedToMarketing();

        $user->update(
            $this->filterProfileData(data: $data)
        );

        if ($emailChanged === true) {
            $this->onEmailChanged(user: $user);
        }

        if ($marketingChanged === true) {
            $this->onMarketingPreferenceChanged(
                user: $user,
                wasSubscribed: $wasSubscribed,
            );
        }

        return $user;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function marketingPreferenceChanged(User $user, array $data): bool
    {
        if (array_key_exists('marketing_opt_out_at', $data) === false) {
            return false;
        }

        $currentlyOptedOut = $user->marketing_opt_out_at !== null;
        $newOptedOut = $data['marketing_opt_out_at'] !== null;

        return $currentlyOptedOut !== $newOptedOut;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function filterProfileData(array $data): array
    {
        return array_filter(
            $data,
            fn (string $key): bool => in_array($key, self::PROFILE_FIELDS, true),
            ARRAY_FILTER_USE_KEY
        );
    }

    private function getListId(): string
    {
        return Config::get('marketing.main_list_uuid');
    }

    protected function onEmailSet(User $user): void
    {
        if ($user->isSubscribedToMarketing() === false) {
            return;
        }

        $this->createExternalSubscriber(user: $user);
    }

    protected function onEmailChanged(User $user): void
    {
        if ($user->external_subscriber_uuid === null) {
            return;
        }

        $this->recipientService->updateRecipient(
            externalId: $user->external_subscriber_uuid,
            data: new UpdateRecipientData(
                email: $user->email,
            ),
        );
    }

    protected function onMarketingPreferenceChanged(User $user, bool $wasSubscribed): void
    {
        if ($wasSubscribed === true) {
            $this->unsubscribeFromMarketing(user: $user);

            return;
        }

        $this->subscribeToMarketing(user: $user);
    }

    private function unsubscribeFromMarketing(User $user): void
    {
        if ($user->external_subscriber_uuid === null) {
            return;
        }

        $this->recipientService->unsubscribeRecipient(
            externalId: $user->external_subscriber_uuid,
        );
    }

    private function subscribeToMarketing(User $user): void
    {
        if ($user->external_subscriber_uuid !== null) {
            $this->recipientService->resubscribeRecipient(
                externalId: $user->external_subscriber_uuid,
            );

            return;
        }

        $this->createExternalSubscriber(user: $user);
    }

    private function createExternalSubscriber(User $user): void
    {
        $subscriberUuid = $this->recipientService->createRecipient(
            data: new CreateRecipientData(
                email: $user->email,
                listId: $this->getListId(),
                firstName: $user->first_name,
                lastName: $user->last_name,
            ),
        );

        $user->external_subscriber_uuid = $subscriberUuid;
        $user->save();
    }
}
