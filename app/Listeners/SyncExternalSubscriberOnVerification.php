<?php

namespace App\Listeners;

use App\Jobs\MarketingEmail\CreateExternalSubscriberJob;
use App\Jobs\MarketingEmail\UpdateExternalSubscriberJob;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Config;

class SyncExternalSubscriberOnVerification
{
    public function handle(Verified $event): void
    {
        $user = $event->user;

        if ($user instanceof User === false) {
            return;
        }

        if ($user->external_subscriber_uuid !== null) {
            UpdateExternalSubscriberJob::dispatch(user: $user);

            return;
        }

        if ($user->isSubscribedToMarketing() === false) {
            return;
        }

        CreateExternalSubscriberJob::dispatch(
            user: $user,
            tags: $this->getTagsForUser(user: $user),
            skipConfirmation: true,
        );
    }

    /**
     * @return array<int, string>
     */
    private function getTagsForUser(User $user): array
    {
        $tags = [];

        $isUserTagUuid = Config::get('marketing.is_user_tag_uuid');

        if ($isUserTagUuid !== null) {
            $tags[] = $isUserTagUuid;
        }

        $showcaseTagUuid = Config::get('marketing.has_showcase_tag_uuid');

        if ($showcaseTagUuid !== null && $user->showcases()->exists() === true) {
            $tags[] = $showcaseTagUuid;
        }

        return $tags;
    }
}
