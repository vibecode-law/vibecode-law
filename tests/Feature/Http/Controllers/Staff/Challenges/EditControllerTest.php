<?php

use App\Models\Challenge\Challenge;
use App\Models\Organisation\Organisation;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

describe('auth', function () {
    test('requires authentication', function () {
        $challenge = Challenge::factory()->create();

        get(route('staff.challenges.edit', $challenge))
            ->assertRedirect(route('login'));
    });

    test('allows admin to view the edit form', function () {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();

        actingAs($admin);

        get(route('staff.challenges.edit', $challenge))
            ->assertOk();
    });

    test('does not allow moderators to edit challenges', function () {
        $moderator = User::factory()->moderator()->create();
        $challenge = Challenge::factory()->create();

        actingAs($moderator);

        get(route('staff.challenges.edit', $challenge))
            ->assertForbidden();
    });

    test('does not allow regular users to edit challenges', function () {
        /** @var User */
        $user = User::factory()->create();
        $challenge = Challenge::factory()->create();

        actingAs($user);

        get(route('staff.challenges.edit', $challenge))
            ->assertForbidden();
    });
});

describe('data', function () {
    test('renders the correct component', function () {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();

        actingAs($admin);

        get(route('staff.challenges.edit', $challenge))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('staff-area/challenges/edit', shouldExist: false)
            );
    });

    test('returns challenge data with correct structure and values', function () {
        $admin = User::factory()->admin()->create();
        $organisation = Organisation::factory()->create();
        $challenge = Challenge::factory()->forOrganisation($organisation)->active()->featured()->withDates()->create();

        actingAs($admin);

        get(route('staff.challenges.edit', $challenge))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('challenge', fn (AssertableInertia $data) => $data
                    ->where('id', $challenge->id)
                    ->where('slug', $challenge->slug)
                    ->where('title', $challenge->title)
                    ->where('tagline', $challenge->tagline)
                    ->where('description', $challenge->description)
                    ->where('starts_at', $challenge->starts_at->toIso8601String())
                    ->where('ends_at', $challenge->ends_at->toIso8601String())
                    ->where('is_active', true)
                    ->where('is_featured', true)
                    ->where('thumbnail_url', null)
                    ->where('thumbnail_rect_strings', null)
                    ->where('thumbnail_crops', null)
                    ->where('organisation.name', $organisation->name)
                    ->missing('description_html')
                    ->missing('showcases_count')
                    ->missing('total_upvotes_count')
                )
            );
    });

    test('returns null organisation when challenge has none', function () {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();

        actingAs($admin);

        get(route('staff.challenges.edit', $challenge))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('challenge.organisation', null)
            );
    });
});
