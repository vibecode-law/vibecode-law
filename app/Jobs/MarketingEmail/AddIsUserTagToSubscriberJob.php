<?php

namespace App\Jobs\MarketingEmail;

use App\Models\User;
use App\Services\MarketingEmail\Recipients\Contracts\RecipientService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Support\Facades\Config;

class AddIsUserTagToSubscriberJob implements ShouldQueue
{
    use Queueable;

    /**
     * @var int
     */
    public $tries = 0;

    /**
     * @var int
     */
    public $maxExceptions = 1;

    public function __construct(
        public User $user,
    ) {}

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [new RateLimited('marketing-api')];
    }

    public function handle(RecipientService $recipientService): void
    {
        if ($this->user->external_subscriber_uuid === null) {
            return;
        }

        $tagUuid = Config::get('marketing.is_user_tag_uuid');

        if ($tagUuid === null) {
            return;
        }

        $recipientService->addTags(
            externalId: $this->user->external_subscriber_uuid,
            tags: [$tagUuid],
        );
    }
}
