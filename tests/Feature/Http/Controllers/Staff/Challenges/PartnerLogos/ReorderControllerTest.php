<?php

use App\Models\Challenge\Challenge;
use App\Models\Challenge\ChallengePartnerLogo;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

describe('auth', function () {
    test('requires authentication', function () {
        $challenge = Challenge::factory()->create();

        post(route('staff.challenges.partner-logos.reorder', $challenge), [
            'items' => [],
        ])->assertRedirect(route('login'));
    });

    test('does not allow regular users', function () {
        /** @var User $user */
        $user = User::factory()->create();
        $challenge = Challenge::factory()->create();
        $logo = ChallengePartnerLogo::factory()->create(['challenge_id' => $challenge->id]);

        actingAs($user)
            ->post(route('staff.challenges.partner-logos.reorder', $challenge), [
                'items' => [['id' => $logo->id, 'order' => 0]],
            ])
            ->assertForbidden();
    });
});

describe('reorder', function () {
    test('updates the order of logos', function () {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();
        $first = ChallengePartnerLogo::factory()->create(['challenge_id' => $challenge->id, 'order' => 1]);
        $second = ChallengePartnerLogo::factory()->create(['challenge_id' => $challenge->id, 'order' => 2]);

        actingAs($admin)
            ->post(route('staff.challenges.partner-logos.reorder', $challenge), [
                'items' => [
                    ['id' => $first->id, 'order' => 2],
                    ['id' => $second->id, 'order' => 1],
                ],
            ])
            ->assertRedirect();

        expect($first->refresh()->order)->toBe(2)
            ->and($second->refresh()->order)->toBe(1);
    });

    test('does not reorder logos from another challenge', function () {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();
        $otherChallenge = Challenge::factory()->create();
        $otherLogo = ChallengePartnerLogo::factory()->create([
            'challenge_id' => $otherChallenge->id,
            'order' => 1,
        ]);

        actingAs($admin)
            ->post(route('staff.challenges.partner-logos.reorder', $challenge), [
                'items' => [['id' => $otherLogo->id, 'order' => 9]],
            ])
            ->assertRedirect();

        expect($otherLogo->refresh()->order)->toBe(1);
    });
});
