<?php

use App\Models\PracticeArea;
use App\Models\Showcase\Showcase;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

test('index returns paginated approved showcases', function () {
    Showcase::factory()->count(25)->approved()->create();

    get(route('showcase.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('showcase/public/index')
            ->has('showcases.data', 20)
            ->where('showcases.meta.per_page', 20)
            ->where('showcases.meta.total', 25)
        );
});

test('index orders showcases by upvotes count descending', function () {
    $lowUpvotes = Showcase::factory()->approved()->hasUpvoters(1)->create();
    $highUpvotes = Showcase::factory()->approved()->hasUpvoters(10)->create();
    $midUpvotes = Showcase::factory()->approved()->hasUpvoters(5)->create();

    get(route('showcase.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('showcases.data.0.id', $highUpvotes->id)
            ->where('showcases.data.1.id', $midUpvotes->id)
            ->where('showcases.data.2.id', $lowUpvotes->id)
        );
});

test('index excludes non-publicly-visible showcases', function () {
    Showcase::factory()->approved()->create();
    Showcase::factory()->draft()->create();
    Showcase::factory()->pending()->create();
    Showcase::factory()->rejected()->create();
    Showcase::factory()->approved()->create(['submitted_date' => null]);

    get(route('showcase.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('showcases.data', 1)
        );
});

test('index returns practice areas for filtering', function () {
    PracticeArea::factory()->count(5)->create();

    get(route('showcase.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('availableFilters.practiceAreas', 5)
        );
});

test('index returns practice areas ordered by name', function () {
    PracticeArea::factory()->create(['name' => 'Zebra']);
    PracticeArea::factory()->create(['name' => 'Apple']);
    PracticeArea::factory()->create(['name' => 'Mango']);

    get(route('showcase.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('availableFilters.practiceAreas.0.name', 'Apple')
            ->where('availableFilters.practiceAreas.1.name', 'Mango')
            ->where('availableFilters.practiceAreas.2.name', 'Zebra')
        );
});

test('index returns no active filter', function () {
    get(route('showcase.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('activeFilter', null)
        );
});

test('index returns correct data structure', function () {
    $user = User::factory()->create();
    $showcase = Showcase::factory()->approved()->for($user)->create([
        'title' => 'Test Project',
        'slug' => 'test-project',
        'tagline' => 'A test project tagline',
        'thumbnail_extension' => 'jpg',
        'thumbnail_crop' => ['x' => 50, 'y' => 75, 'width' => 200, 'height' => 150],
    ]);

    get(route('showcase.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('showcases.data.0', fn (AssertableInertia $s) => $s
                ->where('id', $showcase->id)
                ->where('slug', 'test-project')
                ->where('title', 'Test Project')
                ->where('tagline', 'A test project tagline')
                ->has('thumbnail_url')
                ->where('thumbnail_rect_string', 'rect=50,75,200,150')
                ->where('upvotes_count', 0)
                ->missing('description')
                ->missing('url')
                ->missing('video_url')
                ->missing('source_status')
                ->missing('source_url')
                ->missing('status')
                ->missing('submitted_date')
                ->missing('created_at')
                ->missing('updated_at')
                ->missing('user')
                ->missing('practiceAreas')
                ->missing('view_count')
                ->missing('rejection_reason')
                ->missing('approved_at')
                ->missing('is_featured')
            )
        );
});

test('index includes has_upvoted for authenticated users', function () {
    /** @var User $user */
    $user = User::factory()->create();
    $showcase = Showcase::factory()->approved()->create();
    $showcase->upvoters()->attach($user);

    actingAs($user);

    get(route('showcase.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('showcases.data.0.has_upvoted', true)
        );
});

test('index does not include has_upvoted for guests', function () {
    Showcase::factory()->approved()->create();

    get(route('showcase.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->missing('showcases.data.0.has_upvoted')
        );
});
