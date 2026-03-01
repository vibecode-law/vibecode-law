<?php

use App\Enums\ChallengeVisibility;
use App\Models\Challenge\Challenge;
use App\Models\Challenge\ChallengeInviteCode;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

describe('auth', function () {
    test('requires authentication', function () {
        $response = get(route('showcase.manage.create'));

        $response->assertRedirect(route('login'));
    });

    test('allows authenticated user', function () {
        /** @var User */
        $user = User::factory()->create();

        actingAs($user);

        $response = get(route('showcase.manage.create'));

        $response->assertOk();
    });

    test('requires email verification', function () {
        /** @var User */
        $user = User::factory()->unverified()->create();

        actingAs($user);

        $response = get(route('showcase.manage.create'));

        $response->assertRedirect(route('verification.notice'));
    });
});

describe('data structure', function () {
    test('returns correct component', function () {
        /** @var User */
        $user = User::factory()->create();

        actingAs($user);

        $response = get(route('showcase.manage.create'));

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('showcase/user/create')
        );
    });
});

describe('challenge warning', function () {
    test('returns warning when user lacks submit access to invite-to-submit challenge', function () {
        /** @var User */
        $user = User::factory()->create();
        $challenge = Challenge::factory()->active()->inviteToSubmit()->create();

        actingAs($user);

        get(route('showcase.manage.create', ['challenge' => $challenge->slug]))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('showcase/user/create')
                ->where('challenge', null)
                ->where('challengeWarning', "You don't have permission to submit to the {$challenge->title} challenge. An invite code with submit access is required.")
            );
    });

    test('returns warning when user has only view access to invite-to-submit challenge', function () {
        /** @var User */
        $user = User::factory()->create();
        $challenge = Challenge::factory()->active()->inviteToSubmit()->create();
        $inviteCode = ChallengeInviteCode::factory()->forChallenge($challenge)->viewOnly()->create();
        $user->acceptedChallengeInviteCodes()->attach($inviteCode);

        actingAs($user);

        get(route('showcase.manage.create', ['challenge' => $challenge->slug]))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('showcase/user/create')
                ->where('challenge', null)
                ->where('challengeWarning', "You don't have permission to submit to the {$challenge->title} challenge. An invite code with submit access is required.")
            );
    });

    test('returns no warning when user has submit access', function () {
        /** @var User */
        $user = User::factory()->create();
        $challenge = Challenge::factory()->active()->inviteToSubmit()->create();
        $inviteCode = ChallengeInviteCode::factory()->forChallenge($challenge)->create();
        $user->acceptedChallengeInviteCodes()->attach($inviteCode);

        actingAs($user);

        get(route('showcase.manage.create', ['challenge' => $challenge->slug]))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('showcase/user/create')
                ->where('challenge.title', $challenge->title)
                ->where('challengeWarning', null)
            );
    });

    test('returns no warning for public challenge', function () {
        /** @var User */
        $user = User::factory()->create();
        $challenge = Challenge::factory()->active()->create(['visibility' => ChallengeVisibility::Public]);

        actingAs($user);

        get(route('showcase.manage.create', ['challenge' => $challenge->slug]))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('showcase/user/create')
                ->where('challenge.title', $challenge->title)
                ->where('challengeWarning', null)
            );
    });

    test('returns no warning when no challenge query param', function () {
        /** @var User */
        $user = User::factory()->create();

        actingAs($user);

        get(route('showcase.manage.create'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('showcase/user/create')
                ->where('challenge', null)
                ->where('challengeWarning', null)
            );
    });
});
