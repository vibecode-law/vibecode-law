<?php

use App\Models\Challenge\Challenge;
use App\Models\Challenge\SubChallenge;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

describe('auth', function () {
    test('requires authentication', function () {
        $challenge = Challenge::factory()->create();

        post(route('staff.challenges.sub-challenges.store', $challenge), [
            'name' => 'Best Solo Project',
            'tagline' => 'A great sub-challenge',
        ])->assertRedirect(route('login'));
    });

    test('allows admin to create a sub-challenge', function () {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();

        actingAs($admin)
            ->post(route('staff.challenges.sub-challenges.store', $challenge), [
                'name' => 'Best Solo Project',
                'tagline' => 'A great sub-challenge',
            ])
            ->assertRedirect();
    });

    test('does not allow regular users to create a sub-challenge', function () {
        /** @var User */
        $user = User::factory()->create();
        $challenge = Challenge::factory()->create();

        actingAs($user)
            ->post(route('staff.challenges.sub-challenges.store', $challenge), [
                'name' => 'Best Solo Project',
                'tagline' => 'A great sub-challenge',
            ])
            ->assertForbidden();
    });
});

describe('store', function () {
    test('creates a sub-challenge with the correct data', function () {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();

        actingAs($admin)
            ->post(route('staff.challenges.sub-challenges.store', $challenge), [
                'name' => 'Best Solo Project',
                'tagline' => 'For one-person teams',
                'description' => 'A longer description',
            ])
            ->assertRedirect()
            ->assertSessionHas('flash.message', [
                'message' => 'Sub-challenge created successfully.',
                'type' => 'success',
            ]);

        $subChallenge = SubChallenge::query()
            ->where('challenge_id', $challenge->id)
            ->firstOrFail();

        expect($subChallenge->name)->toBe('Best Solo Project')
            ->and($subChallenge->tagline)->toBe('For one-person teams')
            ->and($subChallenge->description)->toBe('A longer description')
            ->and($subChallenge->order)->toBe(1);
    });

    test('assigns an incrementing order', function () {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();
        SubChallenge::factory()->forChallenge($challenge)->create(['order' => 5]);

        actingAs($admin)
            ->post(route('staff.challenges.sub-challenges.store', $challenge), [
                'name' => 'Second',
                'tagline' => 'The second sub-challenge',
            ]);

        $subChallenge = SubChallenge::query()->where('name', 'Second')->firstOrFail();

        expect($subChallenge->order)->toBe(6);
    });
});

describe('validation', function () {
    test('validates required and invalid fields', function ($data, $invalid) {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();

        actingAs($admin)
            ->post(route('staff.challenges.sub-challenges.store', $challenge), $data)
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
        'name too long' => [
            ['name' => str_repeat('a', 61), 'tagline' => 'A tagline'],
            ['name'],
        ],
        'tagline too long' => [
            ['name' => 'A name', 'tagline' => str_repeat('a', 256)],
            ['tagline'],
        ],
    ]);
});
