<?php

use App\Models\Challenge\Challenge;
use App\Models\Challenge\SubChallenge;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

describe('auth', function () {
    test('requires authentication', function () {
        $challenge = Challenge::factory()->create();

        get(route('staff.challenges.sub-challenges.index', $challenge))
            ->assertRedirect(route('login'));
    });

    test('allows admin to view sub-challenges', function () {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();

        actingAs($admin)
            ->get(route('staff.challenges.sub-challenges.index', $challenge))
            ->assertOk();
    });

    test('does not allow regular users to view sub-challenges', function () {
        /** @var User */
        $user = User::factory()->create();
        $challenge = Challenge::factory()->create();

        actingAs($user)
            ->get(route('staff.challenges.sub-challenges.index', $challenge))
            ->assertForbidden();
    });
});

describe('data', function () {
    test('returns sub-challenges with correct data structure', function () {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();
        $subChallenge = SubChallenge::factory()->forChallenge($challenge)->create(['order' => 1]);

        actingAs($admin)
            ->get(route('staff.challenges.sub-challenges.index', $challenge))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('staff-area/challenges/sub-challenges/index')
                ->where('challenge.id', $challenge->id)
                ->where('challenge.slug', $challenge->slug)
                ->where('challenge.title', $challenge->title)
                ->has('subChallenges', 1, fn (AssertableInertia $sub) => $sub
                    ->where('id', $subChallenge->id)
                    ->where('name', $subChallenge->name)
                    ->where('tagline', $subChallenge->tagline)
                    ->where('description', $subChallenge->description)
                    ->where('order', $subChallenge->order)
                )
            );
    });

    test('orders sub-challenges by order', function () {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();

        $second = SubChallenge::factory()->forChallenge($challenge)->create(['order' => 2]);
        $first = SubChallenge::factory()->forChallenge($challenge)->create(['order' => 1]);

        actingAs($admin)
            ->get(route('staff.challenges.sub-challenges.index', $challenge))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('subChallenges.0.id', $first->id)
                ->where('subChallenges.1.id', $second->id)
            );
    });
});
