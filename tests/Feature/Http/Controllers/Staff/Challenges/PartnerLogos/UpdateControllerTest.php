<?php

use App\Models\Challenge\Challenge;
use App\Models\Challenge\ChallengePartnerLogo;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\patch;

describe('auth', function () {
    test('requires authentication', function () {
        $challenge = Challenge::factory()->create();
        $logo = ChallengePartnerLogo::factory()->create(['challenge_id' => $challenge->id]);

        patch(route('staff.challenges.partner-logos.update', [$challenge, $logo]), [
            'invert_in_dark' => true,
        ])->assertRedirect(route('login'));
    });

    test('does not allow regular users', function () {
        /** @var User $user */
        $user = User::factory()->create();
        $challenge = Challenge::factory()->create();
        $logo = ChallengePartnerLogo::factory()->create(['challenge_id' => $challenge->id]);

        actingAs($user)
            ->patch(route('staff.challenges.partner-logos.update', [$challenge, $logo]), [
                'invert_in_dark' => true,
            ])
            ->assertForbidden();
    });
});

describe('update', function () {
    test('updates href and invert flag', function () {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();
        $logo = ChallengePartnerLogo::factory()->create([
            'challenge_id' => $challenge->id,
            'href' => null,
            'invert_in_dark' => false,
        ]);

        actingAs($admin)
            ->patch(route('staff.challenges.partner-logos.update', [$challenge, $logo]), [
                'href' => 'https://partner.example',
                'invert_in_dark' => true,
            ])
            ->assertRedirect()
            ->assertSessionHas('flash.message', [
                'message' => 'Partner logo updated.',
                'type' => 'success',
            ]);

        $logo->refresh();

        expect($logo->href)->toBe('https://partner.example')
            ->and($logo->invert_in_dark)->toBeTrue();
    });

    test('clears the href when blank', function () {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();
        $logo = ChallengePartnerLogo::factory()->create([
            'challenge_id' => $challenge->id,
            'href' => 'https://partner.example',
        ]);

        actingAs($admin)
            ->patch(route('staff.challenges.partner-logos.update', [$challenge, $logo]), [
                'href' => '',
                'invert_in_dark' => false,
            ])
            ->assertRedirect();

        expect($logo->refresh()->href)->toBeNull();
    });

    test('validates the data', function (array $data) {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();
        $logo = ChallengePartnerLogo::factory()->create(['challenge_id' => $challenge->id]);

        actingAs($admin)
            ->patch(route('staff.challenges.partner-logos.update', [$challenge, $logo]), $data)
            ->assertSessionHasErrors();
    })->with([
        'invalid url' => [['href' => 'not-a-url', 'invert_in_dark' => false]],
        'missing invert' => [['href' => null]],
    ]);
});
