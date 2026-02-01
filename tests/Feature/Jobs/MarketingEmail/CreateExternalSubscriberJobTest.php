<?php

use App\Jobs\MarketingEmail\CreateExternalSubscriberJob;
use App\Models\User;
use App\Services\MarketingEmail\Recipients\Contracts\RecipientService;
use App\Services\MarketingEmail\Recipients\ValueObjects\CreateRecipientData;

it('creates external subscriber and saves uuid to user', function () {
    $subscriberUuid = '11111111-1111-1111-1111-111111111111';

    $user = User::factory()->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'external_subscriber_uuid' => null,
    ]);

    $recipientService = Mockery::mock(RecipientService::class);
    $recipientService->shouldReceive('createRecipient')
        ->once()
        ->withArgs(function (CreateRecipientData $data) use ($user) {
            return $data->email === $user->email
                && $data->firstName === $user->first_name
                && $data->lastName === $user->last_name;
        })
        ->andReturn($subscriberUuid);

    app()->instance(RecipientService::class, $recipientService);

    $job = new CreateExternalSubscriberJob(user: $user);
    $job->handle($recipientService);

    expect($user->refresh()->external_subscriber_uuid)->toBe($subscriberUuid);
});
