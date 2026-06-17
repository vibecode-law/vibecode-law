<?php

namespace App\Jobs\MarketingEmail;

use App\Actions\Challenge\AcceptChallengeInviteCodeAction;
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
        public bool $skipConfirmation = false,
    ) {}

    public function handle(RecipientService $recipientService): void
    {
        $subscriberUuid = $recipientService->createRecipient(
            data: new CreateRecipientData(
                email: $this->user->email,
                listId: Config::get('marketing.main_list_uuid'),
                firstName: $this->user->first_name,
                lastName: $this->user->last_name,
                tags: $this->resolveTags(),
            ),
            skipConfirmation: $this->skipConfirmation,
        );

        $this->user->external_subscriber_uuid = $subscriberUuid;
        $this->user->save();
    }

    /**
     * Merge the tags captured at dispatch with the user's currently-accepted
     * challenge invite tags, which may have been accepted after this job was
     * queued but before it runs.
     *
     * @return array<int, string>
     */
    private function resolveTags(): array
    {
        $inviteTags = (new AcceptChallengeInviteCodeAction)->tagsForAcceptedInviteCodes(user: $this->user);

        return array_values(array_unique([...$this->tags, ...$inviteTags]));
    }
}
