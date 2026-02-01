<?php

namespace App\Jobs\MarketingEmail;

use App\Models\User;
use App\Services\MarketingEmail\Recipients\Contracts\RecipientService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Config;

class AddShowcaseTagToSubscriberJob implements ShouldQueue
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

        $tagUuid = Config::get('marketing.has_showcase_tag_uuid');

        if ($tagUuid === null) {
            return;
        }

        $recipientService->addTags(
            externalId: $this->user->external_subscriber_uuid,
            tags: [$tagUuid],
        );
    }
}
