<?php

use App\Models\Challenge\Challenge;
use App\Models\Challenge\ChallengePartnerLogo;
use App\Models\Challenge\SubChallenge;
use App\Models\Showcase\Showcase;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\get;

test('renders the live view for an enabled challenge', function () {
    $challenge = Challenge::factory()->liveView()->create();

    get(route('inspiration.challenges.live', $challenge))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('challenge/public/live')
            ->has('challenge')
            ->has('showcases')
        );
});

test('returns 404 when the live view is disabled', function () {
    $challenge = Challenge::factory()->active()->create();

    get(route('inspiration.challenges.live', $challenge))
        ->assertNotFound();
});

test('returns 404 when the challenge is inactive', function () {
    $challenge = Challenge::factory()->create(['live_view_enabled' => true]);

    get(route('inspiration.challenges.live', $challenge))
        ->assertNotFound();
});

test('returns 404 when a secret key is required but missing', function () {
    $challenge = Challenge::factory()->liveView()->create([
        'live_view_access_token' => 'super-secret',
    ]);

    get(route('inspiration.challenges.live', $challenge))
        ->assertNotFound();
});

test('returns 404 when the secret key is wrong', function () {
    $challenge = Challenge::factory()->liveView()->create([
        'live_view_access_token' => 'super-secret',
    ]);

    get(route('inspiration.challenges.live', ['challenge' => $challenge, 'key' => 'nope']))
        ->assertNotFound();
});

test('renders when the correct secret key is provided', function () {
    $challenge = Challenge::factory()->liveView()->create([
        'live_view_access_token' => 'super-secret',
    ]);

    get(route('inspiration.challenges.live', ['challenge' => $challenge, 'key' => 'super-secret']))
        ->assertOk();
});

test('returns the correct challenge data structure', function () {
    $challenge = Challenge::factory()->liveView()->create([
        'live_view_heading' => 'LIVE LEADERBOARD',
        'live_view_subheading' => 'Vote now',
    ]);
    $subChallenge = SubChallenge::factory()->forChallenge($challenge)->create();
    $logo = ChallengePartnerLogo::factory()->create([
        'challenge_id' => $challenge->id,
        'href' => 'https://partner.example',
        'invert_in_dark' => true,
    ]);

    get(route('inspiration.challenges.live', $challenge))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('challenge', fn (AssertableInertia $c) => $c
                ->where('id', $challenge->id)
                ->where('slug', $challenge->slug)
                ->where('title', $challenge->title)
                ->where('tagline', $challenge->tagline)
                ->where('live_view_heading', 'LIVE LEADERBOARD')
                ->where('live_view_subheading', 'Vote now')
                ->has('sub_challenges', 1)
                ->where('sub_challenges.0.id', $subChallenge->id)
                ->has('partner_logos', 1)
                ->has('partner_logos.0', fn (AssertableInertia $l) => $l
                    ->where('id', $logo->id)
                    ->where('url', $logo->url)
                    ->where('filename', $logo->filename)
                    ->where('href', 'https://partner.example')
                    ->where('order', $logo->order)
                    ->where('invert_in_dark', true)
                )
            )
        );
});

test('does not expose the access token to the page', function () {
    $challenge = Challenge::factory()->liveView()->create([
        'live_view_access_token' => 'super-secret',
    ]);

    get(route('inspiration.challenges.live', ['challenge' => $challenge, 'key' => 'super-secret']))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('challenge', fn (AssertableInertia $c) => $c
                ->missing('live_view_access_token')
                ->etc()
            )
        );
});

test('returns showcases ordered by upvotes descending', function () {
    $challenge = Challenge::factory()->liveView()->create();
    $upvoters = User::factory()->count(3)->create();

    $most = Showcase::factory()->approved()->create();
    $most->upvoters()->attach($upvoters->pluck('id'));

    $least = Showcase::factory()->approved()->create();

    $challenge->showcases()->attach([$most->id, $least->id]);

    get(route('inspiration.challenges.live', $challenge))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('showcases', 2)
            ->where('showcases.0.id', $most->id)
            ->where('showcases.1.id', $least->id)
        );
});

test('returns the correct showcase data structure', function () {
    $challenge = Challenge::factory()->liveView()->create();
    $subChallenge = SubChallenge::factory()->forChallenge($challenge)->create();

    $showcase = Showcase::factory()->approved()->create([
        'title' => 'Test Showcase',
        'slug' => 'test-showcase',
        'tagline' => 'Test tagline',
    ]);
    $challenge->showcases()->attach($showcase, ['sub_challenge_id' => $subChallenge->id]);

    get(route('inspiration.challenges.live', $challenge))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('showcases.0', fn (AssertableInertia $s) => $s
                ->where('id', $showcase->id)
                ->where('slug', 'test-showcase')
                ->where('title', 'Test Showcase')
                ->where('tagline', 'Test tagline')
                ->has('thumbnail_url')
                ->has('thumbnail_rect_string')
                ->where('upvotes_count', 0)
                ->has('view_count')
                ->has('user')
                ->where('sub_challenge_id', $subChallenge->id)
                ->missing('has_upvoted')
            )
        );
});

test('only includes publicly visible showcases', function () {
    $challenge = Challenge::factory()->liveView()->create();

    $approved = Showcase::factory()->approved()->create();
    $draft = Showcase::factory()->draft()->create();

    $challenge->showcases()->attach([$approved->id, $draft->id]);

    get(route('inspiration.challenges.live', $challenge))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('showcases', 1)
            ->where('showcases.0.id', $approved->id)
        );
});
