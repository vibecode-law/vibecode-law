<?php

use App\Jobs\MarketingEmail\ResubscribeToMarketingJob;
use App\Models\User;
use App\Services\MarketingEmail\Recipients\Contracts\RecipientService;

it('resubscribes external subscriber', function () {
    $existingUuid = '44444444-4444-4444-4444-444444444444';

    $user = User::factory()->create([
        'external_subscriber_uuid' => $existingUuid,
    ]);

    $recipientService = Mockery::mock(RecipientService::class);
    $recipientService->shouldReceive('resubscribeRecipient')
        ->once()
        ->with($existingUuid);

    $job = new ResubscribeToMarketingJob(user: $user);
    $job->handle($recipientService);
});

it('does not resubscribe when user has no external subscriber uuid', function () {
    $user = User::factory()->create([
        'external_subscriber_uuid' => null,
    ]);

    $recipientService = Mockery::mock(RecipientService::class);
    $recipientService->shouldNotReceive('resubscribeRecipient');

    $job = new ResubscribeToMarketingJob(user: $user);
    $job->handle($recipientService);
});
