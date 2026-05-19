<?php

use App\Enums\ChallengeInviteCodeImportStatus;
use App\Models\Challenge\ChallengeInviteCode;
use App\Models\Challenge\ChallengeInviteCodeImport;
use App\Models\User;

describe('inviteCode relationship', function () {
    test('import belongs to an invite code', function () {
        $inviteCode = ChallengeInviteCode::factory()->create();
        $import = ChallengeInviteCodeImport::factory()->for($inviteCode, 'inviteCode')->create();

        expect($import->inviteCode)->toBeInstanceOf(ChallengeInviteCode::class)
            ->and($import->inviteCode->id)->toBe($inviteCode->id);
    });
});

describe('user relationship', function () {
    test('import belongs to a user', function () {
        $user = User::factory()->create();
        $import = ChallengeInviteCodeImport::factory()->for($user)->create();

        expect($import->user)->toBeInstanceOf(User::class)
            ->and($import->user->id)->toBe($user->id);
    });
});

describe('casts', function () {
    test('status is cast to ChallengeInviteCodeImportStatus enum', function () {
        $import = ChallengeInviteCodeImport::factory()->create([
            'status' => ChallengeInviteCodeImportStatus::Completed,
        ]);

        expect($import->status)->toBeInstanceOf(ChallengeInviteCodeImportStatus::class)
            ->and($import->status)->toBe(ChallengeInviteCodeImportStatus::Completed);
    });

    test('skipped_rows is cast to an array', function () {
        $skippedRows = [
            ['row' => 2, 'reason' => 'Missing email'],
            ['row' => 5, 'reason' => 'Duplicate email'],
        ];

        $import = ChallengeInviteCodeImport::factory()->create([
            'skipped_rows' => $skippedRows,
        ]);

        expect($import->skipped_rows)->toBeArray()
            ->and($import->skipped_rows)->toBe($skippedRows);
    });

    test('counts are cast to integers', function () {
        $import = ChallengeInviteCodeImport::factory()->create([
            'total_rows' => '10',
            'imported_count' => '7',
            'skipped_count' => '3',
        ]);

        expect($import->total_rows)->toBeInt()->toBe(10)
            ->and($import->imported_count)->toBeInt()->toBe(7)
            ->and($import->skipped_count)->toBeInt()->toBe(3);
    });
});

describe('factory states', function () {
    test('processing state sets status to Processing', function () {
        $import = ChallengeInviteCodeImport::factory()->processing()->create();

        expect($import->status)->toBe(ChallengeInviteCodeImportStatus::Processing);
    });

    test('completed state sets status to Completed', function () {
        $import = ChallengeInviteCodeImport::factory()->completed()->create();

        expect($import->status)->toBe(ChallengeInviteCodeImportStatus::Completed);
    });

    test('failed state sets status to Failed', function () {
        $import = ChallengeInviteCodeImport::factory()->failed()->create();

        expect($import->status)->toBe(ChallengeInviteCodeImportStatus::Failed);
    });
});
