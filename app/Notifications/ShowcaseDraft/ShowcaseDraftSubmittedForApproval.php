<?php

namespace App\Notifications\ShowcaseDraft;

use App\Models\Showcase\Showcase;
use App\Models\Showcase\ShowcaseDraft;
use App\Models\User;
use App\Notifications\BaseNotification;
use Illuminate\Notifications\Messages\MailMessage;

class ShowcaseDraftSubmittedForApproval extends BaseNotification
{
    public function __construct(public ShowcaseDraft $draft) {}

    /**
     * @param  mixed  $notifiable
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * @param  mixed  $notifiable
     */
    public function toMail($notifiable): MailMessage
    {
        /** @var Showcase $showcase */
        $showcase = $this->draft->showcase;

        /** @var User $user */
        $user = $showcase->user;

        return (new MailMessage)
            ->subject('Showcase Edit Submitted for Approval')
            ->greeting('Hello!')
            ->line("Changes to showcase \"{$showcase->title}\" have been submitted by {$user->first_name} {$user->last_name} and are awaiting approval.")
            ->action('Review Changes', route('showcase.draft.edit', $this->draft))
            ->line('Please review and approve or reject these changes.');
    }

    /**
     * @param  mixed  $notifiable
     * @return array<string, mixed>
     */
    public function toArray($notifiable): array
    {
        /** @var Showcase $showcase */
        $showcase = $this->draft->showcase;

        /** @var User $user */
        $user = $showcase->user;

        return [
            'draft_id' => $this->draft->id,
            'showcase_id' => $this->draft->showcase_id,
            'showcase_title' => $showcase->title,
            'submitted_by' => $user->first_name.' '.$user->last_name,
            'message' => 'Showcase changes have been submitted for approval',
        ];
    }
}
