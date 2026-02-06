<?php

use App\Models\PressCoverage;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\delete;

beforeEach(function () {
    Storage::fake('public');
});

describe('auth', function () {
    test('requires authentication', function () {
        $pressCoverage = PressCoverage::factory()->create();

        delete(route('staff.press-coverage.destroy', $pressCoverage))
            ->assertRedirect(route('login'));
    });

    test('forbids regular users', function () {
        /** @var User */
        $user = User::factory()->create();
        $pressCoverage = PressCoverage::factory()->create();

        actingAs($user);

        delete(route('staff.press-coverage.destroy', $pressCoverage))
            ->assertForbidden();
    });

    test('allows moderators to delete press coverage', function () {
        $moderator = User::factory()->moderator()->create();
        $pressCoverage = PressCoverage::factory()->create();

        actingAs($moderator);

        delete(route('staff.press-coverage.destroy', $pressCoverage))
            ->assertRedirect();
    });
});

describe('destroying', function () {
    test('deletes the press coverage from the database', function () {
        $moderator = User::factory()->moderator()->create();
        $pressCoverage = PressCoverage::factory()->create();

        actingAs($moderator);

        delete(route('staff.press-coverage.destroy', $pressCoverage))
            ->assertRedirect();

        assertDatabaseMissing('press_coverage', ['id' => $pressCoverage->id]);
    });

    test('deletes the thumbnail file via model event when press coverage has thumbnail', function () {
        $moderator = User::factory()->moderator()->create();
        $pressCoverage = PressCoverage::factory()->create([
            'thumbnail_extension' => 'jpg',
        ]);

        $thumbnailPath = "press-coverage/{$pressCoverage->id}/thumbnail.jpg";
        Storage::disk('public')->put($thumbnailPath, 'thumbnail content');

        actingAs($moderator);

        delete(route('staff.press-coverage.destroy', $pressCoverage))
            ->assertRedirect();

        Storage::disk('public')->assertMissing($thumbnailPath);
        assertDatabaseMissing('press_coverage', ['id' => $pressCoverage->id]);
    });

    test('does not error when deleting press coverage without thumbnail', function () {
        $moderator = User::factory()->moderator()->create();
        $pressCoverage = PressCoverage::factory()->create([
            'thumbnail_extension' => null,
        ]);

        actingAs($moderator);

        delete(route('staff.press-coverage.destroy', $pressCoverage))
            ->assertRedirect();

        assertDatabaseMissing('press_coverage', ['id' => $pressCoverage->id]);
    });
});
