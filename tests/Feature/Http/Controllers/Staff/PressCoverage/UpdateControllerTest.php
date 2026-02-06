<?php

use App\Models\PressCoverage;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\put;

describe('auth', function () {
    test('requires authentication', function () {
        $pressCoverage = PressCoverage::factory()->create();

        put(route('staff.press-coverage.update', $pressCoverage), [
            'title' => 'Updated Title',
            'publication_name' => 'Updated Publication',
            'publication_date' => '2026-01-01',
            'url' => 'https://example.com/updated',
        ])->assertRedirect(route('login'));
    });

    test('forbids regular users', function () {
        /** @var User */
        $user = User::factory()->create();
        $pressCoverage = PressCoverage::factory()->create();

        actingAs($user);

        put(route('staff.press-coverage.update', $pressCoverage), [
            'title' => 'Updated Title',
            'publication_name' => 'Updated Publication',
            'publication_date' => '2026-01-01',
            'url' => 'https://example.com/updated',
        ])->assertForbidden();
    });

    test('allows moderators to update press coverage', function () {
        $moderator = User::factory()->moderator()->create();
        $pressCoverage = PressCoverage::factory()->create();

        actingAs($moderator);

        put(route('staff.press-coverage.update', $pressCoverage), [
            'title' => 'Updated Title',
            'publication_name' => 'Updated Publication',
            'publication_date' => '2026-01-01',
            'url' => 'https://example.com/updated',
        ])->assertRedirect();
    });
});
