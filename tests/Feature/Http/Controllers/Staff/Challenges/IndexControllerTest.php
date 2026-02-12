<?php

use App\Models\Challenge\Challenge;
use App\Models\Organisation\Organisation;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

describe('auth', function () {
    test('requires authentication', function () {
        get(route('staff.challenges.index'))
            ->assertRedirect(route('login'));
    });

    test('allows admin to view the challenges list', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        get(route('staff.challenges.index'))
            ->assertOk();
    });

    test('does not allow moderators to view challenges', function () {
        $moderator = User::factory()->moderator()->create();

        actingAs($moderator);

        get(route('staff.challenges.index'))
            ->assertForbidden();
    });

    test('does not allow regular users to view challenges', function () {
        /** @var User */
        $user = User::factory()->create();

        actingAs($user);

        get(route('staff.challenges.index'))
            ->assertForbidden();
    });
});

describe('data', function () {
    test('returns paginated challenges', function () {
        $admin = User::factory()->admin()->create();
        Challenge::factory()->count(30)->create();

        actingAs($admin);

        get(route('staff.challenges.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('staff-area/challenges/index', shouldExist: false)
                ->has('challenges.data', 25)
                ->has('challenges.meta')
                ->has('challenges.links')
            );
    });

    test('returns challenges with correct structure and values', function () {
        $admin = User::factory()->admin()->create();
        $organisation = Organisation::factory()->create();
        $challenge = Challenge::factory()->forOrganisation($organisation)->active()->featured()->withDates()->create();

        actingAs($admin);

        get(route('staff.challenges.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('staff-area/challenges/index', shouldExist: false)
                ->has('challenges.data.0', fn (AssertableInertia $data) => $data
                    ->where('id', $challenge->id)
                    ->where('slug', $challenge->slug)
                    ->where('title', $challenge->title)
                    ->where('tagline', $challenge->tagline)
                    ->where('starts_at', $challenge->starts_at->toIso8601String())
                    ->where('ends_at', $challenge->ends_at->toIso8601String())
                    ->where('is_active', true)
                    ->where('is_featured', true)
                    ->where('organisation.name', $organisation->name)
                    ->where('showcases_count', 0)
                    ->missing('description')
                    ->missing('description_html')
                    ->missing('thumbnail_url')
                    ->missing('thumbnail_rect_strings')
                    ->missing('total_upvotes_count')
                )
            );
    });

    test('orders challenges by created_at descending', function () {
        $admin = User::factory()->admin()->create();
        $older = Challenge::factory()->create(['title' => 'Older Challenge', 'created_at' => now()->subDay()]);
        $newer = Challenge::factory()->create(['title' => 'Newer Challenge', 'created_at' => now()]);

        actingAs($admin);

        get(route('staff.challenges.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('challenges.data.0.id', $newer->id)
                ->where('challenges.data.1.id', $older->id)
            );
    });

    test('includes showcases count', function () {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();
        $challenge->showcases()->attach(
            \App\Models\Showcase\Showcase::factory()->count(3)->create()->pluck('id')
        );

        actingAs($admin);

        get(route('staff.challenges.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('challenges.data.0.showcases_count', 3)
            );
    });

    test('returns null organisation when challenge has none', function () {
        $admin = User::factory()->admin()->create();
        Challenge::factory()->create();

        actingAs($admin);

        get(route('staff.challenges.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('challenges.data.0.organisation', null)
            );
    });
});
