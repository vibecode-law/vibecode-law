<?php

use App\Jobs\MarketingEmail\AddTagToSubscriberJob;
use App\Services\MarketingEmail\Recipients\Contracts\RecipientService;

it('adds tag to external subscriber', function () {
    $externalUuid = '11111111-1111-1111-1111-111111111111';
    $tag = 'startedCourse:intro-to-ai';

    $recipientService = Mockery::mock(RecipientService::class);
    $recipientService->shouldReceive('addTags')
        ->once()
        ->withArgs(function (string $externalId, array $tags) use ($externalUuid, $tag) {
            return $externalId === $externalUuid
                && $tags === [$tag];
        });

    $job = new AddTagToSubscriberJob(
        externalSubscriberUuid: $externalUuid,
        tag: $tag,
    );
    $job->handle($recipientService);
});
