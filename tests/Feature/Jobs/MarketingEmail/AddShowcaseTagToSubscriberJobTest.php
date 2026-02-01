<?php

use App\Jobs\MarketingEmail\AddShowcaseTagToSubscriberJob;
use App\Models\User;
use App\Services\MarketingEmail\Recipients\Contracts\RecipientService;
use Illuminate\Support\Facades\Config;

it('adds showcase tag to external subscriber', function () {
    $externalUuid = '22222222-2222-2222-2222-222222222222';
    $tagUuid = 'tag-uuid-123';

    Config::set('marketing.has_showcase_tag_uuid', $tagUuid);

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

    $job = new AddShowcaseTagToSubscriberJob(user: $user);
    $job->handle($recipientService);
});

it('does not add tag when user has no external subscriber uuid', function () {
    Config::set('marketing.has_showcase_tag_uuid', 'tag-uuid-123');

    $user = User::factory()->create([
        'external_subscriber_uuid' => null,
    ]);

    $recipientService = Mockery::mock(RecipientService::class);
    $recipientService->shouldNotReceive('addTags');

    $job = new AddShowcaseTagToSubscriberJob(user: $user);
    $job->handle($recipientService);
});

it('does not add tag when config tag uuid is null', function () {
    Config::set('marketing.has_showcase_tag_uuid', null);

    $user = User::factory()->create([
        'external_subscriber_uuid' => '22222222-2222-2222-2222-222222222222',
    ]);

    $recipientService = Mockery::mock(RecipientService::class);
    $recipientService->shouldNotReceive('addTags');

    $job = new AddShowcaseTagToSubscriberJob(user: $user);
    $job->handle($recipientService);
});
