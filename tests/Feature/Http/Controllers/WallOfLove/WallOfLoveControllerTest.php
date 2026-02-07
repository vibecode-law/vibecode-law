<?php

use App\Models\PressCoverage;
use App\Models\Testimonial;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\get;

test('returns not found when wol-enabled is false', function () {
    config()->set('app.wol-enabled', false);

    get('/wall-of-love')
        ->assertNotFound();
});

test('returns a successful response', function () {
    get(route('wall-of-love'))
        ->assertSuccessful();
});

test('renders the correct Inertia component', function () {
    get(route('wall-of-love'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('wall-of-love')
        );
});

describe('testimonials', function () {
    test('returns only published testimonials', function () {
        Testimonial::factory()->published()->count(2)->create();
        Testimonial::factory()->count(3)->create();

        get(route('wall-of-love'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('testimonials', 2)
            );
    });

    test('orders testimonials by display_order then created_at desc', function () {
        $second = Testimonial::factory()->published()->create([
            'display_order' => 1,
            'created_at' => now()->subDays(5),
        ]);
        $third = Testimonial::factory()->published()->create([
            'display_order' => 1,
            'created_at' => now()->subDays(10),
        ]);
        $first = Testimonial::factory()->published()->create([
            'display_order' => 0,
            'created_at' => now(),
        ]);

        get(route('wall-of-love'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('testimonials.0', fn (AssertableInertia $t) => $t
                    ->where('id', $first->id)
                    ->etc()
                )
                ->has('testimonials.1', fn (AssertableInertia $t) => $t
                    ->where('id', $second->id)
                    ->etc()
                )
                ->has('testimonials.2', fn (AssertableInertia $t) => $t
                    ->where('id', $third->id)
                    ->etc()
                )
            );
    });

    test('returns the expected testimonial data structure', function () {
        $user = User::factory()->create();
        $testimonial = Testimonial::factory()->for($user)->published()->create([
            'content' => 'Amazing platform!',
            'display_order' => 1,
        ]);

        get(route('wall-of-love'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('testimonials.0', fn (AssertableInertia $t) => $t
                    ->where('id', $testimonial->id)
                    ->where('user_id', $user->id)
                    ->where('content', 'Amazing platform!')
                    ->where('display_name', "{$user->first_name} {$user->last_name}")
                    ->where('is_published', true)
                    ->where('display_order', 1)
                    ->has('avatar')
                    ->has('display_job_title')
                    ->has('display_organisation')
                    ->etc()
                )
            );
    });
});

describe('press coverage', function () {
    test('returns only published press coverage', function () {
        PressCoverage::factory()->published()->count(3)->create();
        PressCoverage::factory()->count(2)->create();

        get(route('wall-of-love'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('pressCoverage', 3)
            );
    });

    test('orders press coverage by display_order then publication_date desc', function () {
        $second = PressCoverage::factory()->published()->create([
            'display_order' => 1,
            'publication_date' => '2026-01-01',
        ]);
        $third = PressCoverage::factory()->published()->create([
            'display_order' => 1,
            'publication_date' => '2025-06-01',
        ]);
        $first = PressCoverage::factory()->published()->create([
            'display_order' => 0,
            'publication_date' => '2025-01-01',
        ]);

        get(route('wall-of-love'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('pressCoverage.0', fn (AssertableInertia $item) => $item
                    ->where('id', $first->id)
                    ->etc()
                )
                ->has('pressCoverage.1', fn (AssertableInertia $item) => $item
                    ->where('id', $second->id)
                    ->etc()
                )
                ->has('pressCoverage.2', fn (AssertableInertia $item) => $item
                    ->where('id', $third->id)
                    ->etc()
                )
            );
    });

    test('returns the expected press coverage data structure', function () {
        $item = PressCoverage::factory()->published()->create([
            'title' => 'AI in Law',
            'publication_name' => 'Legal Times',
            'publication_date' => '2026-01-15',
            'url' => 'https://example.com/article',
            'excerpt' => 'A great article.',
            'display_order' => 0,
        ]);

        get(route('wall-of-love'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('pressCoverage.0', fn (AssertableInertia $pc) => $pc
                    ->where('id', $item->id)
                    ->where('title', 'AI in Law')
                    ->where('publication_name', 'Legal Times')
                    ->where('publication_date', 'January 15, 2026')
                    ->where('url', 'https://example.com/article')
                    ->where('excerpt', 'A great article.')
                    ->where('is_published', true)
                    ->where('display_order', 0)
                    ->has('thumbnail_url')
                    ->has('thumbnail_rect_string')
                    ->etc()
                )
            );
    });
});
