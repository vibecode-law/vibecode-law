<?php

namespace App\Jobs\MarketingEmail;

use App\Models\User;
use App\Services\MarketingEmail\Recipients\Contracts\RecipientService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class UnsubscribeFromMarketingJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public User $user,
    ) {}

    public function handle(RecipientService $recipientService): void
    {
        if ($this->user->external_subscriber_uuid === null) {
            return;
        }

        $recipientService->unsubscribeRecipient(
            externalId: $this->user->external_subscriber_uuid,
        );
    }
}
