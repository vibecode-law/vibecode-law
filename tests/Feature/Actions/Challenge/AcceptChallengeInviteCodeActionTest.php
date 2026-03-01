<?php

use App\Actions\Challenge\AcceptChallengeInviteCodeAction;
use App\Models\Challenge\Challenge;
use App\Models\Challenge\ChallengeInviteCode;
use App\Models\User;

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

test('returns the correct challenge', function () {
    $challenge = Challenge::factory()->create();
    $inviteCode = ChallengeInviteCode::factory()->forChallenge($challenge)->create();
    $user = User::factory()->create();

    $action = new AcceptChallengeInviteCodeAction;
    $result = $action->accept(inviteCode: $inviteCode, user: $user);

    expect($result)->toBeInstanceOf(Challenge::class)
        ->and($result->id)->toBe($challenge->id);
});
