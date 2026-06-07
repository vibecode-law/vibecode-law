<?php

use App\Models\Challenge\Challenge;
use App\Models\Challenge\SubChallenge;

test('belongs to a challenge', function () {
    $challenge = Challenge::factory()->create();
    $subChallenge = SubChallenge::factory()->forChallenge($challenge)->create();

    expect($subChallenge->challenge)->toBeInstanceOf(Challenge::class)
        ->and($subChallenge->challenge->id)->toBe($challenge->id);
});

test('a challenge has many sub-challenges ordered by order', function () {
    $challenge = Challenge::factory()->create();

    $second = SubChallenge::factory()->forChallenge($challenge)->create(['order' => 2]);
    $first = SubChallenge::factory()->forChallenge($challenge)->create(['order' => 1]);

    expect($challenge->subChallenges->pluck('id')->all())->toBe([$first->id, $second->id]);
});

test('hasSubChallenges reflects whether sub-challenges exist', function () {
    $challenge = Challenge::factory()->create();

    expect($challenge->hasSubChallenges())->toBeFalse();

    SubChallenge::factory()->forChallenge($challenge)->create();

    expect($challenge->fresh()->hasSubChallenges())->toBeTrue();
});

test('deleting a challenge deletes its sub-challenges', function () {
    $challenge = Challenge::factory()->create();
    $subChallenge = SubChallenge::factory()->forChallenge($challenge)->create();

    $challenge->delete();

    expect(SubChallenge::find($subChallenge->id))->toBeNull();
});
