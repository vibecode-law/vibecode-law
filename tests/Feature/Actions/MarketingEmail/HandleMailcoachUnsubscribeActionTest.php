<?php

use App\Actions\MarketingEmail\HandleMailcoachUnsubscribeAction;
use App\Models\User;
use Illuminate\Support\Facades\Log;

it('updates user marketing opt-out status when email matches', function () {
    $user = User::factory()->create([
        'email' => 'subscriber@example.com',
        'marketing_opt_out_at' => null,
    ]);

    $action = new HandleMailcoachUnsubscribeAction;
    $action->execute(email: 'subscriber@example.com');

    $user->refresh();
    expect($user->marketing_opt_out_at)->not->toBeNull();
});

it('does not update user when already opted out', function () {
    $optOutTime = now()->subDay();

    $user = User::factory()->create([
        'email' => 'subscriber@example.com',
        'marketing_opt_out_at' => $optOutTime,
    ]);

    $action = new HandleMailcoachUnsubscribeAction;
    $action->execute(email: 'subscriber@example.com');

    $user->refresh();
    expect($user->marketing_opt_out_at->toDateTimeString())->toBe($optOutTime->toDateTimeString());
});

it('logs error when email does not match any user', function () {
    Log::shouldReceive('channel')
        ->with('mailcoachWebhook')
        ->once()
        ->andReturnSelf();

    Log::shouldReceive('error')
        ->once()
        ->with('Unsubscribe webhook received for unknown email', [
            'email' => 'unknown@example.com',
        ]);

    $action = new HandleMailcoachUnsubscribeAction;
    $action->execute(email: 'unknown@example.com');
});

it('logs info when user is successfully unsubscribed', function () {
    $user = User::factory()->create([
        'email' => 'subscriber@example.com',
        'marketing_opt_out_at' => null,
    ]);

    Log::shouldReceive('channel')
        ->with('mailcoachWebhook')
        ->once()
        ->andReturnSelf();

    Log::shouldReceive('info')
        ->once()
        ->with('User unsubscribed from marketing', [
            'user_id' => $user->id,
            'email' => 'subscriber@example.com',
        ]);

    $action = new HandleMailcoachUnsubscribeAction;
    $action->execute(email: 'subscriber@example.com');
});
