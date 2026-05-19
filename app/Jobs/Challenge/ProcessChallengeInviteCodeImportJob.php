<?php

namespace App\Jobs\Challenge;

use App\Actions\Challenge\ImportChallengeInviteeAction;
use App\Enums\ChallengeInviteCodeImportStatus;
use App\Exceptions\SkippedImportRowException;
use App\Models\Challenge\ChallengeInviteCode;
use App\Models\Challenge\ChallengeInviteCodeImport;
use App\Support\ChallengeInviteeCsv;
use App\ValueObjects\ChallengeInviteImportTally;
use App\ValueObjects\SkippedImportRow;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProcessChallengeInviteCodeImportJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ChallengeInviteCodeImport $import,
        public string $path,
    ) {}

    public function handle(ImportChallengeInviteeAction $importInvitee): void
    {
        $this->markProcessing();

        try {
            $handle = Storage::readStream($this->path);

            if ($handle === null) {
                $this->markFailed();

                return;
            }

            $tally = $this->importRows($handle, $importInvitee);

            fclose($handle);

            $this->markCompleted($tally);
        } catch (Throwable $exception) {
            $this->markFailed();

            throw $exception;
        } finally {
            Storage::delete($this->path);
        }
    }

    /**
     * Import every data row from the CSV stream into the invite code.
     *
     * @param  resource  $handle
     */
    private function importRows($handle, ImportChallengeInviteeAction $importInvitee): ChallengeInviteImportTally
    {
        /** @var ChallengeInviteCode $inviteCode */
        $inviteCode = $this->import->inviteCode;
        $customMessage = $this->import->custom_message;
        $columnMap = ChallengeInviteeCsv::mapHeaderToKnownColumnsAndIndex($this->readHeader($handle));

        $tally = new ChallengeInviteImportTally;

        while (($cells = fgetcsv($handle)) !== false) {
            if (ChallengeInviteeCsv::isEmptyRow($cells) === true) {
                continue;
            }

            $rowNumber = $tally->countRow();
            $row = ChallengeInviteeCsv::mapRowToKnownColumns($cells, $columnMap);
            $email = $row['email'] ?? null;

            if ($tally->registerEmail($email) === true) {
                $tally->recordSkipped(new SkippedImportRow($rowNumber, $email, 'Duplicate email within the file.'));

                continue;
            }

            try {
                $importInvitee->import(
                    inviteCode: $inviteCode,
                    row: $row,
                    customMessage: $customMessage,
                );

                $tally->recordImported();
            } catch (SkippedImportRowException $exception) {
                $tally->recordSkipped(new SkippedImportRow($rowNumber, $email, $exception->reason));
            }
        }

        return $tally;
    }

    /**
     * @param  resource  $handle
     * @return array<int, string|null>
     */
    private function readHeader($handle): array
    {
        $header = fgetcsv($handle);

        return is_array($header) ? $header : [];
    }

    private function markProcessing(): void
    {
        $this->import->update(['status' => ChallengeInviteCodeImportStatus::Processing]);
    }

    private function markCompleted(ChallengeInviteImportTally $tally): void
    {
        $this->import->update([
            'status' => ChallengeInviteCodeImportStatus::Completed,
            'total_rows' => $tally->rowsProcessed(),
            'imported_count' => $tally->importedCount(),
            'skipped_count' => $tally->skippedCount(),
            'skipped_rows' => $tally->skippedRowsArray(),
        ]);
    }

    private function markFailed(): void
    {
        $this->import->update(['status' => ChallengeInviteCodeImportStatus::Failed]);
    }
}
