<?php

namespace App\Jobs\MarketingEmail;

use App\Services\MarketingEmail\Recipients\Contracts\RecipientService;
use App\Services\MarketingEmail\Recipients\ValueObjects\CreateRecipientData;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Config;

class CreateGuestSubscriberJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $email,
    ) {}

    public function handle(RecipientService $recipientService): void
    {
        $recipientService->createRecipient(
            data: new CreateRecipientData(
                email: $this->email,
                listId: Config::get('marketing.main_list_uuid'),
            ),
            skipConfirmation: false
        );
    }
}
