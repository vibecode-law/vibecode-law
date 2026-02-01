<?php

namespace App\Jobs\MarketingEmail;

use App\Models\User;
use App\Services\MarketingEmail\Recipients\Contracts\RecipientService;
use App\Services\MarketingEmail\Recipients\ValueObjects\CreateRecipientData;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Config;

class CreateExternalSubscriberJob implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<int, string>  $tags
     */
    public function __construct(
        public User $user,
        public array $tags = [],
    ) {}

    public function handle(RecipientService $recipientService): void
    {
        $subscriberUuid = $recipientService->createRecipient(
            data: new CreateRecipientData(
                email: $this->user->email,
                listId: Config::get('marketing.main_list_uuid'),
                firstName: $this->user->first_name,
                lastName: $this->user->last_name,
                tags: $this->tags,
            ),
        );

        $this->user->external_subscriber_uuid = $subscriberUuid;
        $this->user->save();
    }
}
