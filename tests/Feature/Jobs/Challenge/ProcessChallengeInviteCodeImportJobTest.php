<?php

use App\Actions\Challenge\ImportChallengeInviteeAction;
use App\Enums\ChallengeInviteCodeImportStatus;
use App\Exceptions\SkippedImportRowException;
use App\Jobs\Challenge\ProcessChallengeInviteCodeImportJob;
use App\Models\Challenge\ChallengeInviteCode;
use App\Models\Challenge\ChallengeInviteCodeImport;
use App\Models\User;
use App\Notifications\Challenge\ChallengeInvitation;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;

use function Pest\Laravel\mock;

function runImportJob(
    ChallengeInviteCode $inviteCode,
    string $csv,
    ?string $message = null,
    ImportChallengeInviteeAction|MockInterface|null $action = null,
): ChallengeInviteCodeImport {
    Storage::fake();
    Storage::put('imports/test.csv', $csv);

    $import = ChallengeInviteCodeImport::factory()->create([
        'challenge_invite_code_id' => $inviteCode->id,
        'custom_message' => $message,
    ]);

    app(ProcessChallengeInviteCodeImportJob::class, [
        'import' => $import,
        'path' => 'imports/test.csv',
    ])->handle($action ?? app(ImportChallengeInviteeAction::class));

    return $import->fresh();
}

test('processes the CSV end to end through the real action', function () {
    Notification::fake();
    $inviteCode = ChallengeInviteCode::factory()->create();

    $csv = "email,first_name,last_name,organisation,job_title,linkedin_url,bio\n"
        .'jane@example.com,Jane,Doe,Acme,Counsel,https://linkedin.com/in/jane,Hello'."\n";

    $import = runImportJob($inviteCode, $csv);

    $user = User::query()->where('email', 'jane@example.com')->sole();

    expect($inviteCode->users()->whereKey($user->id)->exists())->toBeTrue()
        ->and($import->status)->toBe(ChallengeInviteCodeImportStatus::Completed)
        ->and($import->total_rows)->toBe(1)
        ->and($import->imported_count)->toBe(1)
        ->and($import->skipped_count)->toBe(0);

    Notification::assertSentTo($user, ChallengeInvitation::class);
});

test('passes the mapped row, invite code and custom message to the action', function () {
    $inviteCode = ChallengeInviteCode::factory()->create();

    $action = mock(ImportChallengeInviteeAction::class);
    $action->shouldReceive('import')
        ->once()
        ->withArgs(function (ChallengeInviteCode $code, array $row, ?string $message) use ($inviteCode) {
            return $code->is($inviteCode)
                && $row['email'] === 'jane@example.com'
                && $row['first_name'] === 'Jane'
                && $row['last_name'] === 'Doe'
                && $row['organisation'] === 'Acme'
                && $message === 'See you there';
        });

    $csv = "email,first_name,last_name,organisation\njane@example.com,Jane,Doe,Acme\n";

    $import = runImportJob($inviteCode, $csv, 'See you there', $action);

    expect($import->status)->toBe(ChallengeInviteCodeImportStatus::Completed)
        ->and($import->imported_count)->toBe(1);
});

test('marks the import processing before handing rows to the action', function () {
    $inviteCode = ChallengeInviteCode::factory()->create();

    $action = mock(ImportChallengeInviteeAction::class);
    $action->shouldReceive('import')
        ->once()
        ->andReturnUsing(function () use (&$statusDuringImport) {
            $statusDuringImport = ChallengeInviteCodeImport::query()->sole()->status;
        });

    $import = runImportJob($inviteCode, "email,first_name,last_name\na@example.com,A,B\n", action: $action);

    expect($statusDuringImport)->toBe(ChallengeInviteCodeImportStatus::Processing)
        ->and($import->status)->toBe(ChallengeInviteCodeImportStatus::Completed);
});

test('skips empty rows, in-file duplicates and rows the action rejects', function () {
    $inviteCode = ChallengeInviteCode::factory()->create();

    $action = mock(ImportChallengeInviteeAction::class);
    $action->shouldReceive('import')
        ->andReturnUsing(function (ChallengeInviteCode $inviteCode, array $row) {
            if ($inviteCode->exists === true && $row['email'] === 'bad@example.com') {
                throw new SkippedImportRowException('Invalid row.');
            }
        });

    $csv = "email,first_name,last_name\n"
        ."valid@example.com,Valid,Person\n"
        ."\n"
        ."bad@example.com,Bad,Row\n"
        ."valid@example.com,Dupe,Person\n";

    $import = runImportJob($inviteCode, $csv, action: $action);

    expect($import->status)->toBe(ChallengeInviteCodeImportStatus::Completed)
        ->and($import->total_rows)->toBe(3)
        ->and($import->imported_count)->toBe(1)
        ->and($import->skipped_count)->toBe(2)
        ->and($import->skipped_rows)->toHaveCount(2)
        ->and($import->skipped_rows[0]['reason'])->toBe('Invalid row.')
        ->and($import->skipped_rows[1]['reason'])->toBe('Duplicate email within the file.');
});

test('marks the import failed when the file cannot be read', function () {
    Storage::fake();
    $inviteCode = ChallengeInviteCode::factory()->create();
    $import = ChallengeInviteCodeImport::factory()->create([
        'challenge_invite_code_id' => $inviteCode->id,
    ]);

    /** @var ImportChallengeInviteeAction&MockInterface $action */
    $action = mock(ImportChallengeInviteeAction::class);
    $action->shouldNotReceive('import');

    app(ProcessChallengeInviteCodeImportJob::class, [
        'import' => $import,
        'path' => 'imports/missing.csv',
    ])->handle($action);

    expect($import->fresh()->status)->toBe(ChallengeInviteCodeImportStatus::Failed);
});

test('marks the import failed and rethrows when the action throws unexpectedly', function () {
    $inviteCode = ChallengeInviteCode::factory()->create();

    $action = mock(ImportChallengeInviteeAction::class);
    $action->shouldReceive('import')->andThrow(new RuntimeException('boom'));

    $csv = "email,first_name,last_name\na@example.com,A,B\n";

    expect(fn () => runImportJob($inviteCode, $csv, action: $action))
        ->toThrow(RuntimeException::class, 'boom');

    Storage::assertMissing('imports/test.csv');
});

test('deletes the uploaded file once processed', function () {
    $inviteCode = ChallengeInviteCode::factory()->create();

    $action = mock(ImportChallengeInviteeAction::class);
    $action->shouldReceive('import');

    runImportJob($inviteCode, "email,first_name,last_name\na@example.com,A,B\n", action: $action);

    Storage::assertMissing('imports/test.csv');
});
