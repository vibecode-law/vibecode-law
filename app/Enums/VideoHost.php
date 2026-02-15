<?php

namespace App\Enums;

use App\Concerns\FrontendTransformable;

enum VideoHost: int
{
    use FrontendTransformable;
    case Mux = 1;

    public function label(): string
    {
        return match ($this) {
            self::Mux => 'Mux',
        };
    }
}
