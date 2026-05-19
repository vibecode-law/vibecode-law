<?php

use App\Enums\ChallengeInviteCodeImportStatus;
use App\Jobs\Challenge\ProcessChallengeInviteCodeImportJob;
use App\Models\Challenge\Challenge;
use App\Models\Challenge\ChallengeInviteCode;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

beforeEach(function () {
    Storage::fake();
    Queue::fake();
});

function importRoute(Challenge $challenge, ChallengeInviteCode $inviteCode): string
{
    return route('staff.challenges.invite-codes.import', [$challenge, $inviteCode]);
}

function csvFile(string $content): UploadedFile
{
    return UploadedFile::fake()->createWithContent('invitees.csv', $content);
}

describe('auth', function () {
    test('requires authentication', function () {
        $inviteCode = ChallengeInviteCode::factory()->create();

        post(importRoute($inviteCode->challenge, $inviteCode), [
            'file' => csvFile("email,first_name,last_name\na@b.com,A,B\n"),
        ])->assertRedirect(route('login'));
    });

    test('forbids regular users', function () {
        /** @var User $user */
        $user = User::factory()->create();
        $inviteCode = ChallengeInviteCode::factory()->create();

        actingAs($user)
            ->post(importRoute($inviteCode->challenge, $inviteCode), [
                'file' => csvFile("email,first_name,last_name\na@b.com,A,B\n"),
            ])
            ->assertForbidden();
    });

    test('allows a user with challenge.update permission', function () {
        $user = userWithPermissions(['challenge.view', 'challenge.update']);
        $inviteCode = ChallengeInviteCode::factory()->create();

        actingAs($user)
            ->post(importRoute($inviteCode->challenge, $inviteCode), [
                'file' => csvFile("email,first_name,last_name\njane@example.com,Jane,Doe\n"),
            ])
            ->assertRedirect()
            ->assertSessionHas('flash.message', [
                'message' => 'Invitee import queued. Participants will be emailed shortly.',
                'type' => 'success',
            ]);
    });
});

describe('import', function () {
    test('queues an import for a valid CSV', function () {
        $admin = User::factory()->admin()->create();
        $inviteCode = ChallengeInviteCode::factory()->create();

        actingAs($admin)
            ->post(importRoute($inviteCode->challenge, $inviteCode), [
                'file' => csvFile("email,first_name,last_name\njane@example.com,Jane,Doe\n"),
                'custom_message' => 'Welcome aboard!',
            ])
            ->assertRedirect()
            ->assertSessionHas('flash.message', [
                'message' => 'Invitee import queued. Participants will be emailed shortly.',
                'type' => 'success',
            ]);

        $import = $inviteCode->imports()->sole();

        expect($import->status)->toBe(ChallengeInviteCodeImportStatus::Pending)
            ->and($import->custom_message)->toBe('Welcome aboard!')
            ->and($import->user_id)->toBe($admin->id);

        Queue::assertPushed(
            ProcessChallengeInviteCodeImportJob::class,
            fn ($job) => $job->import->is($import)
        );
    });

    test('rejects an empty CSV', function () {
        $admin = User::factory()->admin()->create();
        $inviteCode = ChallengeInviteCode::factory()->create();

        actingAs($admin)
            ->post(importRoute($inviteCode->challenge, $inviteCode), [
                'file' => csvFile(''),
            ])
            ->assertRedirect()
            ->assertSessionHas('flash.message', [
                'message' => 'The CSV file is empty.',
                'type' => 'error',
            ]);

        expect($inviteCode->imports()->count())->toBe(0);

        Queue::assertNotPushed(ProcessChallengeInviteCodeImportJob::class);
    });

    test('rejects a header-only CSV with no data rows', function () {
        $admin = User::factory()->admin()->create();
        $inviteCode = ChallengeInviteCode::factory()->create();

        actingAs($admin)
            ->post(importRoute($inviteCode->challenge, $inviteCode), [
                'file' => csvFile("email,first_name,last_name\n\n"),
            ])
            ->assertRedirect()
            ->assertSessionHas('flash.message', [
                'message' => 'The CSV file does not contain any data rows.',
                'type' => 'error',
            ]);

        expect($inviteCode->imports()->count())->toBe(0);

        Queue::assertNotPushed(ProcessChallengeInviteCodeImportJob::class);
    });

    test('rejects a CSV missing required columns', function () {
        $admin = User::factory()->admin()->create();
        $inviteCode = ChallengeInviteCode::factory()->create();

        actingAs($admin)
            ->post(importRoute($inviteCode->challenge, $inviteCode), [
                'file' => csvFile("email,first_name\njane@example.com,Jane\n"),
            ])
            ->assertRedirect()
            ->assertSessionHas('flash.message', [
                'message' => 'The CSV must include email, first_name and last_name columns.',
                'type' => 'error',
            ]);

        expect($inviteCode->imports()->count())->toBe(0);

        Queue::assertNotPushed(ProcessChallengeInviteCodeImportJob::class);
    });
});

describe('form data validation', function () {
    test('requires a file', function () {
        $admin = User::factory()->admin()->create();
        $inviteCode = ChallengeInviteCode::factory()->create();

        actingAs($admin)
            ->post(importRoute($inviteCode->challenge, $inviteCode), [
                'custom_message' => 'Hi',
            ])
            ->assertSessionHasErrors(['file']);
    });

    test('rejects non-CSV uploads', function () {
        $admin = User::factory()->admin()->create();
        $inviteCode = ChallengeInviteCode::factory()->create();

        actingAs($admin)
            ->post(importRoute($inviteCode->challenge, $inviteCode), [
                'file' => UploadedFile::fake()->create('invitees.pdf', 10, 'application/pdf'),
            ])
            ->assertSessionHasErrors(['file' => 'The import file must be a CSV.']);
    });

    test('rejects a file larger than 1MB', function () {
        $admin = User::factory()->admin()->create();
        $inviteCode = ChallengeInviteCode::factory()->create();

        actingAs($admin)
            ->post(importRoute($inviteCode->challenge, $inviteCode), [
                'file' => UploadedFile::fake()->create('invitees.csv', 1025, 'text/csv'),
            ])
            ->assertSessionHasErrors(['file' => 'The import file may not be larger than 1MB.']);
    });

    test('rejects a custom message over 2000 characters', function () {
        $admin = User::factory()->admin()->create();
        $inviteCode = ChallengeInviteCode::factory()->create();

        actingAs($admin)
            ->post(importRoute($inviteCode->challenge, $inviteCode), [
                'file' => csvFile("email,first_name,last_name\njane@example.com,Jane,Doe\n"),
                'custom_message' => str_repeat('a', 2001),
            ])
            ->assertSessionHasErrors(['custom_message']);
    });
});
