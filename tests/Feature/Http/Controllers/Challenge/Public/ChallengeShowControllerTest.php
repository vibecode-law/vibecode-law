<?php

use App\Models\Challenge\Challenge;
use App\Models\Organisation\Organisation;
use App\Models\Showcase\Showcase;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    Config::set('app.challenges_enabled', true);
});

test('show returns 404 when challenges are disabled', function () {
    Config::set('app.challenges_enabled', false);

    $challenge = Challenge::factory()->active()->create();

    get(route('inspiration.challenges.show', $challenge))
        ->assertNotFound();
});

test('show allows admins when challenges are disabled', function () {
    Config::set('app.challenges_enabled', false);

    $admin = User::factory()->admin()->create();
    $challenge = Challenge::factory()->active()->create();

    actingAs($admin)
        ->get(route('inspiration.challenges.show', $challenge))
        ->assertOk();
});

test('show returns active challenge', function () {
    $challenge = Challenge::factory()->active()->create();

    get(route('inspiration.challenges.show', $challenge))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('challenge/public/show')
            ->has('challenge')
            ->has('showcases')
            ->has('participants')
        );
});

test('show returns 404 for inactive challenge', function () {
    $challenge = Challenge::factory()->create();

    get(route('inspiration.challenges.show', $challenge))
        ->assertNotFound();
});

test('show returns correct challenge data structure', function () {
    $organisation = Organisation::factory()->create();
    $challenge = Challenge::factory()
        ->ongoing()
        ->forOrganisation($organisation)
        ->create([
            'description' => '**description**',
        ])
        ->fresh();

    get(route('inspiration.challenges.show', $challenge))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('challenge', fn (AssertableInertia $c) => $c
                ->where('id', $challenge->id)
                ->where('slug', $challenge->slug)
                ->where('title', $challenge->title)
                ->where('tagline', $challenge->tagline)
                ->missing('description')
                ->where('description_html', "<p><strong>description</strong></p>\n")
                ->where('starts_at', $challenge->starts_at->toIso8601String())
                ->where('ends_at', $challenge->ends_at->toIso8601String())
                ->where('is_active', true)
                ->where('is_featured', false)
                ->where('thumbnail_url', $challenge->thumbnail_url)
                ->where('thumbnail_rect_strings', $challenge->thumbnail_rect_strings)
                ->has('organisation', fn (AssertableInertia $o) => $o
                    ->where('id', $organisation->id)
                    ->where('name', $organisation->name)
                    ->where('tagline', $organisation->tagline)
                    ->where('thumbnail_url', null)
                    ->where('thumbnail_rect_strings', null)
                )
            )
        );
});

test('show uses slug for route model binding', function () {
    Challenge::factory()->active()->create([
        'slug' => 'unique-slug-123',
    ]);

    get('/inspiration/challenges/unique-slug-123')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('challenge.slug', 'unique-slug-123')
        );
});

test('show returns 404 for non-existent challenge', function () {
    get('/inspiration/challenges/non-existent-slug')
        ->assertNotFound();
});

test('show returns showcases ordered by upvotes descending', function () {
    $challenge = Challenge::factory()->active()->create();
    $upvoters = User::factory()->count(5)->create();

    $mostUpvoted = Showcase::factory()->approved()->create(['title' => 'Most Upvoted']);
    $mostUpvoted->upvoters()->attach($upvoters->pluck('id'));

    $middleUpvoted = Showcase::factory()->approved()->create(['title' => 'Middle Upvoted']);
    $middleUpvoted->upvoters()->attach($upvoters->take(2)->pluck('id'));

    $leastUpvoted = Showcase::factory()->approved()->create(['title' => 'Least Upvoted']);

    $challenge->showcases()->attach([
        $mostUpvoted->id,
        $middleUpvoted->id,
        $leastUpvoted->id,
    ]);

    get(route('inspiration.challenges.show', $challenge))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('showcases', 3)
            ->where('showcases.0.id', $mostUpvoted->id)
            ->where('showcases.1.id', $middleUpvoted->id)
            ->where('showcases.2.id', $leastUpvoted->id)
        );
});

test('show returns correct showcase data structure', function () {
    $challenge = Challenge::factory()->active()->create();

    $showcase = Showcase::factory()->approved()->create([
        'title' => 'Test Showcase',
        'slug' => 'test-showcase',
        'tagline' => 'Test tagline',
        'thumbnail_extension' => 'jpg',
        'thumbnail_crop' => ['x' => 10, 'y' => 20, 'width' => 100, 'height' => 100],
    ]);

    $challenge->showcases()->attach($showcase);

    get(route('inspiration.challenges.show', $challenge))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('showcases.0', fn (AssertableInertia $s) => $s
                ->where('id', $showcase->id)
                ->where('slug', 'test-showcase')
                ->where('title', 'Test Showcase')
                ->where('tagline', 'Test tagline')
                ->has('thumbnail_url')
                ->where('thumbnail_rect_string', 'rect=10,20,100,100')
                ->where('upvotes_count', 0)
                ->missing('has_upvoted')
                ->has('view_count')
                ->has('user')
            )
        );
});

test('show includes has_upvoted for authenticated users', function () {
    $challenge = Challenge::factory()->active()->create();
    /** @var User */
    $user = User::factory()->create();

    $showcase = Showcase::factory()->approved()->create();
    $showcase->upvoters()->attach($user);

    $challenge->showcases()->attach($showcase);

    actingAs($user)
        ->get(route('inspiration.challenges.show', $challenge))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('showcases.0.has_upvoted', true)
            ->where('showcases.0.upvotes_count', 1)
        );
});

test('show has_upvoted is false when authenticated user has not upvoted', function () {
    $challenge = Challenge::factory()->active()->create();
    /** @var User */
    $user = User::factory()->create();

    $showcase = Showcase::factory()->approved()->create();
    $challenge->showcases()->attach($showcase);

    actingAs($user)
        ->get(route('inspiration.challenges.show', $challenge))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('showcases.0.has_upvoted', false)
        );
});

test('show excludes has_upvoted for guests', function () {
    $challenge = Challenge::factory()->active()->create();

    $showcase = Showcase::factory()->approved()->create();
    $challenge->showcases()->attach($showcase);

    get(route('inspiration.challenges.show', $challenge))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('showcases.0', fn (AssertableInertia $s) => $s
                ->missing('has_upvoted')
                ->has('id')
                ->has('slug')
                ->has('title')
                ->has('tagline')
                ->has('thumbnail_url')
                ->has('thumbnail_rect_string')
                ->has('upvotes_count')
                ->has('view_count')
                ->has('user')
            )
        );
});

test('show only includes publicly visible showcases', function () {
    $challenge = Challenge::factory()->active()->create();

    $approved = Showcase::factory()->approved()->create();
    $draft = Showcase::factory()->draft()->create();
    $pending = Showcase::factory()->pending()->create();
    $rejected = Showcase::factory()->rejected()->create();

    $challenge->showcases()->attach([
        $approved->id,
        $draft->id,
        $pending->id,
        $rejected->id,
    ]);

    get(route('inspiration.challenges.show', $challenge))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('showcases', 1)
            ->where('showcases.0.id', $approved->id)
        );
});

test('show returns organisation when present', function () {
    $organisation = Organisation::factory()->create();
    $challenge = Challenge::factory()
        ->active()
        ->forOrganisation($organisation)
        ->create();

    get(route('inspiration.challenges.show', $challenge))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('challenge.organisation', fn (AssertableInertia $o) => $o
                ->where('id', $organisation->id)
                ->where('name', $organisation->name)
                ->where('tagline', $organisation->tagline)
                ->where('thumbnail_url', null)
                ->where('thumbnail_rect_strings', null)
            )
        );
});

test('show returns null organisation when absent', function () {
    $challenge = Challenge::factory()->active()->create();

    get(route('inspiration.challenges.show', $challenge))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('challenge.organisation', null)
        );
});

test('show returns empty showcases array when none exist', function () {
    $challenge = Challenge::factory()->active()->create();

    get(route('inspiration.challenges.show', $challenge))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('showcases', 0)
        );
});

test('show returns unique participants from publicly visible showcases', function () {
    $challenge = Challenge::factory()->active()->create();
    $user = User::factory()->create(['first_name' => 'Alice']);

    $showcase1 = Showcase::factory()->approved()->for($user)->create();
    $showcase2 = Showcase::factory()->approved()->for($user)->create();
    $showcase3 = Showcase::factory()->approved()->create();

    $challenge->showcases()->attach([$showcase1->id, $showcase2->id, $showcase3->id]);

    get(route('inspiration.challenges.show', $challenge))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('participants', 2)
            ->where('participants.0.first_name', 'Alice')
            ->has('participants.0', fn (AssertableInertia $p) => $p
                ->has('first_name')
                ->has('avatar')
                ->has('handle')
            )
        );
});

test('show excludes participants from non-visible showcases', function () {
    $challenge = Challenge::factory()->active()->create();

    $approved = Showcase::factory()->approved()->create();
    $draft = Showcase::factory()->draft()->create();

    $challenge->showcases()->attach([$approved->id, $draft->id]);

    get(route('inspiration.challenges.show', $challenge))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('participants', 1)
        );
});
