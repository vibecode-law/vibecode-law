<?php

namespace App\Services\User;

use App\Jobs\MarketingEmail\CreateExternalSubscriberJob;
use App\Jobs\MarketingEmail\ResubscribeToMarketingJob;
use App\Jobs\MarketingEmail\UnsubscribeFromMarketingJob;
use App\Jobs\MarketingEmail\UpdateExternalSubscriberJob;
use App\Models\User;
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

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, bool $emailVerified = false): User
    {
        $user = new User;

        $user->fill($this->filterProfileData(data: $data));

        if ($emailVerified === true) {
            $user->email_verified_at = now();
        }

        $user->save();

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
        if ($user->hasVerifiedEmail() === false) {
            return;
        }

        if ($user->isSubscribedToMarketing() === false) {
            return;
        }

        CreateExternalSubscriberJob::dispatch(user: $user);
    }

    protected function onEmailChanged(User $user): void
    {
        if ($user->hasVerifiedEmail() === false) {
            return;
        }

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

        if ($user->hasVerifiedEmail() === false) {
            return;
        }

        CreateExternalSubscriberJob::dispatch(
            user: $user,
            tags: $this->getTagsForNewSubscriber(user: $user),
        );
    }

    /**
     * @return array<int, string>
     */
    private function getTagsForNewSubscriber(User $user): array
    {
        $tags = [];

        if ($user->showcases()->exists() === true) {
            $showcaseTagUuid = Config::get('marketing.has_showcase_tag_uuid');

            if ($showcaseTagUuid !== null) {
                $tags[] = $showcaseTagUuid;
            }
        }

        return $tags;
    }
}
