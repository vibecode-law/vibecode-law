<?php

namespace App\Enums;

use App\Concerns\FrontendTransformable;

enum InviteCodeScope: int
{
    use FrontendTransformable;

    case View = 1;
    case ViewAndSubmit = 2;

    public function label(): string
    {
        return match ($this) {
            self::View => 'View',
            self::ViewAndSubmit => 'View & Submit',
        };
    }

    /**
     * @return array<int, int>
     */
    public function satisfiedBy(): array
    {
        return match ($this) {
            self::View => [self::View->value, self::ViewAndSubmit->value],
            self::ViewAndSubmit => [self::ViewAndSubmit->value],
        };
    }
}
