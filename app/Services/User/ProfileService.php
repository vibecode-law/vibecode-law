<?php

namespace App\Services\User;

use App\Jobs\MarketingEmail\CreateExternalSubscriberJob;
use App\Jobs\MarketingEmail\ResubscribeToMarketingJob;
use App\Jobs\MarketingEmail\UnsubscribeFromMarketingJob;
use App\Jobs\MarketingEmail\UpdateExternalSubscriberJob;
use App\Models\User;

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

    protected function onEmailSet(User $user): void
    {
        if ($user->isSubscribedToMarketing() === false) {
            return;
        }

        CreateExternalSubscriberJob::dispatch(user: $user);
    }

    protected function onEmailChanged(User $user): void
    {
        UpdateExternalSubscriberJob::dispatch(user: $user);
    }

    protected function onMarketingPreferenceChanged(User $user, bool $wasSubscribed): void
    {
        if ($wasSubscribed === true) {
            UnsubscribeFromMarketingJob::dispatch(user: $user);

            return;
        }

        $this->subscribeToMarketing(user: $user);
    }

    private function subscribeToMarketing(User $user): void
    {
        if ($user->external_subscriber_uuid !== null) {
            ResubscribeToMarketingJob::dispatch(user: $user);

            return;
        }

        CreateExternalSubscriberJob::dispatch(user: $user);
    }
}
