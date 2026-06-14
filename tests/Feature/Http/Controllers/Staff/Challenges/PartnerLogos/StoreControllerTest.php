<?php

use App\Models\Challenge\Challenge;
use App\Models\Challenge\ChallengePartnerLogo;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

beforeEach(function () {
    Storage::fake('public');
});

describe('auth', function () {
    test('requires authentication', function () {
        $challenge = Challenge::factory()->create();

        post(route('staff.challenges.partner-logos.store', $challenge), [
            'logos' => [UploadedFile::fake()->image('logo.png')],
        ])->assertRedirect(route('login'));
    });

    test('does not allow regular users', function () {
        /** @var User $user */
        $user = User::factory()->create();
        $challenge = Challenge::factory()->create();

        actingAs($user)
            ->post(route('staff.challenges.partner-logos.store', $challenge), [
                'logos' => [UploadedFile::fake()->image('logo.png')],
            ])
            ->assertForbidden();
    });
});

describe('store', function () {
    test('uploads logos and stores them in order', function () {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();

        actingAs($admin)
            ->post(route('staff.challenges.partner-logos.store', $challenge), [
                'logos' => [
                    UploadedFile::fake()->image('first.png'),
                    UploadedFile::fake()->image('second.png'),
                ],
            ])
            ->assertRedirect()
            ->assertSessionHas('flash.message', [
                'message' => 'Partner logos uploaded successfully.',
                'type' => 'success',
            ]);

        $logos = $challenge->partnerLogos()->get();

        expect($logos)->toHaveCount(2)
            ->and($logos[0]->filename)->toBe('first.png')
            ->and($logos[0]->order)->toBe(1)
            ->and($logos[1]->filename)->toBe('second.png')
            ->and($logos[1]->order)->toBe(2);

        Storage::disk('public')->assertExists($logos[0]->path);
        Storage::disk('public')->assertExists($logos[1]->path);
    });

    test('appends to existing logos preserving order', function () {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();
        ChallengePartnerLogo::factory()->create([
            'challenge_id' => $challenge->id,
            'order' => 5,
        ]);

        actingAs($admin)
            ->post(route('staff.challenges.partner-logos.store', $challenge), [
                'logos' => [UploadedFile::fake()->image('new.png')],
            ])
            ->assertRedirect();

        $logo = $challenge->partnerLogos()->where('filename', 'new.png')->firstOrFail();

        expect($logo->order)->toBe(6);
    });

    test('validates the upload', function (array $data, string $errorKey) {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();

        actingAs($admin)
            ->post(route('staff.challenges.partner-logos.store', $challenge), $data)
            ->assertSessionHasErrors($errorKey);

        expect($challenge->partnerLogos()->count())->toBe(0);
    })->with([
        'no files' => [[], 'logos'],
        'not an image' => [['logos' => [UploadedFile::fake()->create('doc.pdf', 100)]], 'logos.0'],
        'too large' => [['logos' => [UploadedFile::fake()->image('big.png')->size(3000)]], 'logos.0'],
    ]);
});
