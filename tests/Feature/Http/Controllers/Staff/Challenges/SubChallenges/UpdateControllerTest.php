<?php

use App\Models\Challenge\Challenge;
use App\Models\Challenge\SubChallenge;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\patch;

describe('auth', function () {
    test('requires authentication', function () {
        $challenge = Challenge::factory()->create();
        $subChallenge = SubChallenge::factory()->forChallenge($challenge)->create();

        patch(route('staff.challenges.sub-challenges.update', [$challenge, $subChallenge]), [
            'name' => 'Updated',
            'tagline' => 'Updated tagline',
        ])->assertRedirect(route('login'));
    });

    test('does not allow regular users to update a sub-challenge', function () {
        /** @var User */
        $user = User::factory()->create();
        $challenge = Challenge::factory()->create();
        $subChallenge = SubChallenge::factory()->forChallenge($challenge)->create();

        actingAs($user)
            ->patch(route('staff.challenges.sub-challenges.update', [$challenge, $subChallenge]), [
                'name' => 'Updated',
                'tagline' => 'Updated tagline',
            ])
            ->assertForbidden();
    });
});

describe('update', function () {
    test('updates the sub-challenge with the correct data', function () {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();
        $subChallenge = SubChallenge::factory()->forChallenge($challenge)->create();

        actingAs($admin)
            ->patch(route('staff.challenges.sub-challenges.update', [$challenge, $subChallenge]), [
                'name' => 'Updated name',
                'tagline' => 'Updated tagline',
                'description' => 'Updated description',
            ])
            ->assertRedirect()
            ->assertSessionHas('flash.message', [
                'message' => 'Sub-challenge updated successfully.',
                'type' => 'success',
            ]);

        $subChallenge->refresh();

        expect($subChallenge->name)->toBe('Updated name')
            ->and($subChallenge->tagline)->toBe('Updated tagline')
            ->and($subChallenge->description)->toBe('Updated description');
    });

    test('scopes the sub-challenge to the challenge', function () {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();
        $otherChallenge = Challenge::factory()->create();
        $subChallenge = SubChallenge::factory()->forChallenge($challenge)->create();

        actingAs($admin)
            ->patch(route('staff.challenges.sub-challenges.update', [$otherChallenge, $subChallenge]), [
                'name' => 'Updated name',
                'tagline' => 'Updated tagline',
            ])
            ->assertNotFound();
    });
});

describe('validation', function () {
    test('validates required and invalid fields', function ($data, $invalid) {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();
        $subChallenge = SubChallenge::factory()->forChallenge($challenge)->create();

        actingAs($admin)
            ->patch(route('staff.challenges.sub-challenges.update', [$challenge, $subChallenge]), $data)
            ->assertSessionHasErrors($invalid);
    })->with([
        'missing name' => [
            ['tagline' => 'A tagline'],
            ['name'],
        ],
        'missing tagline' => [
            ['name' => 'A name'],
            ['tagline'],
        ],
    ]);
});
