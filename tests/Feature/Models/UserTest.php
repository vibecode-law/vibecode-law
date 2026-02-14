<?php

use App\Models\Challenge\Challenge;
use App\Models\Course\Course;
use App\Models\Course\Lesson;
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

describe('courses relationship', function () {
    test('user belongs to many courses', function () {
        $user = User::factory()->create();
        $courses = Course::factory()->count(3)->create();

        $user->courses()->attach($courses);

        expect($user->courses)->toHaveCount(3)
            ->each->toBeInstanceOf(Course::class);
    });

    test('pivot timestamps are accessible', function () {
        $user = User::factory()->create();
        $course = Course::factory()->create();

        $user->courses()->attach($course, [
            'viewed_at' => now(),
            'started_at' => null,
            'completed_at' => now(),
        ]);

        $pivot = $user->courses->first()->pivot;

        expect($pivot->viewed_at)->not->toBeNull()
            ->and($pivot->started_at)->toBeNull()
            ->and($pivot->completed_at)->not->toBeNull();
    });
});

describe('lessons relationship', function () {
    test('user belongs to many lessons', function () {
        $user = User::factory()->create();
        $lessons = Lesson::factory()->count(3)->create();

        $user->lessons()->attach($lessons);

        expect($user->lessons)->toHaveCount(3)
            ->each->toBeInstanceOf(Lesson::class);
    });

    test('pivot timestamps are accessible', function () {
        $user = User::factory()->create();
        $lesson = Lesson::factory()->create();

        $user->lessons()->attach($lesson, [
            'viewed_at' => now(),
            'started_at' => null,
            'completed_at' => now(),
        ]);

        $pivot = $user->lessons->first()->pivot;

        expect($pivot->viewed_at)->not->toBeNull()
            ->and($pivot->started_at)->toBeNull()
            ->and($pivot->completed_at)->not->toBeNull();
    });
});
