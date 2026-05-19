<?php

use App\Actions\Challenge\AcceptChallengeInviteCodeAction;
use App\Jobs\MarketingEmail\AddTagToSubscriberJob;
use App\Jobs\MarketingEmail\RemoveTagFromSubscriberJob;
use App\Models\Challenge\Challenge;
use App\Models\Challenge\ChallengeInviteCode;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

test('creates pivot record on accept', function () {
    $user = User::factory()->create();
    $inviteCode = ChallengeInviteCode::factory()->create();

    $action = new AcceptChallengeInviteCodeAction;
    $action->accept(inviteCode: $inviteCode, user: $user);

    expect($inviteCode->users()->where('user_id', $user->id)->exists())->toBeTrue();
});

test('accepting same code twice does not duplicate', function () {
    $user = User::factory()->create();
    $inviteCode = ChallengeInviteCode::factory()->create();

    $action = new AcceptChallengeInviteCodeAction;
    $action->accept(inviteCode: $inviteCode, user: $user);
    $action->accept(inviteCode: $inviteCode, user: $user);

    expect($inviteCode->users)->toHaveCount(1);
});

test('does not add invite when user already has same scope for challenge', function () {
    $user = User::factory()->create();
    $challenge = Challenge::factory()->create();
    $existingCode = ChallengeInviteCode::factory()->forChallenge($challenge)->create();
    $newCode = ChallengeInviteCode::factory()->forChallenge($challenge)->create();

    $action = new AcceptChallengeInviteCodeAction;
    $action->accept(inviteCode: $existingCode, user: $user);
    $action->accept(inviteCode: $newCode, user: $user);

    expect($existingCode->users()->where('user_id', $user->id)->exists())->toBeTrue()
        ->and($newCode->users()->where('user_id', $user->id)->exists())->toBeFalse();
});

test('replaces view invite when accepting view and submit for same challenge', function () {
    $user = User::factory()->create();
    $challenge = Challenge::factory()->create();
    $viewCode = ChallengeInviteCode::factory()->forChallenge($challenge)->viewOnly()->create();
    $viewAndSubmitCode = ChallengeInviteCode::factory()->forChallenge($challenge)->create();

    $action = new AcceptChallengeInviteCodeAction;
    $action->accept(inviteCode: $viewCode, user: $user);
    $action->accept(inviteCode: $viewAndSubmitCode, user: $user);

    expect($viewCode->users()->where('user_id', $user->id)->exists())->toBeFalse()
        ->and($viewAndSubmitCode->users()->where('user_id', $user->id)->exists())->toBeTrue();
});

test('does not add view invite when user already has view and submit for challenge', function () {
    $user = User::factory()->create();
    $challenge = Challenge::factory()->create();
    $viewAndSubmitCode = ChallengeInviteCode::factory()->forChallenge($challenge)->create();
    $viewCode = ChallengeInviteCode::factory()->forChallenge($challenge)->viewOnly()->create();

    $action = new AcceptChallengeInviteCodeAction;
    $action->accept(inviteCode: $viewAndSubmitCode, user: $user);
    $action->accept(inviteCode: $viewCode, user: $user);

    expect($viewAndSubmitCode->users()->where('user_id', $user->id)->exists())->toBeTrue()
        ->and($viewCode->users()->where('user_id', $user->id)->exists())->toBeFalse();
});

test('allows invites for different challenges', function () {
    $user = User::factory()->create();
    $codeA = ChallengeInviteCode::factory()->create();
    $codeB = ChallengeInviteCode::factory()->create();

    $action = new AcceptChallengeInviteCodeAction;
    $action->accept(inviteCode: $codeA, user: $user);
    $action->accept(inviteCode: $codeB, user: $user);

    expect($codeA->users()->where('user_id', $user->id)->exists())->toBeTrue()
        ->and($codeB->users()->where('user_id', $user->id)->exists())->toBeTrue();
});

test('dispatches add tag job with challenge slug and slugified label on accept', function () {
    Queue::fake();

    $challenge = Challenge::factory()->create(['slug' => 'my-challenge']);
    $inviteCode = ChallengeInviteCode::factory()->forChallenge($challenge)->create(['label' => 'Early Access', 'code' => 'EARLY2026']);
    $user = User::factory()->create(['external_subscriber_uuid' => 'sub-uuid']);

    (new AcceptChallengeInviteCodeAction)->accept(inviteCode: $inviteCode, user: $user);

    Queue::assertPushed(AddTagToSubscriberJob::class, function (AddTagToSubscriberJob $job) {
        return $job->externalSubscriberUuid === 'sub-uuid'
            && $job->tag === 'challengeInvite:my-challenge:early-access:EARLY2026';
    });
    Queue::assertNotPushed(RemoveTagFromSubscriberJob::class);
});

test('does not dispatch tag jobs when user has no external subscriber', function () {
    Queue::fake();

    $inviteCode = ChallengeInviteCode::factory()->create();
    $user = User::factory()->create(['external_subscriber_uuid' => null]);

    (new AcceptChallengeInviteCodeAction)->accept(inviteCode: $inviteCode, user: $user);

    Queue::assertNotPushed(AddTagToSubscriberJob::class);
    Queue::assertNotPushed(RemoveTagFromSubscriberJob::class);
});

test('removes replaced invite tag and adds new tag when upgrading scope', function () {
    Queue::fake();

    $challenge = Challenge::factory()->create(['slug' => 'my-challenge']);
    $viewCode = ChallengeInviteCode::factory()->forChallenge($challenge)->viewOnly()->create(['label' => 'View Only', 'code' => 'VIEW2026']);
    $viewAndSubmitCode = ChallengeInviteCode::factory()->forChallenge($challenge)->create(['label' => 'Full Access', 'code' => 'FULL2026']);
    $user = User::factory()->create(['external_subscriber_uuid' => 'sub-uuid']);

    $action = new AcceptChallengeInviteCodeAction;
    $action->accept(inviteCode: $viewCode, user: $user);
    $action->accept(inviteCode: $viewAndSubmitCode, user: $user);

    Queue::assertPushed(RemoveTagFromSubscriberJob::class, function (RemoveTagFromSubscriberJob $job) {
        return $job->externalSubscriberUuid === 'sub-uuid'
            && $job->tag === 'challengeInvite:my-challenge:view-only:VIEW2026';
    });
    Queue::assertPushed(AddTagToSubscriberJob::class, function (AddTagToSubscriberJob $job) {
        return $job->tag === 'challengeInvite:my-challenge:full-access:FULL2026';
    });
});

test('does not dispatch tag jobs when user already has sufficient access', function () {
    Queue::fake();

    $challenge = Challenge::factory()->create();
    $viewAndSubmitCode = ChallengeInviteCode::factory()->forChallenge($challenge)->create();
    $viewCode = ChallengeInviteCode::factory()->forChallenge($challenge)->viewOnly()->create();
    $user = User::factory()->create(['external_subscriber_uuid' => 'sub-uuid']);

    $action = new AcceptChallengeInviteCodeAction;
    $action->accept(inviteCode: $viewAndSubmitCode, user: $user);
    Queue::fake();
    $action->accept(inviteCode: $viewCode, user: $user);

    Queue::assertNotPushed(AddTagToSubscriberJob::class);
    Queue::assertNotPushed(RemoveTagFromSubscriberJob::class);
});

test('returns the correct challenge', function () {
    $challenge = Challenge::factory()->create();
    $inviteCode = ChallengeInviteCode::factory()->forChallenge($challenge)->create();
    $user = User::factory()->create();

    $action = new AcceptChallengeInviteCodeAction;
    $result = $action->accept(inviteCode: $inviteCode, user: $user);

    expect($result)->toBeInstanceOf(Challenge::class)
        ->and($result->id)->toBe($challenge->id);
});
