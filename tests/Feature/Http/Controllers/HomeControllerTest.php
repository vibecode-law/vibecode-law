<?php

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

test('limits showcases to top 5 per month', function () {
    $upvoters = User::factory()->count(10)->create();

    // Create 7 showcases in the same month with different upvote counts
    $showcases = collect();
    for ($i = 1; $i <= 7; $i++) {
        $showcase = Showcase::factory()->approved()->create([
            'title' => "Showcase {$i}",
            'submitted_date' => now()->startOfMonth()->addDays($i - 1),
        ]);
        // More upvotes for lower numbers (so Showcase 1 has most upvotes)
        $showcase->upvoters()->attach($upvoters->take(8 - $i)->pluck('id'));
        $showcases->push($showcase);
    }

    $response = get('/');
    $response->assertOk();

    $showcasesByMonth = $response->original->getData()['page']['props']['showcasesByMonth'];
    $currentMonth = now()->format('Y-m');

    expect($showcasesByMonth)->toHaveKey($currentMonth);
    expect($showcasesByMonth[$currentMonth])->toHaveCount(5);

    // Verify top 5 by upvotes are returned (showcases 1-5)
    $ids = collect($showcasesByMonth[$currentMonth])->pluck('id')->all();
    expect($ids)->toBe($showcases->take(5)->pluck('id')->all());
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
        ->create([
            'title' => 'Test Showcase',
            'slug' => 'test-showcase',
            'tagline' => 'Test tagline',
            'submitted_date' => now(),
            'thumbnail_extension' => 'jpg',
            'thumbnail_crop' => ['x' => 10, 'y' => 20, 'width' => 100, 'height' => 100],
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
            )
        );
});
