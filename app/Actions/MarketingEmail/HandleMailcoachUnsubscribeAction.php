<?php

namespace App\Actions\MarketingEmail;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class HandleMailcoachUnsubscribeAction
{
    public function execute(string $email): void
    {
        $user = User::query()
            ->where('email', $email)
            ->first();

        if ($user === null) {
            Log::channel('mailcoachWebhook')->error('Unsubscribe webhook received for unknown email', [
                'email' => $email,
            ]);

            return;
        }

        if ($user->marketing_opt_out_at !== null) {
            return;
        }

        $user->update([
            'marketing_opt_out_at' => now(),
        ]);

        Log::channel('mailcoachWebhook')->info('User unsubscribed from marketing', [
            'user_id' => $user->id,
            'email' => $email,
        ]);
    }
}
