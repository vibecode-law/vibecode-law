<?php

use App\Models\Challenge\Challenge;
use App\Models\Showcase\Showcase;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

test('returns showcases grouped by month', function () {
    Showcase::factory()->approved()->create([
        'submitted_date' => now()->startOfYear()->addMonth(0),
    ]);

    Showcase::factory()->approved()->create([
        'submitted_date' => now()->startOfYear()->addMonth(1),
    ]);

    get('/')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('home')
            ->has('showcasesByMonth')
        );
});

test('showcases within a month are ordered by upvotes descending', function () {
    $upvoters = User::factory()->count(5)->create();

    // Create three showcases in the same month with different upvote counts
    $mostUpvoted = Showcase::factory()->approved()->create([
        'title' => 'Most Upvoted',
        'submitted_date' => now()->startOfMonth(),
    ]);
    $mostUpvoted->upvoters()->attach($upvoters->pluck('id'));

    $middleUpvoted = Showcase::factory()->approved()->create([
        'title' => 'Middle Upvoted',
        'submitted_date' => now()->startOfMonth()->addDay(),
    ]);
    $middleUpvoted->upvoters()->attach($upvoters->take(2)->pluck('id'));

    $leastUpvoted = Showcase::factory()->approved()->create([
        'title' => 'Least Upvoted',
        'submitted_date' => now()->startOfMonth()->addDays(2),
    ]);
    // No upvotes for this one

    $response = get('/');

    $response->assertOk();

    $showcasesByMonth = $response->original->getData()['page']['props']['showcasesByMonth'];
    $currentMonth = now()->format('Y-m');

    expect($showcasesByMonth)->toHaveKey($currentMonth);

    $monthShowcases = $showcasesByMonth[$currentMonth];

    expect($monthShowcases)->toHaveCount(3);
    expect($monthShowcases[0]['id'])->toBe($mostUpvoted->id);
    expect($monthShowcases[1]['id'])->toBe($middleUpvoted->id);
    expect($monthShowcases[2]['id'])->toBe($leastUpvoted->id);
});

test('only returns approved and publicly visible showcases', function () {
    Showcase::factory()->draft()->create();
    Showcase::factory()->pending()->create();
    Showcase::factory()->rejected()->create();
    $approved = Showcase::factory()->approved()->create([
        'submitted_date' => now(),
    ]);

    get('/')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('home')
            ->where('showcasesByMonth.'.now()->format('Y-m').'.0.id', $approved->id)
        );
});

test('returns all showcases for a month', function () {
    $upvoters = User::factory()->count(10)->create();

    $showcases = collect();
    for ($i = 1; $i <= 7; $i++) {
        $showcase = Showcase::factory()->approved()->create([
            'title' => "Showcase {$i}",
            'submitted_date' => now()->startOfMonth()->addDays($i - 1),
        ]);
        $showcase->upvoters()->attach($upvoters->take(8 - $i)->pluck('id'));
        $showcases->push($showcase);
    }

    $response = get('/');
    $response->assertOk();

    $showcasesByMonth = $response->original->getData()['page']['props']['showcasesByMonth'];
    $currentMonth = now()->format('Y-m');

    expect($showcasesByMonth)->toHaveKey($currentMonth);
    expect($showcasesByMonth[$currentMonth])->toHaveCount(7);

    $ids = collect($showcasesByMonth[$currentMonth])->pluck('id')->all();
    expect($ids)->toBe($showcases->pluck('id')->all());
});

test('only returns showcases from the last 3 months', function () {
    // Create showcases in different months
    Showcase::factory()->approved()->create([
        'submitted_date' => now()->startOfMonth(),
    ]);

    Showcase::factory()->approved()->create([
        'submitted_date' => now()->subMonthNoOverflow()->startOfMonth(),
    ]);

    Showcase::factory()->approved()->create([
        'submitted_date' => now()->subMonthsNoOverflow(2)->startOfMonth(),
    ]);

    Showcase::factory()->approved()->create([
        'submitted_date' => now()->subMonthsNoOverflow(3)->startOfMonth(),
    ]);

    Showcase::factory()->approved()->create([
        'submitted_date' => now()->subMonthsNoOverflow(4)->startOfMonth(),
    ]);

    $response = get('/');
    $response->assertOk();

    ray($response->original->getData()['page']['props']);

    $showcasesByMonth = $response->original->getData()['page']['props']['showcasesByMonth'];

    // Should have 3 months
    expect($showcasesByMonth)->toHaveCount(3);

    // Should include current month and 2 months back
    expect($showcasesByMonth)->toHaveKey(now()->format('Y-m'));
    expect($showcasesByMonth)->toHaveKey(now()->subMonthNoOverflow()->format('Y-m'));
    expect($showcasesByMonth)->toHaveKey(now()->subMonthsNoOverflow(2)->format('Y-m'));

    // Should not include 3 or 4 months ago
    expect($showcasesByMonth)->not->toHaveKey(now()->subMonthsNoOverflow(3)->format('Y-m'));
    expect($showcasesByMonth)->not->toHaveKey(now()->subMonthsNoOverflow(4)->format('Y-m'));
});

test('only returns minimal fields for homepage showcases', function () {
    /** @var User $user */
    $user = User::factory()->create();

    $showcase = Showcase::factory()
        ->approved()
        ->for($user)
        ->create([
            'title' => 'Test Showcase',
            'slug' => 'test-showcase',
            'tagline' => 'Test tagline',
            'submitted_date' => now(),
            'thumbnail_extension' => 'jpg',
            'thumbnail_crop' => ['x' => 10, 'y' => 20, 'width' => 100, 'height' => 100],
            'view_count' => 15,
        ]);

    $showcase->upvoters()->attach($user);

    $monthKey = now()->format('Y-m');

    actingAs($user)
        ->get('/')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('home')
            ->has("showcasesByMonth.{$monthKey}.0", fn (AssertableInertia $showcaseProp) => $showcaseProp
                ->where('id', $showcase->id)
                ->where('slug', 'test-showcase')
                ->where('title', 'Test Showcase')
                ->where('tagline', 'Test tagline')
                ->has('thumbnail_url')
                ->where('thumbnail_rect_string', 'rect=10,20,100,100')
                ->where('upvotes_count', 1)
                ->where('has_upvoted', true)
                ->where('view_count', 15)
                ->has('user', fn (AssertableInertia $userProp) => $userProp
                    ->where('first_name', $user->first_name)
                    ->where('last_name', $user->last_name)
                    ->where('handle', $user->handle)
                    ->where('organisation', $user->organisation)
                    ->where('job_title', $user->job_title)
                    ->has('avatar')
                    ->where('linkedin_url', $user->linkedin_url)
                    ->where('team_role', null)
                )
            )
        );
});

describe('recentShowcases', function () {
    test('returns 3 most recent showcases ordered by submitted_date desc', function () {
        $oldest = Showcase::factory()->approved()->create([
            'title' => 'Oldest',
            'submitted_date' => now()->subDays(3),
        ]);

        $middle = Showcase::factory()->approved()->create([
            'title' => 'Middle',
            'submitted_date' => now()->subDays(2),
        ]);

        $newest = Showcase::factory()->approved()->create([
            'title' => 'Newest',
            'submitted_date' => now()->subDay(),
        ]);

        $extraOld = Showcase::factory()->approved()->create([
            'title' => 'Extra Old',
            'submitted_date' => now()->subDays(4),
        ]);

        get('/')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('home')
                ->has('recentShowcases', 3)
                ->where('recentShowcases.0.id', $newest->id)
                ->where('recentShowcases.1.id', $middle->id)
                ->where('recentShowcases.2.id', $oldest->id)
            );
    });

    test('only includes publicly visible showcases', function () {
        Showcase::factory()->draft()->create();
        Showcase::factory()->pending()->create();
        Showcase::factory()->rejected()->create();

        $approved = Showcase::factory()->approved()->create([
            'submitted_date' => now(),
        ]);

        get('/')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('home')
                ->has('recentShowcases', 1)
                ->where('recentShowcases.0.id', $approved->id)
            );
    });

    test('returns only minimal fields', function () {
        /** @var User $user */
        $user = User::factory()->create();

        $showcase = Showcase::factory()
            ->approved()
            ->for($user)
            ->create([
                'title' => 'Recent Showcase',
                'slug' => 'recent-showcase',
                'submitted_date' => now(),
                'thumbnail_extension' => 'jpg',
                'thumbnail_crop' => ['x' => 10, 'y' => 20, 'width' => 100, 'height' => 100],
            ]);

        get('/')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('home')
                ->has('recentShowcases.0', fn (AssertableInertia $showcaseProp) => $showcaseProp
                    ->where('id', $showcase->id)
                    ->where('slug', 'recent-showcase')
                    ->where('title', 'Recent Showcase')
                    ->has('thumbnail_url')
                    ->where('thumbnail_rect_string', 'rect=10,20,100,100')
                    ->has('user', fn (AssertableInertia $userProp) => $userProp
                        ->where('first_name', $user->first_name)
                        ->where('last_name', $user->last_name)
                        ->where('handle', $user->handle)
                        ->where('organisation', $user->organisation)
                        ->where('job_title', $user->job_title)
                        ->has('avatar')
                        ->where('linkedin_url', $user->linkedin_url)
                        ->where('team_role', null)
                    )
                )
            );
    });
});

describe('activeChallenges', function () {
    test('returns active featured challenges when feature is enabled', function () {
        config(['app.challenges_enabled' => true]);

        $activeFeatured = Challenge::factory()->active()->featured()->create([
            'title' => 'Active Featured Challenge',
        ]);

        Challenge::factory()->active()->create([
            'title' => 'Active Non-Featured',
        ]);

        Challenge::factory()->featured()->create([
            'title' => 'Inactive Featured',
            'is_active' => false,
        ]);

        get('/')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('home')
                ->has('activeChallenges', 1)
                ->where('activeChallenges.0.id', $activeFeatured->id)
                ->where('activeChallenges.0.title', 'Active Featured Challenge')
            );
    });

    test('returns empty array when feature is disabled', function () {
        config(['app.challenges_enabled' => false]);

        Challenge::factory()->active()->featured()->create();

        get('/')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('home')
                ->where('activeChallenges', [])
            );
    });

    test('orders active challenges by showcases count descending', function () {
        config(['app.challenges_enabled' => true]);

        $lessChallenged = Challenge::factory()->active()->featured()->create([
            'title' => 'Less Popular',
        ]);

        $moreChallenged = Challenge::factory()->active()->featured()->create([
            'title' => 'More Popular',
        ]);

        $showcases = Showcase::factory()->approved()->count(3)->create([
            'submitted_date' => now(),
        ]);
        $moreChallenged->showcases()->attach($showcases->pluck('id'));

        $singleShowcase = Showcase::factory()->approved()->create([
            'submitted_date' => now(),
        ]);
        $lessChallenged->showcases()->attach($singleShowcase->id);

        get('/')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('home')
                ->has('activeChallenges', 2)
                ->where('activeChallenges.0.id', $moreChallenged->id)
                ->where('activeChallenges.0.showcases_count', 3)
                ->where('activeChallenges.1.id', $lessChallenged->id)
                ->where('activeChallenges.1.showcases_count', 1)
            );
    });
});
