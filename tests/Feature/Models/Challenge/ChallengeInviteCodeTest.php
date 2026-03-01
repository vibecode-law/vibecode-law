<?php

use App\Enums\InviteCodeScope;
use App\Models\Challenge\Challenge;
use App\Models\Challenge\ChallengeInviteCode;
use App\Models\Challenge\ChallengeInviteCodeUser;
use App\Models\User;

describe('challenge relationship', function () {
    test('invite code belongs to a challenge', function () {
        $challenge = Challenge::factory()->create();
        $inviteCode = ChallengeInviteCode::factory()->forChallenge($challenge)->create();

        expect($inviteCode->challenge)->toBeInstanceOf(Challenge::class)
            ->and($inviteCode->challenge->id)->toBe($challenge->id);
    });
});

describe('users relationship', function () {
    test('invite code can have many users', function () {
        $inviteCode = ChallengeInviteCode::factory()->create();
        $users = User::factory()->count(3)->create();

        $inviteCode->users()->attach($users);

        expect($inviteCode->users)->toHaveCount(3)
            ->each->toBeInstanceOf(User::class);
    });

    test('users relationship uses ChallengeInviteCodeUser pivot model', function () {
        $inviteCode = ChallengeInviteCode::factory()->create();
        $user = User::factory()->create();

        $inviteCode->users()->attach($user);

        expect($inviteCode->users->first()->pivot)->toBeInstanceOf(ChallengeInviteCodeUser::class);
    });

    test('users relationship includes timestamps on pivot', function () {
        $inviteCode = ChallengeInviteCode::factory()->create();
        $user = User::factory()->create();

        $inviteCode->users()->attach($user);

        $pivot = $inviteCode->users->first()->pivot;

        expect($pivot->created_at)->not->toBeNull()
            ->and($pivot->updated_at)->not->toBeNull();
    });
});

describe('casts', function () {
    test('scope is cast to InviteCodeScope enum', function () {
        $inviteCode = ChallengeInviteCode::factory()->create([
            'scope' => InviteCodeScope::ViewAndSubmit,
        ]);

        expect($inviteCode->scope)->toBeInstanceOf(InviteCodeScope::class)
            ->and($inviteCode->scope)->toBe(InviteCodeScope::ViewAndSubmit);
    });

    test('is_active is cast to boolean', function () {
        $inviteCode = ChallengeInviteCode::factory()->create([
            'is_active' => true,
        ]);

        expect($inviteCode->is_active)->toBeBool()
            ->and($inviteCode->is_active)->toBeTrue();
    });
});

describe('factory states', function () {
    test('viewOnly state sets scope to View', function () {
        $inviteCode = ChallengeInviteCode::factory()->viewOnly()->create();

        expect($inviteCode->scope)->toBe(InviteCodeScope::View);
    });

    test('disabled state sets is_active to false', function () {
        $inviteCode = ChallengeInviteCode::factory()->disabled()->create();

        expect($inviteCode->is_active)->toBeFalse();
    });
});
