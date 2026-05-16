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

    test('allows a user with press-coverage.delete permission', function () {
        $user = userWithPermissions(['press-coverage.view', 'press-coverage.delete']);
        $pressCoverage = PressCoverage::factory()->create();

        actingAs($user);

        delete(route('staff.press-coverage.destroy', $pressCoverage))
            ->assertRedirect();
    });

    test('forbids a staff user without press-coverage.delete permission', function () {
        $user = userWithPermissions(['press-coverage.view']);
        $pressCoverage = PressCoverage::factory()->create();

        actingAs($user);

        delete(route('staff.press-coverage.destroy', $pressCoverage))
            ->assertForbidden();
    });
});

describe('destroying', function () {
    test('deletes the press coverage from the database', function () {
        $marketingManager = User::factory()->marketingManager()->create();
        $pressCoverage = PressCoverage::factory()->create();

        actingAs($marketingManager);

        delete(route('staff.press-coverage.destroy', $pressCoverage))
            ->assertRedirect();

        assertDatabaseMissing('press_coverage', ['id' => $pressCoverage->id]);
    });

    test('deletes the thumbnail file via model event when press coverage has thumbnail', function () {
        $marketingManager = User::factory()->marketingManager()->create();
        $pressCoverage = PressCoverage::factory()->create([
            'thumbnail_extension' => 'jpg',
        ]);

        $thumbnailPath = "press-coverage/{$pressCoverage->id}/thumbnail.jpg";
        Storage::disk('public')->put($thumbnailPath, 'thumbnail content');

        actingAs($marketingManager);

        delete(route('staff.press-coverage.destroy', $pressCoverage))
            ->assertRedirect();

        Storage::disk('public')->assertMissing($thumbnailPath);
        assertDatabaseMissing('press_coverage', ['id' => $pressCoverage->id]);
    });

    test('does not error when deleting press coverage without thumbnail', function () {
        $marketingManager = User::factory()->marketingManager()->create();
        $pressCoverage = PressCoverage::factory()->create([
            'thumbnail_extension' => null,
        ]);

        actingAs($marketingManager);

        delete(route('staff.press-coverage.destroy', $pressCoverage))
            ->assertRedirect();

        assertDatabaseMissing('press_coverage', ['id' => $pressCoverage->id]);
    });
});
