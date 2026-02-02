<?php

namespace App\Webhooks\Mailcoach;

use App\Actions\MarketingEmail\HandleMailcoachUnsubscribeAction;
use Spatie\WebhookClient\Jobs\ProcessWebhookJob;

class ProcessMailcoachWebhookJob extends ProcessWebhookJob
{
    public function handle(HandleMailcoachUnsubscribeAction $handleUnsubscribe): void
    {
        /** @var array{event?: string, email?: string} $payload */
        $payload = $this->webhookCall->payload;

        $event = $payload['event'] ?? null;

        if ($event !== 'UnsubscribedEvent') {
            return;
        }

        $email = $payload['email'] ?? null;

        if ($email === null) {
            return;
        }

        $handleUnsubscribe->execute(email: $email);
    }
}
