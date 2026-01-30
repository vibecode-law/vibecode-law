<?php

use App\Models\Showcase\Showcase;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    Config::set('app.launched', false);
});

test('returns all showcases ordered up upvotes descending', function () {
    $upvoters = User::factory()->count(5)->create();

    $mostUpvoted = Showcase::factory()->approved()->create([
        'title' => 'Most Upvoted',
    ]);
    $mostUpvoted->upvoters()->attach($upvoters->pluck('id'));

    $leastUpvoted = Showcase::factory()->approved()->create([
        'title' => 'Least Upvoted',
    ]);

    get('/')->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('home')
            ->has('featuredShowcases', 2)
            ->where('featuredShowcases.0.id', $mostUpvoted->id)
            ->where('featuredShowcases.1.id', $leastUpvoted->id)
            ->missing('showcasesByMonth')
        );
});

test('returns minimal fields for featured showcases', function () {
    /** @var User */
    $user = User::factory()->create();

    $showcase = Showcase::factory()
        ->approved()
        ->create([
            'title' => 'Test Showcase',
            'slug' => 'test-showcase',
            'tagline' => 'Test tagline',
            'thumbnail_extension' => 'jpg',
            'thumbnail_crop' => ['x' => 10, 'y' => 20, 'width' => 100, 'height' => 100],
        ]);

    $showcase->upvoters()->attach($user);

    actingAs($user)
        ->get('/')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('home')
            ->has('featuredShowcases.0', fn (AssertableInertia $showcaseProp) => $showcaseProp
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
