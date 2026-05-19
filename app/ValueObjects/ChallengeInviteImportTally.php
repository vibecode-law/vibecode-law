<?php

namespace App\ValueObjects;

use Illuminate\Support\Str;

/**
 * Accumulates the running totals for a challenge invitee import and tracks
 * which emails have been seen so duplicates within the file are skipped.
 */
class ChallengeInviteImportTally
{
    private int $rowsProcessed = 0;

    private int $importedCount = 0;

    /** @var list<SkippedImportRow> */
    private array $skippedRows = [];

    /** @var array<string, true> */
    private array $seenEmails = [];

    /**
     * Count the current data row and return its line number in the file
     * (offset by one to account for the header row).
     */
    public function countRow(): int
    {
        $this->rowsProcessed++;

        return $this->rowsProcessed + 1;
    }

    /**
     * Register an email, returning true when it has already been seen.
     * Blank emails are never treated as duplicates.
     */
    public function registerEmail(?string $email): bool
    {
        $key = Str::lower(trim((string) ($email ?? '')));

        if ($key === '') {
            return false;
        }

        if (isset($this->seenEmails[$key]) === true) {
            return true;
        }

        $this->seenEmails[$key] = true;

        return false;
    }

    public function recordImported(): void
    {
        $this->importedCount++;
    }

    public function recordSkipped(SkippedImportRow $row): void
    {
        $this->skippedRows[] = $row;
    }

    public function rowsProcessed(): int
    {
        return $this->rowsProcessed;
    }

    public function importedCount(): int
    {
        return $this->importedCount;
    }

    public function skippedCount(): int
    {
        return count($this->skippedRows);
    }

    /**
     * @return list<array{row: int, email: string|null, reason: string}>
     */
    public function skippedRowsArray(): array
    {
        return array_map(
            callback: static fn (SkippedImportRow $row): array => $row->toArray(),
            array: $this->skippedRows,
        );
    }
}
