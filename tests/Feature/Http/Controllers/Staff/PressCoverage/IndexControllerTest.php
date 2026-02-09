<?php

use App\Models\PressCoverage;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

describe('auth', function () {
    test('requires authentication', function () {
        get(route('staff.press-coverage.index'))
            ->assertRedirect(route('login'));
    });

    test('forbids regular users', function () {
        /** @var User */
        $user = User::factory()->create();

        actingAs($user);

        get(route('staff.press-coverage.index'))
            ->assertForbidden();
    });

    test('allows moderators to view press coverage index', function () {
        $moderator = User::factory()->moderator()->create();

        actingAs($moderator);

        get(route('staff.press-coverage.index'))
            ->assertSuccessful();
    });
});

describe('data', function () {
    test('renders the correct Inertia component', function () {
        $moderator = User::factory()->moderator()->create();

        actingAs($moderator);

        get(route('staff.press-coverage.index'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('staff-area/press-coverage/index')
            );
    });

    test('returns all press coverage items', function () {
        $moderator = User::factory()->moderator()->create();
        PressCoverage::factory()->count(4)->create();

        actingAs($moderator);

        get(route('staff.press-coverage.index'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('pressCoverage', 4)
            );
    });

    test('returns both published and unpublished items', function () {
        $moderator = User::factory()->moderator()->create();
        PressCoverage::factory()->published()->count(2)->create();
        PressCoverage::factory()->count(1)->create();

        actingAs($moderator);

        get(route('staff.press-coverage.index'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('pressCoverage', 3)
            );
    });

    test('orders by display_order then publication_date desc', function () {
        $moderator = User::factory()->moderator()->create();

        $second = PressCoverage::factory()->create([
            'display_order' => 1,
            'publication_date' => '2026-01-01',
        ]);
        $third = PressCoverage::factory()->create([
            'display_order' => 1,
            'publication_date' => '2025-06-01',
        ]);
        $first = PressCoverage::factory()->create([
            'display_order' => 0,
            'publication_date' => '2025-01-01',
        ]);

        actingAs($moderator);

        get(route('staff.press-coverage.index'))
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

    test('returns the expected data structure', function () {
        $moderator = User::factory()->moderator()->create();

        $item = PressCoverage::factory()->published()->create([
            'title' => 'AI in Law',
            'publication_name' => 'Legal Times',
            'publication_date' => '2026-01-15',
            'url' => 'https://example.com/article',
            'excerpt' => 'A summary of the article.',
            'thumbnail_extension' => null,
            'thumbnail_crop' => null,
            'display_order' => 2,
        ]);

        actingAs($moderator);

        get(route('staff.press-coverage.index'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('pressCoverage.0', fn (AssertableInertia $pc) => $pc
                    ->where('id', $item->id)
                    ->where('title', 'AI in Law')
                    ->where('publication_name', 'Legal Times')
                    ->where('publication_date', '2026-01-15')
                    ->where('url', 'https://example.com/article')
                    ->where('excerpt', 'A summary of the article.')
                    ->where('thumbnail_url', null)
                    ->where('thumbnail_rect_string', null)
                    ->where('is_published', true)
                    ->where('display_order', 2)
                )
            );
    });
});
