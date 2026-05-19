<?php

namespace App\Http\Resources\Challenge;

use App\Enums\ChallengeInviteCodeImportStatus;
use App\Models\Challenge\ChallengeInviteCodeImport;
use App\ValueObjects\SkippedImportRow;
use Carbon\CarbonInterface;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Resource;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class ChallengeInviteCodeImportResource extends Resource
{
    public int $id;

    public int $challenge_invite_code_id;

    public ChallengeInviteCodeImportStatus $status;

    public int $total_rows;

    public int $imported_count;

    public int $skipped_count;

    /** @var list<SkippedImportRow>|null */
    public ?array $skipped_rows;

    #[WithCast(DateTimeInterfaceCast::class)]
    public CarbonInterface $created_at;

    public static function fromModel(ChallengeInviteCodeImport $import): self
    {
        $skippedRows = $import->skipped_rows;

        return self::from([
            'id' => $import->id,
            'challenge_invite_code_id' => $import->challenge_invite_code_id,
            'status' => $import->status,
            'total_rows' => $import->total_rows,
            'imported_count' => $import->imported_count,
            'skipped_count' => $import->skipped_count,
            'skipped_rows' => is_array($skippedRows)
                ? array_map(SkippedImportRow::fromArray(...), $skippedRows)
                : null,
            'created_at' => $import->created_at,
        ]);
    }
}
