<?php

namespace App\Jobs\MarketingEmail;

use App\Models\User;
use App\Services\MarketingEmail\Recipients\Contracts\RecipientService;
use App\Services\MarketingEmail\Recipients\ValueObjects\UpdateRecipientData;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class UpdateExternalSubscriberJob implements ShouldQueue
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

        $recipientService->updateRecipient(
            externalId: $this->user->external_subscriber_uuid,
            data: new UpdateRecipientData(
                email: $this->user->email,
            ),
        );
    }
}
