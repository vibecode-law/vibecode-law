<?php

use App\Enums\ChallengeVisibility;
use App\Models\Challenge\Challenge;
use App\Models\Challenge\ChallengeInviteCode;
use App\Models\Challenge\SubChallenge;
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
                ->where('selectedChallengeId', null)
                ->where('challengeWarning', "You don't have permission to submit to the {$challenge->title} challenge. An invite code with submit access is required.")
                ->etc()
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
                ->where('selectedChallengeId', null)
                ->where('challengeWarning', "You don't have permission to submit to the {$challenge->title} challenge. An invite code with submit access is required.")
                ->etc()
            );
    });

    test('preselects challenge when user has submit access', function () {
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
                ->where('selectedChallengeId', $challenge->id)
                ->where('challengeWarning', null)
                ->etc()
            );
    });

    test('preselects public challenge', function () {
        /** @var User */
        $user = User::factory()->create();
        $challenge = Challenge::factory()->active()->create(['visibility' => ChallengeVisibility::Public]);

        actingAs($user);

        get(route('showcase.manage.create', ['challenge' => $challenge->slug]))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('showcase/user/create')
                ->where('selectedChallengeId', $challenge->id)
                ->where('challengeWarning', null)
                ->etc()
            );
    });

    test('returns warning when challenge has not started yet', function () {
        /** @var User */
        $user = User::factory()->create();
        $challenge = Challenge::factory()->upcoming()->create(['visibility' => ChallengeVisibility::Public]);

        actingAs($user);

        get(route('showcase.manage.create', ['challenge' => $challenge->slug]))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('showcase/user/create')
                ->where('selectedChallengeId', null)
                ->where('challengeWarning', "The {$challenge->title} challenge is not open for submissions yet.")
                ->etc()
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
                ->where('selectedChallengeId', null)
                ->where('challengeWarning', null)
                ->etc()
            );
    });
});

describe('available challenges', function () {
    test('includes open public challenges with their sub-challenges', function () {
        /** @var User */
        $user = User::factory()->create();
        $challenge = Challenge::factory()->ongoing()->create(['visibility' => ChallengeVisibility::Public]);
        $subChallenge = SubChallenge::factory()->forChallenge($challenge)->create();

        actingAs($user);

        get(route('showcase.manage.create'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('showcase/user/create')
                ->has('availableChallenges', 1, fn (AssertableInertia $item) => $item
                    ->where('id', $challenge->id)
                    ->where('title', $challenge->title)
                    ->where('slug', $challenge->slug)
                    ->has('sub_challenges', 1, fn (AssertableInertia $sub) => $sub
                        ->where('id', $subChallenge->id)
                        ->where('name', $subChallenge->name)
                        ->where('tagline', $subChallenge->tagline)
                        ->where('description', $subChallenge->description)
                        ->where('order', $subChallenge->order)
                    )
                )
                ->etc()
            );
    });

    test('excludes inactive, closed, and non-invited challenges', function () {
        /** @var User */
        $user = User::factory()->create();

        Challenge::factory()->create(['is_active' => false, 'visibility' => ChallengeVisibility::Public]);
        Challenge::factory()->ended()->create(['visibility' => ChallengeVisibility::Public]);
        Challenge::factory()->active()->inviteToSubmit()->create();

        actingAs($user);

        get(route('showcase.manage.create'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('showcase/user/create')
                ->where('availableChallenges', [])
                ->etc()
            );
    });

    test('includes invite-to-submit challenges the user can submit to', function () {
        /** @var User */
        $user = User::factory()->create();
        $challenge = Challenge::factory()->ongoing()->inviteToSubmit()->create();
        $inviteCode = ChallengeInviteCode::factory()->forChallenge($challenge)->create();
        $user->acceptedChallengeInviteCodes()->attach($inviteCode);

        actingAs($user);

        get(route('showcase.manage.create'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('showcase/user/create')
                ->has('availableChallenges', 1, fn (AssertableInertia $item) => $item
                    ->where('id', $challenge->id)
                    ->etc()
                )
                ->etc()
            );
    });
});
