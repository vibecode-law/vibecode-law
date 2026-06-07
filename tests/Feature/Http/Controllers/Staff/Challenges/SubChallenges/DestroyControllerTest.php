<?php

use App\Models\Challenge\Challenge;
use App\Models\Challenge\SubChallenge;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\delete;

describe('auth', function () {
    test('requires authentication', function () {
        $challenge = Challenge::factory()->create();
        $subChallenge = SubChallenge::factory()->forChallenge($challenge)->create();

        delete(route('staff.challenges.sub-challenges.destroy', [$challenge, $subChallenge]))
            ->assertRedirect(route('login'));
    });

    test('does not allow regular users to delete a sub-challenge', function () {
        /** @var User */
        $user = User::factory()->create();
        $challenge = Challenge::factory()->create();
        $subChallenge = SubChallenge::factory()->forChallenge($challenge)->create();

        actingAs($user)
            ->delete(route('staff.challenges.sub-challenges.destroy', [$challenge, $subChallenge]))
            ->assertForbidden();
    });
});

describe('destroy', function () {
    test('deletes the sub-challenge', function () {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();
        $subChallenge = SubChallenge::factory()->forChallenge($challenge)->create();

        actingAs($admin)
            ->delete(route('staff.challenges.sub-challenges.destroy', [$challenge, $subChallenge]))
            ->assertRedirect()
            ->assertSessionHas('flash.message', [
                'message' => 'Sub-challenge deleted.',
                'type' => 'success',
            ]);

        expect(SubChallenge::find($subChallenge->id))->toBeNull();
    });

    test('scopes the sub-challenge to the challenge', function () {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();
        $otherChallenge = Challenge::factory()->create();
        $subChallenge = SubChallenge::factory()->forChallenge($challenge)->create();

        actingAs($admin)
            ->delete(route('staff.challenges.sub-challenges.destroy', [$otherChallenge, $subChallenge]))
            ->assertNotFound();

        expect(SubChallenge::find($subChallenge->id))->not->toBeNull();
    });
});
