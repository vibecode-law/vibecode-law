<?php

use App\Jobs\MarketingEmail\UnsubscribeFromMarketingJob;
use App\Models\User;
use App\Services\MarketingEmail\Recipients\Contracts\RecipientService;

it('unsubscribes external subscriber', function () {
    $existingUuid = '33333333-3333-3333-3333-333333333333';

    $user = User::factory()->create([
        'external_subscriber_uuid' => $existingUuid,
    ]);

    $recipientService = Mockery::mock(RecipientService::class);
    $recipientService->shouldReceive('unsubscribeRecipient')
        ->once()
        ->with($existingUuid);

    $job = new UnsubscribeFromMarketingJob(user: $user);
    $job->handle($recipientService);
});

it('does not unsubscribe when user has no external subscriber uuid', function () {
    $user = User::factory()->create([
        'external_subscriber_uuid' => null,
    ]);

    $recipientService = Mockery::mock(RecipientService::class);
    $recipientService->shouldNotReceive('unsubscribeRecipient');

    $job = new UnsubscribeFromMarketingJob(user: $user);
    $job->handle($recipientService);
});
