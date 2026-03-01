<?php

use App\Models\Challenge\Challenge;
use App\Models\Challenge\ChallengeInviteCode;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

test('guest sees invite page without challenge details', function () {
    $challenge = Challenge::factory()->active()->inviteToViewAndSubmit()->create();
    $inviteCode = ChallengeInviteCode::factory()->forChallenge($challenge)->create();

    get(route('challenges.invite.accept', $inviteCode->code))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('challenge/invite', shouldExist: false)
            ->missing('challenge')
        );
});

test('guest has url.intended set in session', function () {
    $challenge = Challenge::factory()->active()->inviteToViewAndSubmit()->create();
    $inviteCode = ChallengeInviteCode::factory()->forChallenge($challenge)->create();

    get(route('challenges.invite.accept', $inviteCode->code))
        ->assertSessionHas('url.intended', route('challenges.invite.accept', $inviteCode->code));
});

test('authenticated user accepts code and redirects to challenge', function () {
    /** @var User */
    $user = User::factory()->create();
    $challenge = Challenge::factory()->active()->inviteToViewAndSubmit()->create();
    $inviteCode = ChallengeInviteCode::factory()->forChallenge($challenge)->create();

    actingAs($user)
        ->get(route('challenges.invite.accept', $inviteCode->code))
        ->assertRedirect(route('inspiration.challenges.show', $challenge));

    expect($inviteCode->users()->where('user_id', $user->id)->exists())->toBeTrue();
});

test('accepting same code twice is idempotent', function () {
    /** @var User */
    $user = User::factory()->create();
    $challenge = Challenge::factory()->active()->inviteToViewAndSubmit()->create();
    $inviteCode = ChallengeInviteCode::factory()->forChallenge($challenge)->create();

    actingAs($user);

    get(route('challenges.invite.accept', $inviteCode->code))
        ->assertRedirect();

    get(route('challenges.invite.accept', $inviteCode->code))
        ->assertRedirect();

    expect($inviteCode->users()->where('user_id', $user->id)->count())->toBe(1);
});

test('returns 404 for non-existent code', function () {
    get(route('challenges.invite.accept', 'nonexistent-code'))
        ->assertNotFound();
});

test('returns 404 for disabled invite code', function () {
    $challenge = Challenge::factory()->active()->inviteToViewAndSubmit()->create();
    $inviteCode = ChallengeInviteCode::factory()->forChallenge($challenge)->disabled()->create();

    get(route('challenges.invite.accept', $inviteCode->code))
        ->assertNotFound();
});

test('returns 404 for invite code on inactive challenge', function () {
    $challenge = Challenge::factory()->inviteToViewAndSubmit()->create();
    $inviteCode = ChallengeInviteCode::factory()->forChallenge($challenge)->create();

    get(route('challenges.invite.accept', $inviteCode->code))
        ->assertNotFound();
});
