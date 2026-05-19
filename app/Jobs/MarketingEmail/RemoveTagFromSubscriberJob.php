<?php

namespace App\Jobs\MarketingEmail;

use App\Services\MarketingEmail\Recipients\Contracts\RecipientService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RemoveTagFromSubscriberJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $externalSubscriberUuid,
        public string $tag,
    ) {}

    public function handle(RecipientService $recipientService): void
    {
        $recipientService->removeTags(
            externalId: $this->externalSubscriberUuid,
            tags: [$this->tag],
        );
    }
}
