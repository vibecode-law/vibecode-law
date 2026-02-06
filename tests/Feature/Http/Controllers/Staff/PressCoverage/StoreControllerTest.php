<?php

use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

describe('auth', function () {
    test('requires authentication', function () {
        post(route('staff.press-coverage.store'), [
            'title' => 'Test Article',
            'publication_name' => 'Test Publication',
            'publication_date' => '2026-01-01',
            'url' => 'https://example.com/article',
        ])->assertRedirect(route('login'));
    });

    test('forbids regular users', function () {
        /** @var User */
        $user = User::factory()->create();

        actingAs($user);

        post(route('staff.press-coverage.store'), [
            'title' => 'Test Article',
            'publication_name' => 'Test Publication',
            'publication_date' => '2026-01-01',
            'url' => 'https://example.com/article',
        ])->assertForbidden();
    });

    test('allows moderators to store press coverage', function () {
        $moderator = User::factory()->moderator()->create();

        actingAs($moderator);

        post(route('staff.press-coverage.store'), [
            'title' => 'Test Article',
            'publication_name' => 'Test Publication',
            'publication_date' => '2026-01-01',
            'url' => 'https://example.com/article',
        ])->assertRedirect();
    });
});
