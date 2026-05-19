<?php

namespace App\Http\Controllers\Staff\Challenges\InviteCodes;

use App\Enums\ChallengeInviteCodeImportStatus;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Staff\ChallengeInviteCodeImportRequest;
use App\Jobs\Challenge\ProcessChallengeInviteCodeImportJob;
use App\Models\Challenge\Challenge;
use App\Models\Challenge\ChallengeInviteCode;
use App\Support\ChallengeInviteeCsv;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class ImportStoreController extends BaseController
{
    public function __invoke(ChallengeInviteCodeImportRequest $request, Challenge $challenge, ChallengeInviteCode $inviteCode): RedirectResponse
    {
        $this->authorize('manageInviteCodes', $challenge);

        $file = $request->file('file');

        $validationError = $this->csvValidationError($file);

        if ($validationError !== null) {
            return $this->error(message: $validationError);
        }

        $this->queueImport(
            inviteCode: $inviteCode,
            path: $file->store("challenge-invitee-imports/{$inviteCode->id}"),
            customMessage: $request->validated('custom_message'),
        );

        return $this->success(message: 'Invitee import queued. Participants will be emailed shortly.');
    }

    private function csvValidationError(UploadedFile $file): ?string
    {
        $handle = fopen($file->getRealPath(), 'rb');

        if ($handle === false) {
            return 'The CSV file could not be read.';
        }

        try {
            $header = fgetcsv($handle);

            if (is_array($header) === false) {
                return 'The CSV file is empty.';
            }

            if (ChallengeInviteeCsv::hasRequiredColumns(ChallengeInviteeCsv::mapHeaderToKnownColumnsAndIndex($header)) === false) {
                return 'The CSV must include email, first_name and last_name columns.';
            }

            if ($this->hasDataRow($handle) === false) {
                return 'The CSV file does not contain any data rows.';
            }

            return null;
        } finally {
            fclose($handle);
        }
    }

    /**
     * Determine whether the CSV contains at least one non-empty data row.
     *
     * @param  resource  $handle
     */
    private function hasDataRow($handle): bool
    {
        while (($cells = fgetcsv($handle)) !== false) {
            if (ChallengeInviteeCsv::isEmptyRow($cells) === false) {
                return true;
            }
        }

        return false;
    }

    private function queueImport(ChallengeInviteCode $inviteCode, string $path, ?string $customMessage): void
    {
        $import = $inviteCode->imports()->create([
            'user_id' => Auth::id(),
            'status' => ChallengeInviteCodeImportStatus::Pending,
            'custom_message' => $customMessage,
        ]);

        ProcessChallengeInviteCodeImportJob::dispatch(import: $import, path: $path);
    }

    private function success(string $message): RedirectResponse
    {
        return Redirect::back()->with('flash', [
            'message' => ['message' => $message, 'type' => 'success'],
        ]);
    }

    private function error(string $message): RedirectResponse
    {
        return Redirect::back()->with('flash', [
            'message' => ['message' => $message, 'type' => 'error'],
        ]);
    }
}
