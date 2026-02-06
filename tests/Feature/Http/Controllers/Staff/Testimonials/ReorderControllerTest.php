<?php

use App\Models\Testimonial;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

describe('auth', function () {
    test('requires authentication', function () {
        $testimonial = Testimonial::factory()->create();

        post(route('staff.testimonials.reorder'), [
            'items' => [
                ['id' => $testimonial->id, 'display_order' => 0],
            ],
        ])->assertRedirect(route('login'));
    });

    test('forbids regular users', function () {
        /** @var User */
        $user = User::factory()->create();
        $testimonial = Testimonial::factory()->create();

        actingAs($user);

        post(route('staff.testimonials.reorder'), [
            'items' => [
                ['id' => $testimonial->id, 'display_order' => 0],
            ],
        ])->assertForbidden();
    });

    test('allows moderators to reorder testimonials', function () {
        $moderator = User::factory()->moderator()->create();
        $testimonial = Testimonial::factory()->create();

        actingAs($moderator);

        post(route('staff.testimonials.reorder'), [
            'items' => [
                ['id' => $testimonial->id, 'display_order' => 0],
            ],
        ])->assertRedirect();
    });
});
