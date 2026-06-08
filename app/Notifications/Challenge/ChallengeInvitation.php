<?php

namespace App\Notifications\Challenge;

use App\Enums\InviteCodeScope;
use App\Models\Challenge\ChallengeInviteCode;
use App\Models\User;
use App\Notifications\BaseNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;

class ChallengeInvitation extends BaseNotification
{
    public function __construct(
        public ChallengeInviteCode $inviteCode,
        public bool $isNewUser,
        public ?string $passwordToken = null,
        public ?string $customMessage = null,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        $challengeTitle = $this->inviteCode->challenge->title;
        $canSubmit = $this->inviteCode->scope === InviteCodeScope::ViewAndSubmit;
        $challengeUrl = URL::route('inspiration.challenges.show', ['challenge' => $this->inviteCode->challenge]);

        $message = (new MailMessage)
            ->subject("You've been invited to {$challengeTitle}")
            ->greeting("Hello {$notifiable->first_name}!")
            ->line($canSubmit === true
                ? "You've been added as a participant in the \"{$challengeTitle}\" challenge."
                : "You've been invited to view the \"{$challengeTitle}\" challenge.");

        if ($this->customMessage !== null && trim($this->customMessage) !== '') {
            $paragraphs = preg_split('/\R{2,}/', trim($this->customMessage));

            foreach ($paragraphs as $paragraph) {
                $message->line(trim($paragraph));
            }
        }

        if ($this->isNewUser === true && $this->passwordToken !== null) {
            $resetUrl = URL::route('password.reset', [
                'token' => $this->passwordToken,
                'email' => $notifiable->email,
            ]);

            $message
                ->line($canSubmit === true
                    ? 'A '.config('app.name').' account has been created for you, which you\'ll need to submit your entry. To get started, please set your password by clicking the button below.'
                    : 'A '.config('app.name').' account has been created for you so you can view the challenge. To get started, please set your password by clicking the button below.')
                ->action('Set Your Password', $resetUrl)
                ->line('This password reset link will expire in '.config('auth.passwords.users.expire').' minutes. If it expires, use the "Forgot password?" feature on the login page.')
                ->line("Once you're set up, you can [view the challenge here]({$challengeUrl}).");
        } else {
            $message
                ->line($canSubmit === true
                    ? 'You can access the challenge and submit your entry using your existing account.'
                    : 'You can view the challenge using your existing account.')
                ->action('View Challenge', $challengeUrl);
        }

        return $message
            ->line("You can also sign in with LinkedIn, provided your LinkedIn account uses the email address {$notifiable->email}.")
            ->line('If you did not expect this invitation, no further action is required.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [];
    }
}
