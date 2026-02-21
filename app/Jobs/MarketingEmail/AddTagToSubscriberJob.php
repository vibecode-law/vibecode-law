<?php

namespace App\Jobs\MarketingEmail;

use App\Services\MarketingEmail\Recipients\Contracts\RecipientService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class AddTagToSubscriberJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $externalSubscriberUuid,
        public string $tag,
    ) {}

    public function handle(RecipientService $recipientService): void
    {
        $recipientService->addTags(
            externalId: $this->externalSubscriberUuid,
            tags: [$this->tag],
        );
    }
}
