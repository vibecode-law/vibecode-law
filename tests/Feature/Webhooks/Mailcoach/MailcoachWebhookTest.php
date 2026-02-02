<?php

use App\Models\User;
use App\Webhooks\Mailcoach\ProcessMailcoachWebhookJob;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;
use Spatie\WebhookClient\Models\WebhookCall;

beforeEach(function () {
    Config::set('webhook-client.configs.0.signing_secret', 'test-webhook-secret');
});

it('accepts valid webhook and queues processing job', function () {
    Queue::fake();

    $payload = [
        'event' => 'UnsubscribedEvent',
        'email' => 'test@example.com',
    ];

    $signature = hash_hmac(
        algo: 'sha256',
        data: json_encode($payload),
        key: 'test-webhook-secret',
    );

    $this->postJson(
        uri: route('webhook-client-mailcoach'),
        data: $payload,
        headers: ['Signature' => $signature],
    )->assertSuccessful();

    Queue::assertPushed(ProcessMailcoachWebhookJob::class);
});

it('stores webhook call in database', function () {
    Queue::fake();

    $payload = [
        'event' => 'UnsubscribedEvent',
        'email' => 'test@example.com',
    ];

    $signature = hash_hmac(
        algo: 'sha256',
        data: json_encode($payload),
        key: 'test-webhook-secret',
    );

    $this->postJson(
        uri: route('webhook-client-mailcoach'),
        data: $payload,
        headers: ['Signature' => $signature],
    )->assertSuccessful();

    expect(WebhookCall::query()->count())->toBe(1);

    $webhookCall = WebhookCall::query()->first();
    expect($webhookCall->payload)->toBe($payload);
    expect($webhookCall->name)->toBe('mailcoach');
});

it('rejects webhook with invalid signature', function () {
    Queue::fake();

    $payload = [
        'event' => 'UnsubscribedEvent',
        'email' => 'test@example.com',
    ];

    $this->postJson(
        uri: route('webhook-client-mailcoach'),
        data: $payload,
        headers: ['Signature' => 'invalid-signature'],
    )->assertInternalServerError();

    Queue::assertNothingPushed();
    expect(WebhookCall::query()->count())->toBe(0);
});

it('rejects webhook with missing signature', function () {
    Queue::fake();

    $payload = [
        'event' => 'UnsubscribedEvent',
        'email' => 'test@example.com',
    ];

    $this->postJson(
        uri: route('webhook-client-mailcoach'),
        data: $payload,
    )->assertInternalServerError();

    Queue::assertNothingPushed();
    expect(WebhookCall::query()->count())->toBe(0);
});

it('rejects webhook when secret is not configured', function () {
    Queue::fake();
    Config::set('webhook-client.configs.0.signing_secret', null);

    $payload = [
        'event' => 'UnsubscribedEvent',
        'email' => 'test@example.com',
    ];

    $signature = hash_hmac(
        algo: 'sha256',
        data: json_encode($payload),
        key: 'any-secret',
    );

    $this->postJson(
        uri: route('webhook-client-mailcoach'),
        data: $payload,
        headers: ['Signature' => $signature],
    )->assertInternalServerError();

    Queue::assertNothingPushed();
});

it('processes unsubscribe event and updates user opt-out status', function () {
    $user = User::factory()->create([
        'email' => 'subscriber@example.com',
        'marketing_opt_out_at' => null,
    ]);

    $payload = [
        'event' => 'UnsubscribedEvent',
        'email' => 'subscriber@example.com',
    ];

    $signature = hash_hmac(
        algo: 'sha256',
        data: json_encode($payload),
        key: 'test-webhook-secret',
    );

    $this->postJson(
        uri: route('webhook-client-mailcoach'),
        data: $payload,
        headers: ['Signature' => $signature],
    )->assertSuccessful();

    $user->refresh();
    expect($user->marketing_opt_out_at)->not->toBeNull();
});

it('handles non-unsubscribe events gracefully', function () {
    Queue::fake();

    $payload = [
        'event' => 'SubscribedEvent',
        'email' => 'test@example.com',
    ];

    $signature = hash_hmac(
        algo: 'sha256',
        data: json_encode($payload),
        key: 'test-webhook-secret',
    );

    $this->postJson(
        uri: route('webhook-client-mailcoach'),
        data: $payload,
        headers: ['Signature' => $signature],
    )->assertSuccessful();

    Queue::assertPushed(ProcessMailcoachWebhookJob::class);
});
