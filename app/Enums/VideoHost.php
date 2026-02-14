<?php

namespace App\Enums;

enum VideoHost: int
{
    case Mux = 1;

    public function label(): string
    {
        return match ($this) {
            self::Mux => 'Mux',
        };
    }
}
