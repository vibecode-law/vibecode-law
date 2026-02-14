<?php

use App\Jobs\MarketingEmail\AddIsUserTagToSubscriberJob;
use App\Models\User;
use App\Services\MarketingEmail\Recipients\Contracts\RecipientService;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Support\Facades\Config;

it('adds is_user tag to external subscriber', function () {
    $externalUuid = '22222222-2222-2222-2222-222222222222';
    $tagUuid = 'is-user-tag-uuid';

    Config::set('marketing.is_user_tag_uuid', $tagUuid);

    $user = User::factory()->create([
        'external_subscriber_uuid' => $externalUuid,
    ]);

    $recipientService = Mockery::mock(RecipientService::class);
    $recipientService->shouldReceive('addTags')
        ->once()
        ->withArgs(function (string $externalId, array $tags) use ($externalUuid, $tagUuid) {
            return $externalId === $externalUuid
                && $tags === [$tagUuid];
        });

    $job = new AddIsUserTagToSubscriberJob(user: $user);
    $job->handle($recipientService);
});

it('does not add tag when user has no external subscriber uuid', function () {
    Config::set('marketing.is_user_tag_uuid', 'is-user-tag-uuid');

    $user = User::factory()->create([
        'external_subscriber_uuid' => null,
    ]);

    $recipientService = Mockery::mock(RecipientService::class);
    $recipientService->shouldNotReceive('addTags');

    $job = new AddIsUserTagToSubscriberJob(user: $user);
    $job->handle($recipientService);
});

it('does not add tag when config tag uuid is null', function () {
    Config::set('marketing.is_user_tag_uuid', null);

    $user = User::factory()->create([
        'external_subscriber_uuid' => '22222222-2222-2222-2222-222222222222',
    ]);

    $recipientService = Mockery::mock(RecipientService::class);
    $recipientService->shouldNotReceive('addTags');

    $job = new AddIsUserTagToSubscriberJob(user: $user);
    $job->handle($recipientService);
});

it('has rate limiting middleware', function () {
    $user = User::factory()->create();

    $job = new AddIsUserTagToSubscriberJob(user: $user);
    $middleware = $job->middleware();

    expect($middleware)->toHaveCount(1)
        ->and($middleware[0])->toBeInstanceOf(RateLimited::class);
});
