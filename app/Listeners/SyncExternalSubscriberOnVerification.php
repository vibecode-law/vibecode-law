<?php

namespace App\Listeners;

use App\Jobs\MarketingEmail\CreateExternalSubscriberJob;
use App\Jobs\MarketingEmail\UpdateExternalSubscriberJob;
use App\Models\User;
use Illuminate\Auth\Events\Verified;

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

        CreateExternalSubscriberJob::dispatch(user: $user, skipConfirmation: true);
    }
}
