<?php

namespace App\Enums;

use App\Concerns\FrontendTransformable;

enum TagType: int
{
    use FrontendTransformable;
    case Tool = 1;
    case Skill = 2;
    case TechStack = 3;
    case Other = 100;

    public function label(): string
    {
        return match ($this) {
            self::Tool => 'Tool',
            self::Skill => 'Skill',
            self::TechStack => 'Tech Stack',
            self::Other => 'Other',
        };
    }
}
