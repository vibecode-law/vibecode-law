<?php

use App\Models\Challenge\Challenge;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

test('avatar returns null when avatar_path is null', function () {
    $user = User::factory()->make(['avatar_path' => null]);

    expect($user->avatar)->toBeNull();
});

test('avatar returns storage url when image transform base url is not set', function () {
    Storage::fake('public');
    Config::set('services.image-transform.base_url', null);

    $user = User::factory()->make(['avatar_path' => 'avatars/test-avatar.jpg']);

    expect($user->avatar)->toBe(Storage::disk('public')->url('avatars/test-avatar.jpg'));
});

test('avatar returns image transform url when image transform base url is set', function () {
    Config::set('services.image-transform.base_url', 'https://images.example.com');

    $user = User::factory()->make(['avatar_path' => 'avatars/test-avatar.jpg']);

    expect($user->avatar)->toBe('https://images.example.com/avatars/test-avatar.jpg');
});

describe('hostedChallenges relationship', function () {
    test('user can have many hosted challenges', function () {
        $user = User::factory()->create();
        Challenge::factory()->count(3)->forUser($user)->create();

        expect($user->hostedChallenges)->toHaveCount(3);
        expect($user->hostedChallenges->first())->toBeInstanceOf(Challenge::class);
    });

    test('user with no hosted challenges returns empty collection', function () {
        $user = User::factory()->create();

        expect($user->hostedChallenges)->toBeEmpty();
    });
});
