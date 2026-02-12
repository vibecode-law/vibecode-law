<?php

use App\Models\Challenge\Challenge;
use App\Models\Challenge\ChallengeShowcase;
use App\Models\Showcase\Showcase;

test('pivot belongs to challenge', function () {
    $challenge = Challenge::factory()->create();
    $showcase = Showcase::factory()->create();

    $challenge->showcases()->attach($showcase);

    $pivot = ChallengeShowcase::query()
        ->where('challenge_id', $challenge->id)
        ->where('showcase_id', $showcase->id)
        ->first();

    expect($pivot->challenge)->toBeInstanceOf(Challenge::class);
    expect($pivot->challenge->id)->toBe($challenge->id);
});

test('pivot belongs to showcase', function () {
    $challenge = Challenge::factory()->create();
    $showcase = Showcase::factory()->create();

    $challenge->showcases()->attach($showcase);

    $pivot = ChallengeShowcase::query()
        ->where('challenge_id', $challenge->id)
        ->where('showcase_id', $showcase->id)
        ->first();

    expect($pivot->showcase)->toBeInstanceOf(Showcase::class);
    expect($pivot->showcase->id)->toBe($showcase->id);
});

test('pivot is deleted when challenge is deleted', function () {
    $challenge = Challenge::factory()->create();
    $showcase = Showcase::factory()->create();

    $challenge->showcases()->attach($showcase);

    expect(ChallengeShowcase::query()->count())->toBe(1);

    $challenge->delete();

    expect(ChallengeShowcase::query()->count())->toBe(0);
});

test('pivot is deleted when showcase is deleted', function () {
    $challenge = Challenge::factory()->create();
    $showcase = Showcase::factory()->create();

    $challenge->showcases()->attach($showcase);

    expect(ChallengeShowcase::query()->count())->toBe(1);

    $showcase->forceDelete();

    expect(ChallengeShowcase::query()->count())->toBe(0);
});
