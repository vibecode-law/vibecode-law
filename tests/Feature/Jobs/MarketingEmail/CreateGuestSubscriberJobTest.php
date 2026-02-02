<?php

use App\Jobs\MarketingEmail\CreateGuestSubscriberJob;
use App\Services\MarketingEmail\Recipients\Contracts\RecipientService;
use App\Services\MarketingEmail\Recipients\ValueObjects\CreateRecipientData;
use Illuminate\Support\Facades\Config;

it('creates a subscriber with the provided email', function () {
    $email = 'guest@example.com';
    $listUuid = 'test-main-list-uuid';

    Config::set('marketing.main_list_uuid', $listUuid);

    $recipientService = Mockery::mock(RecipientService::class);
    $recipientService->shouldReceive('createRecipient')
        ->once()
        ->withArgs(function (CreateRecipientData $data, bool $skipConfirmation) use ($email, $listUuid) {
            return $data->email === $email
                && $data->listId === $listUuid
                && $data->firstName === null
                && $data->lastName === null
                && $data->tags === []
                && $skipConfirmation === false;
        })
        ->andReturn('subscriber-uuid');

    app()->instance(RecipientService::class, $recipientService);

    $job = new CreateGuestSubscriberJob(email: $email);
    $job->handle($recipientService);
});

it('uses the configured marketing list uuid', function () {
    $customListUuid = 'custom-list-uuid-12345';

    Config::set('marketing.main_list_uuid', $customListUuid);

    $recipientService = Mockery::mock(RecipientService::class);
    $recipientService->shouldReceive('createRecipient')
        ->once()
        ->withArgs(function (CreateRecipientData $data) use ($customListUuid) {
            return $data->listId === $customListUuid;
        })
        ->andReturn('subscriber-uuid');

    app()->instance(RecipientService::class, $recipientService);

    $job = new CreateGuestSubscriberJob(email: 'test@example.com');
    $job->handle($recipientService);
});

it('does not skip confirmation for guest subscribers', function () {
    $recipientService = Mockery::mock(RecipientService::class);
    $recipientService->shouldReceive('createRecipient')
        ->once()
        ->withArgs(function (CreateRecipientData $data, bool $skipConfirmation) {
            return $skipConfirmation === false;
        })
        ->andReturn('subscriber-uuid');

    app()->instance(RecipientService::class, $recipientService);

    $job = new CreateGuestSubscriberJob(email: 'guest@example.com');
    $job->handle($recipientService);
});
