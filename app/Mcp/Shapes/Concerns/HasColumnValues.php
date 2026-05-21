<?php

namespace App\Mcp\Shapes\Concerns;

/**
 * Shared helper for the backed column enums used by the list tools. Provides
 * the string values of every case for validation rules and JSON schema enums.
 */
trait HasColumnValues
{
    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $column): string => $column->value, self::cases());
    }
}
