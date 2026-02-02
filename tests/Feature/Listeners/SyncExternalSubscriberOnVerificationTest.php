<?php

use App\Jobs\MarketingEmail\CreateExternalSubscriberJob;
use App\Jobs\MarketingEmail\UpdateExternalSubscriberJob;
use App\Listeners\SyncExternalSubscriberOnVerification;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Queue;

it('dispatches CreateExternalSubscriberJob when user verifies email and is subscribed to marketing', function () {
    Queue::fake();

    $user = User::factory()->create([
        'email_verified_at' => now(),
        'marketing_opt_out_at' => null,
        'external_subscriber_uuid' => null,
    ]);

    $listener = new SyncExternalSubscriberOnVerification;
    $listener->handle(new Verified($user));

    Queue::assertPushed(CreateExternalSubscriberJob::class, function (CreateExternalSubscriberJob $job) use ($user) {
        return $job->user->is($user)
            && $job->skipConfirmation === true;
    });
});

it('dispatches UpdateExternalSubscriberJob when user has existing external subscriber', function () {
    Queue::fake();

    $user = User::factory()->create([
        'email_verified_at' => now(),
        'marketing_opt_out_at' => null,
        'external_subscriber_uuid' => '11111111-1111-1111-1111-111111111111',
    ]);

    $listener = new SyncExternalSubscriberOnVerification;
    $listener->handle(new Verified($user));

    Queue::assertPushed(UpdateExternalSubscriberJob::class, function (UpdateExternalSubscriberJob $job) use ($user) {
        return $job->user->is($user);
    });
    Queue::assertNotPushed(CreateExternalSubscriberJob::class);
});

it('dispatches UpdateExternalSubscriberJob even when user has opted out of marketing', function () {
    Queue::fake();

    $user = User::factory()->create([
        'email_verified_at' => now(),
        'marketing_opt_out_at' => now(),
        'external_subscriber_uuid' => '11111111-1111-1111-1111-111111111111',
    ]);

    $listener = new SyncExternalSubscriberOnVerification;
    $listener->handle(new Verified($user));

    Queue::assertPushed(UpdateExternalSubscriberJob::class, function (UpdateExternalSubscriberJob $job) use ($user) {
        return $job->user->is($user);
    });
});

it('does not dispatch CreateExternalSubscriberJob when user has opted out of marketing', function () {
    Queue::fake();

    $user = User::factory()->create([
        'email_verified_at' => now(),
        'marketing_opt_out_at' => now(),
        'external_subscriber_uuid' => null,
    ]);

    $listener = new SyncExternalSubscriberOnVerification;
    $listener->handle(new Verified($user));

    Queue::assertNotPushed(CreateExternalSubscriberJob::class);
    Queue::assertNotPushed(UpdateExternalSubscriberJob::class);
});
