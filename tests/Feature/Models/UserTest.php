<?php

use App\Enums\ChallengeVisibility;
use App\Enums\InviteCodeScope;
use App\Models\Challenge\Challenge;
use App\Models\Challenge\ChallengeInviteCode;
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

describe('acceptedChallengeInviteCodes relationship', function () {
    test('user can have accepted invite codes', function () {
        $user = User::factory()->create();
        $inviteCodes = ChallengeInviteCode::factory()->count(2)->create();

        $user->acceptedChallengeInviteCodes()->attach($inviteCodes);

        expect($user->acceptedChallengeInviteCodes)->toHaveCount(2)
            ->each->toBeInstanceOf(ChallengeInviteCode::class);
    });

    test('user with no accepted invite codes returns empty collection', function () {
        $user = User::factory()->create();

        expect($user->acceptedChallengeInviteCodes)->toBeEmpty();
    });
});

describe('hasChallengeAccess', function () {
    test('returns true for Public challenges regardless of invite', function () {
        $user = User::factory()->create();
        $challenge = Challenge::factory()->create(['visibility' => ChallengeVisibility::Public]);

        expect($user->hasChallengeAccess($challenge, InviteCodeScope::View))->toBeTrue()
            ->and($user->hasChallengeAccess($challenge, InviteCodeScope::ViewAndSubmit))->toBeTrue();
    });

    test('returns true when user has accepted code with sufficient scope', function () {
        $user = User::factory()->create();
        $challenge = Challenge::factory()->inviteToSubmit()->create();
        $inviteCode = ChallengeInviteCode::factory()->forChallenge($challenge)->create([
            'scope' => InviteCodeScope::ViewAndSubmit,
        ]);

        $user->acceptedChallengeInviteCodes()->attach($inviteCode);

        expect($user->hasChallengeAccess($challenge, InviteCodeScope::View))->toBeTrue()
            ->and($user->hasChallengeAccess($challenge, InviteCodeScope::ViewAndSubmit))->toBeTrue();
    });

    test('returns false when user has no accepted code', function () {
        $user = User::factory()->create();
        $challenge = Challenge::factory()->inviteToViewAndSubmit()->create();

        expect($user->hasChallengeAccess($challenge, InviteCodeScope::View))->toBeFalse();
    });

    test('returns true when user has view-only code and only View is required', function () {
        $user = User::factory()->create();
        $challenge = Challenge::factory()->inviteToViewAndSubmit()->create();
        $inviteCode = ChallengeInviteCode::factory()->forChallenge($challenge)->viewOnly()->create();

        $user->acceptedChallengeInviteCodes()->attach($inviteCode);

        expect($user->hasChallengeAccess($challenge, InviteCodeScope::View))->toBeTrue();
    });

    test('returns false when user has view-only code but needs ViewAndSubmit', function () {
        $user = User::factory()->create();
        $challenge = Challenge::factory()->inviteToSubmit()->create();
        $inviteCode = ChallengeInviteCode::factory()->forChallenge($challenge)->viewOnly()->create();

        $user->acceptedChallengeInviteCodes()->attach($inviteCode);

        expect($user->hasChallengeAccess($challenge, InviteCodeScope::ViewAndSubmit))->toBeFalse();
    });
});
