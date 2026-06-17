<?php

use App\Jobs\MarketingEmail\CreateExternalSubscriberJob;
use App\Models\Challenge\Challenge;
use App\Models\Challenge\ChallengeInviteCode;
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
                && $data->lastName === $user->last_name
                && $data->tags === [];
        })
        ->andReturn($subscriberUuid);

    app()->instance(RecipientService::class, $recipientService);

    $job = new CreateExternalSubscriberJob(user: $user);
    $job->handle($recipientService);

    expect($user->refresh()->external_subscriber_uuid)->toBe($subscriberUuid);
});

it('creates external subscriber with tags when provided', function () {
    $subscriberUuid = '11111111-1111-1111-1111-111111111111';
    $tags = ['tag-uuid-1', 'tag-uuid-2'];

    $user = User::factory()->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'external_subscriber_uuid' => null,
    ]);

    $recipientService = Mockery::mock(RecipientService::class);
    $recipientService->shouldReceive('createRecipient')
        ->once()
        ->withArgs(function (CreateRecipientData $data) use ($user, $tags) {
            return $data->email === $user->email
                && $data->firstName === $user->first_name
                && $data->lastName === $user->last_name
                && $data->tags === $tags;
        })
        ->andReturn($subscriberUuid);

    app()->instance(RecipientService::class, $recipientService);

    $job = new CreateExternalSubscriberJob(user: $user, tags: $tags);
    $job->handle($recipientService);

    expect($user->refresh()->external_subscriber_uuid)->toBe($subscriberUuid);
});

it('includes tags for challenge invite codes accepted after the job was dispatched', function () {
    $subscriberUuid = '11111111-1111-1111-1111-111111111111';

    $user = User::factory()->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'external_subscriber_uuid' => null,
    ]);

    // Job captured these tags at dispatch time, before the invite was accepted.
    $job = new CreateExternalSubscriberJob(user: $user, tags: ['is-user-tag-uuid']);

    $challenge = Challenge::factory()->create(['slug' => 'my-challenge']);
    $inviteCode = ChallengeInviteCode::factory()->forChallenge($challenge)->create([
        'label' => 'Early Access',
        'code' => 'EARLY2026',
    ]);
    $user->acceptedChallengeInviteCodes()->attach($inviteCode);

    $recipientService = Mockery::mock(RecipientService::class);
    $recipientService->shouldReceive('createRecipient')
        ->once()
        ->withArgs(function (CreateRecipientData $data) {
            return $data->tags === ['is-user-tag-uuid', 'challengeInvite:my-challenge:early-access:EARLY2026'];
        })
        ->andReturn($subscriberUuid);

    app()->instance(RecipientService::class, $recipientService);

    $job->handle($recipientService);

    expect($user->refresh()->external_subscriber_uuid)->toBe($subscriberUuid);
});

it('does not duplicate a challenge invite tag already passed at dispatch', function () {
    $subscriberUuid = '11111111-1111-1111-1111-111111111111';

    $user = User::factory()->create(['external_subscriber_uuid' => null]);

    $challenge = Challenge::factory()->create(['slug' => 'my-challenge']);
    $inviteCode = ChallengeInviteCode::factory()->forChallenge($challenge)->create([
        'label' => 'Early Access',
        'code' => 'EARLY2026',
    ]);
    $user->acceptedChallengeInviteCodes()->attach($inviteCode);

    $inviteTag = 'challengeInvite:my-challenge:early-access:EARLY2026';

    // Import flow passes the invite tag explicitly at dispatch; it should not be duplicated.
    $job = new CreateExternalSubscriberJob(user: $user, tags: [$inviteTag]);

    $recipientService = Mockery::mock(RecipientService::class);
    $recipientService->shouldReceive('createRecipient')
        ->once()
        ->withArgs(function (CreateRecipientData $data) use ($inviteTag) {
            return $data->tags === [$inviteTag];
        })
        ->andReturn($subscriberUuid);

    app()->instance(RecipientService::class, $recipientService);

    $job->handle($recipientService);

    expect($user->refresh()->external_subscriber_uuid)->toBe($subscriberUuid);
});

it('passes skip confirmation flag to recipient service', function () {
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
        ->withArgs(function (CreateRecipientData $data, bool $skipConfirmation) use ($user) {
            return $data->email === $user->email
                && $data->firstName === $user->first_name
                && $data->lastName === $user->last_name
                && $skipConfirmation === true;
        })
        ->andReturn($subscriberUuid);

    app()->instance(RecipientService::class, $recipientService);

    $job = new CreateExternalSubscriberJob(user: $user, skipConfirmation: true);
    $job->handle($recipientService);

    expect($user->refresh()->external_subscriber_uuid)->toBe($subscriberUuid);
});
