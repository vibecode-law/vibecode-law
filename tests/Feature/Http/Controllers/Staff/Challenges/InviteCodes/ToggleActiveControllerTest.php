<?php

use App\Models\Challenge\Challenge;
use App\Models\Challenge\ChallengeInviteCode;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

describe('auth', function () {
    test('requires authentication', function () {
        $challenge = Challenge::factory()->create();
        $inviteCode = ChallengeInviteCode::factory()->forChallenge($challenge)->create();

        post(route('staff.challenges.invite-codes.toggle', [$challenge, $inviteCode]))
            ->assertRedirect(route('login'));
    });

    test('allows admin to toggle invite code', function () {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();
        $inviteCode = ChallengeInviteCode::factory()->forChallenge($challenge)->create();

        actingAs($admin)
            ->post(route('staff.challenges.invite-codes.toggle', [$challenge, $inviteCode]))
            ->assertRedirect();
    });

    test('does not allow regular users to toggle invite codes', function () {
        /** @var User */
        $user = User::factory()->create();
        $challenge = Challenge::factory()->create();
        $inviteCode = ChallengeInviteCode::factory()->forChallenge($challenge)->create();

        actingAs($user)
            ->post(route('staff.challenges.invite-codes.toggle', [$challenge, $inviteCode]))
            ->assertForbidden();
    });
});

describe('toggle', function () {
    test('toggles active invite code to disabled', function () {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();
        $inviteCode = ChallengeInviteCode::factory()->forChallenge($challenge)->create([
            'is_active' => true,
        ]);

        actingAs($admin)
            ->post(route('staff.challenges.invite-codes.toggle', [$challenge, $inviteCode]))
            ->assertRedirect()
            ->assertSessionHas('flash.message', [
                'message' => 'Invite code disabled.',
                'type' => 'success',
            ]);

        expect($inviteCode->refresh()->is_active)->toBeFalse();
    });

    test('toggles disabled invite code to active', function () {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();
        $inviteCode = ChallengeInviteCode::factory()->forChallenge($challenge)->disabled()->create();

        actingAs($admin)
            ->post(route('staff.challenges.invite-codes.toggle', [$challenge, $inviteCode]))
            ->assertRedirect()
            ->assertSessionHas('flash.message', [
                'message' => 'Invite code enabled.',
                'type' => 'success',
            ]);

        expect($inviteCode->refresh()->is_active)->toBeTrue();
    });
});
