<?php

namespace App\Enums;

use App\Concerns\FrontendTransformable;

enum ExperienceLevel: int
{
    use FrontendTransformable;
    case Beginner = 1;
    case Intermediate = 2;
    case Advanced = 3;
    case Professional = 4;

    public function label(): string
    {
        return match ($this) {
            self::Beginner => 'Beginner',
            self::Intermediate => 'Intermediate',
            self::Advanced => 'Advanced',
            self::Professional => 'Professional',
        };
    }
}
