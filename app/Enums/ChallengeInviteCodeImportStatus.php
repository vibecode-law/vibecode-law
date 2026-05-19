<?php

namespace App\Enums;

use App\Concerns\FrontendTransformable;

enum ChallengeInviteCodeImportStatus: int
{
    use FrontendTransformable;

    case Pending = 1;
    case Processing = 2;
    case Completed = 3;
    case Failed = 4;

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Processing => 'Processing',
            self::Completed => 'Completed',
            self::Failed => 'Failed',
        };
    }

    public function isFinished(): bool
    {
        return match ($this) {
            self::Completed, self::Failed => true,
            self::Pending, self::Processing => false,
        };
    }
}
