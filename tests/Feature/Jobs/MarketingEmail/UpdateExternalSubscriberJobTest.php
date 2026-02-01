<?php

use App\Jobs\MarketingEmail\UpdateExternalSubscriberJob;
use App\Models\User;
use App\Services\MarketingEmail\Recipients\Contracts\RecipientService;
use App\Services\MarketingEmail\Recipients\ValueObjects\UpdateRecipientData;

it('updates external subscriber email', function () {
    $existingUuid = '22222222-2222-2222-2222-222222222222';

    $user = User::factory()->create([
        'email' => 'new-email@example.com',
        'external_subscriber_uuid' => $existingUuid,
    ]);

    $recipientService = Mockery::mock(RecipientService::class);
    $recipientService->shouldReceive('updateRecipient')
        ->once()
        ->withArgs(function (string $externalId, UpdateRecipientData $data) use ($existingUuid, $user) {
            return $externalId === $existingUuid
                && $data->email === $user->email;
        });

    $job = new UpdateExternalSubscriberJob(user: $user);
    $job->handle($recipientService);
});

it('does not update when user has no external subscriber uuid', function () {
    $user = User::factory()->create([
        'email' => 'email@example.com',
        'external_subscriber_uuid' => null,
    ]);

    $recipientService = Mockery::mock(RecipientService::class);
    $recipientService->shouldNotReceive('updateRecipient');

    $job = new UpdateExternalSubscriberJob(user: $user);
    $job->handle($recipientService);
});
