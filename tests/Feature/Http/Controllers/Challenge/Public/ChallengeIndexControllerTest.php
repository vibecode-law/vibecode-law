<?php

use App\Models\Challenge\Challenge;
use App\Models\Organisation\Organisation;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\get;

test('index returns active challenges', function () {
    Challenge::factory()->active()->count(3)->create();
    Challenge::factory()->create(); // Inactive, should be excluded

    get(route('inspiration.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('challenge/public/index')
            ->has('activeChallenges', 3)
        );
});

test('index returns featured challenges', function () {
    Challenge::factory()->active()->featured()->count(2)->create();
    Challenge::factory()->active()->create(); // Not featured

    get(route('inspiration.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('featuredChallenges', 2)
            ->has('activeChallenges', 1) // Only non-featured
        );
});

test('index excludes inactive challenges', function () {
    Challenge::factory()->active()->create();
    Challenge::factory()->count(3)->create(); // Inactive

    get(route('inspiration.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('activeChallenges', 1)
            ->has('featuredChallenges', 0)
        );
});

test('index orders challenges by total upvotes descending', function () {
    $lowUpvotes = Challenge::factory()->active()->create();
    $highUpvotes = Challenge::factory()->active()->create();
    $midUpvotes = Challenge::factory()->active()->create();

    // Give each challenge a showcase with different upvote counts
    $showcaseLow = \App\Models\Showcase\Showcase::factory()->create();
    $lowUpvotes->showcases()->attach($showcaseLow);
    $showcaseLow->upvoters()->attach(\App\Models\User::factory()->count(1)->create()->pluck('id'));

    $showcaseHigh = \App\Models\Showcase\Showcase::factory()->create();
    $highUpvotes->showcases()->attach($showcaseHigh);
    $showcaseHigh->upvoters()->attach(\App\Models\User::factory()->count(5)->create()->pluck('id'));

    $showcaseMid = \App\Models\Showcase\Showcase::factory()->create();
    $midUpvotes->showcases()->attach($showcaseMid);
    $showcaseMid->upvoters()->attach(\App\Models\User::factory()->count(3)->create()->pluck('id'));

    get(route('inspiration.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('activeChallenges.0.id', $highUpvotes->id)
            ->where('activeChallenges.1.id', $midUpvotes->id)
            ->where('activeChallenges.2.id', $lowUpvotes->id)
        );
});

test('index returns correct data structure for active challenges', function () {
    $organisation = Organisation::factory()->create();
    $challenge = Challenge::factory()
        ->ongoing()
        ->forOrganisation($organisation)
        ->create()
        ->fresh();

    get(route('inspiration.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('activeChallenges.0', fn (AssertableInertia $c) => $c
                ->where('id', $challenge->id)
                ->where('slug', $challenge->slug)
                ->where('title', $challenge->title)
                ->where('tagline', $challenge->tagline)
                ->where('thumbnail_url', null)
                ->where('thumbnail_rect_strings', null)
                ->where('starts_at', $challenge->starts_at->toIso8601String())
                ->where('ends_at', $challenge->ends_at->toIso8601String())
                ->where('showcases_count', 0)
                ->where('total_upvotes_count', 0)
                ->has('organisation', fn (AssertableInertia $o) => $o
                    ->where('id', $organisation->id)
                    ->where('name', $organisation->name)
                    ->where('tagline', $organisation->tagline)
                    ->where('thumbnail_url', null)
                    ->where('thumbnail_rect_strings', null)
                )
                ->missing('description')
                ->missing('description_html')
                ->missing('is_active')
                ->missing('is_featured')
            )
        );
});

test('index returns correct data structure for featured challenges', function () {
    $organisation = Organisation::factory()->create();
    $challenge = Challenge::factory()
        ->ongoing()
        ->featured()
        ->forOrganisation($organisation)
        ->create()
        ->fresh();

    get(route('inspiration.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('featuredChallenges.0', fn (AssertableInertia $c) => $c
                ->where('id', $challenge->id)
                ->where('slug', $challenge->slug)
                ->where('title', $challenge->title)
                ->where('tagline', $challenge->tagline)
                ->where('thumbnail_url', null)
                ->where('thumbnail_rect_strings', null)
                ->where('starts_at', $challenge->starts_at->toIso8601String())
                ->where('ends_at', $challenge->ends_at->toIso8601String())
                ->where('is_featured', true)
                ->where('showcases_count', 0)
                ->where('total_upvotes_count', 0)
                ->has('organisation', fn (AssertableInertia $o) => $o
                    ->where('id', $organisation->id)
                    ->where('name', $organisation->name)
                    ->where('tagline', $organisation->tagline)
                    ->where('thumbnail_url', null)
                    ->where('thumbnail_rect_strings', null)
                )
                ->missing('description')
                ->missing('description_html')
                ->missing('is_active')
            )
        );
});

test('index returns empty arrays when no active challenges exist', function () {
    Challenge::factory()->count(3)->create(); // All inactive

    get(route('inspiration.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('activeChallenges', 0)
            ->has('featuredChallenges', 0)
        );
});

test('index includes showcases_count for active challenges', function () {
    $challenge = Challenge::factory()->active()->create();
    $challenge->showcases()->attach(
        \App\Models\Showcase\Showcase::factory()->count(5)->create()->pluck('id')
    );

    get(route('inspiration.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('activeChallenges.0.showcases_count', 5)
        );
});

test('index includes total_upvotes_count for active challenges', function () {
    $challenge = Challenge::factory()->active()->create();
    $showcases = \App\Models\Showcase\Showcase::factory()->count(2)->create();
    $challenge->showcases()->attach($showcases->pluck('id'));

    // Add 3 upvoters to first showcase
    $users1 = \App\Models\User::factory()->count(3)->create();
    $showcases[0]->upvoters()->attach($users1->pluck('id'));

    // Add 2 upvoters to second showcase
    $users2 = \App\Models\User::factory()->count(2)->create();
    $showcases[1]->upvoters()->attach($users2->pluck('id'));

    get(route('inspiration.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('activeChallenges.0.total_upvotes_count', 5)
        );
});
