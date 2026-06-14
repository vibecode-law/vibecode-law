<?php

use App\Models\Challenge\Challenge;
use App\Models\Challenge\ChallengePartnerLogo;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

describe('auth', function () {
    test('requires authentication', function () {
        $challenge = Challenge::factory()->create();

        get(route('staff.challenges.partner-logos.index', $challenge))
            ->assertRedirect(route('login'));
    });

    test('does not allow regular users', function () {
        /** @var User $user */
        $user = User::factory()->create();
        $challenge = Challenge::factory()->create();

        actingAs($user)
            ->get(route('staff.challenges.partner-logos.index', $challenge))
            ->assertForbidden();
    });
});

describe('index', function () {
    test('renders the page with logos and challenge data', function () {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->liveView()->create([
            'live_view_access_token' => 'secret',
        ]);
        $logo = ChallengePartnerLogo::factory()->create([
            'challenge_id' => $challenge->id,
            'invert_in_dark' => true,
        ]);

        actingAs($admin)
            ->get(route('staff.challenges.partner-logos.index', $challenge))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('staff-area/challenges/partner-logos/index')
                ->has('challenge', fn (AssertableInertia $c) => $c
                    ->where('id', $challenge->id)
                    ->where('slug', $challenge->slug)
                    ->where('title', $challenge->title)
                    ->where('live_view_enabled', true)
                    ->where('live_view_access_token', 'secret')
                )
                ->has('partnerLogos', 1)
                ->has('partnerLogos.0', fn (AssertableInertia $l) => $l
                    ->where('id', $logo->id)
                    ->where('url', $logo->url)
                    ->where('filename', $logo->filename)
                    ->where('href', $logo->href)
                    ->where('order', $logo->order)
                    ->where('invert_in_dark', true)
                )
            );
    });
});
