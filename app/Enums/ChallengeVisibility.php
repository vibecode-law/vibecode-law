<?php

namespace App\Enums;

use App\Concerns\FrontendTransformable;

enum ChallengeVisibility: int
{
    use FrontendTransformable;

    case Public = 1;
    case InviteToSubmit = 2;
    case InviteToViewAndSubmit = 3;

    public function label(): string
    {
        return match ($this) {
            self::Public => 'Public',
            self::InviteToSubmit => 'Invite to Submit',
            self::InviteToViewAndSubmit => 'Invite to View & Submit',
        };
    }
}
