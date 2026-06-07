<?php

use App\Models\Challenge\Challenge;
use App\Models\Challenge\SubChallenge;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

describe('auth', function () {
    test('requires authentication', function () {
        $challenge = Challenge::factory()->create();
        $subChallenge = SubChallenge::factory()->forChallenge($challenge)->create();

        post(route('staff.challenges.sub-challenges.reorder', $challenge), [
            'items' => [['id' => $subChallenge->id, 'order' => 0]],
        ])->assertRedirect(route('login'));
    });

    test('does not allow regular users to reorder sub-challenges', function () {
        /** @var User */
        $user = User::factory()->create();
        $challenge = Challenge::factory()->create();
        $subChallenge = SubChallenge::factory()->forChallenge($challenge)->create();

        actingAs($user)
            ->post(route('staff.challenges.sub-challenges.reorder', $challenge), [
                'items' => [['id' => $subChallenge->id, 'order' => 0]],
            ])
            ->assertForbidden();
    });
});

describe('reorder', function () {
    test('updates the order of sub-challenges', function () {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();
        $first = SubChallenge::factory()->forChallenge($challenge)->create(['order' => 0]);
        $second = SubChallenge::factory()->forChallenge($challenge)->create(['order' => 1]);

        actingAs($admin)
            ->post(route('staff.challenges.sub-challenges.reorder', $challenge), [
                'items' => [
                    ['id' => $second->id, 'order' => 0],
                    ['id' => $first->id, 'order' => 1],
                ],
            ])
            ->assertRedirect();

        expect($first->refresh()->order)->toBe(1)
            ->and($second->refresh()->order)->toBe(0);
    });

    test('does not reorder sub-challenges from another challenge', function () {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();
        $otherChallenge = Challenge::factory()->create();
        $otherSub = SubChallenge::factory()->forChallenge($otherChallenge)->create(['order' => 3]);

        actingAs($admin)
            ->post(route('staff.challenges.sub-challenges.reorder', $challenge), [
                'items' => [['id' => $otherSub->id, 'order' => 0]],
            ])
            ->assertRedirect();

        expect($otherSub->refresh()->order)->toBe(3);
    });

    test('validates items', function ($data, $invalid) {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();

        actingAs($admin)
            ->post(route('staff.challenges.sub-challenges.reorder', $challenge), $data)
            ->assertSessionHasErrors($invalid);
    })->with([
        'missing items' => [
            [],
            ['items'],
        ],
        'non-existent id' => [
            ['items' => [['id' => 99999, 'order' => 0]]],
            ['items.0.id'],
        ],
        'missing order' => [
            ['items' => [['id' => 1]]],
            ['items.0.order'],
        ],
    ]);
});
