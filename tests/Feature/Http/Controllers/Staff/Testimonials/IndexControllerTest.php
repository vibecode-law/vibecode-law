<?php

use App\Models\Testimonial;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

describe('auth', function () {
    test('requires authentication', function () {
        get(route('staff.testimonials.index'))
            ->assertRedirect(route('login'));
    });

    test('forbids regular users', function () {
        /** @var User */
        $user = User::factory()->create();

        actingAs($user);

        get(route('staff.testimonials.index'))
            ->assertForbidden();
    });

    test('allows moderators to view testimonials index', function () {
        $moderator = User::factory()->moderator()->create();

        actingAs($moderator);

        get(route('staff.testimonials.index'))
            ->assertSuccessful();
    });
});

describe('data', function () {
    test('renders the correct Inertia component', function () {
        $moderator = User::factory()->moderator()->create();

        actingAs($moderator);

        get(route('staff.testimonials.index'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('staff-area/testimonials/index')
            );
    });

    test('returns all testimonials', function () {
        $moderator = User::factory()->moderator()->create();
        Testimonial::factory()->count(3)->create();

        actingAs($moderator);

        get(route('staff.testimonials.index'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('testimonials', 3)
            );
    });

    test('returns both published and unpublished testimonials', function () {
        $moderator = User::factory()->moderator()->create();
        Testimonial::factory()->published()->count(2)->create();
        Testimonial::factory()->count(1)->create();

        actingAs($moderator);

        get(route('staff.testimonials.index'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('testimonials', 3)
            );
    });

    test('orders testimonials by display_order then created_at desc', function () {
        $moderator = User::factory()->moderator()->create();

        $second = Testimonial::factory()->create([
            'display_order' => 1,
            'created_at' => now()->subDays(5),
        ]);
        $third = Testimonial::factory()->create([
            'display_order' => 1,
            'created_at' => now()->subDays(10),
        ]);
        $first = Testimonial::factory()->create([
            'display_order' => 0,
            'created_at' => now(),
        ]);

        actingAs($moderator);

        get(route('staff.testimonials.index'))
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

    test('returns the expected data structure', function () {
        $moderator = User::factory()->moderator()->create();
        $user = User::factory()->create();

        $testimonial = Testimonial::factory()->for($user)->published()->create([
            'name' => 'Override Name',
            'job_title' => 'Override Title',
            'organisation' => 'Override Org',
            'content' => 'Great platform!',
            'display_order' => 5,
        ]);

        actingAs($moderator);

        get(route('staff.testimonials.index'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('testimonials.0', fn (AssertableInertia $t) => $t
                    ->where('id', $testimonial->id)
                    ->where('user_id', $user->id)
                    ->where('name', 'Override Name')
                    ->where('job_title', 'Override Title')
                    ->where('organisation', 'Override Org')
                    ->where('content', 'Great platform!')
                    ->where('display_name', "{$user->first_name} {$user->last_name}")
                    ->where('display_job_title', $user->job_title)
                    ->where('display_organisation', $user->organisation)
                    ->where('is_published', true)
                    ->where('display_order', 5)
                    ->has('avatar')
                    ->where('avatar_rect_string', null)
                    ->where('avatar_crop', null)
                    ->has('user')
                )
            );
    });

    test('includes user relationship data', function () {
        $moderator = User::factory()->moderator()->create();
        $user = User::factory()->create();

        Testimonial::factory()->for($user)->create();

        actingAs($moderator);

        get(route('staff.testimonials.index'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('testimonials.0.user')
            );
    });

    test('handles testimonials without user', function () {
        $moderator = User::factory()->moderator()->create();

        Testimonial::factory()->create([
            'user_id' => null,
            'name' => 'External Person',
        ]);

        actingAs($moderator);

        get(route('staff.testimonials.index'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('testimonials.0', fn (AssertableInertia $t) => $t
                    ->where('user_id', null)
                    ->where('display_name', 'External Person')
                    ->etc()
                )
            );
    });
});
