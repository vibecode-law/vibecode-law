<?php

use App\Models\Challenge\Challenge;
use App\Models\Challenge\ChallengePartnerLogo;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\delete;

beforeEach(function () {
    Storage::fake('public');
});

describe('auth', function () {
    test('requires authentication', function () {
        $challenge = Challenge::factory()->create();
        $logo = ChallengePartnerLogo::factory()->create(['challenge_id' => $challenge->id]);

        delete(route('staff.challenges.partner-logos.destroy', [$challenge, $logo]))
            ->assertRedirect(route('login'));
    });

    test('does not allow regular users', function () {
        /** @var User $user */
        $user = User::factory()->create();
        $challenge = Challenge::factory()->create();
        $logo = ChallengePartnerLogo::factory()->create(['challenge_id' => $challenge->id]);

        actingAs($user)
            ->delete(route('staff.challenges.partner-logos.destroy', [$challenge, $logo]))
            ->assertForbidden();
    });
});

describe('destroy', function () {
    test('deletes the logo and its file', function () {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();
        $path = UploadedFile::fake()->image('logo.png')->storeAs(
            "challenge/{$challenge->id}/partner-logos",
            'logo.png',
            'public',
        );
        $logo = ChallengePartnerLogo::factory()->create([
            'challenge_id' => $challenge->id,
            'path' => $path,
        ]);

        Storage::disk('public')->assertExists($path);

        actingAs($admin)
            ->delete(route('staff.challenges.partner-logos.destroy', [$challenge, $logo]))
            ->assertRedirect()
            ->assertSessionHas('flash.message', [
                'message' => 'Partner logo deleted.',
                'type' => 'success',
            ]);

        expect(ChallengePartnerLogo::query()->find($logo->id))->toBeNull();
        Storage::disk('public')->assertMissing($path);
    });
});
