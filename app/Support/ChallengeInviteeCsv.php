<?php

namespace App\Support;

use Illuminate\Support\Str;

class ChallengeInviteeCsv
{
    /**
     * @var list<string>
     */
    public const REQUIRED_COLUMNS = ['email', 'first_name', 'last_name'];

    /**
     * @var list<string>
     */
    public const OPTIONAL_COLUMNS = ['organisation', 'job_title', 'linkedin_url', 'bio'];

    /**
     * @param  array<int, string|null>  $header
     * @return array<string, int>
     */
    public static function mapHeaderToKnownColumnsAndIndex(array $header): array
    {
        $known = array_merge(self::REQUIRED_COLUMNS, self::OPTIONAL_COLUMNS);
        $map = [];

        foreach ($header as $index => $label) {
            $normalised = Str::of((string) $label)->trim()->lower()->replace([' ', '-'], '_')->value();

            if (in_array($normalised, $known, true) === true) {
                $map[$normalised] = $index;
            }
        }

        return $map;
    }

    /**
     * @param  array<string, int>  $mappedHeaders
     */
    public static function hasRequiredColumns(array $mappedHeaders): bool
    {
        foreach (self::REQUIRED_COLUMNS as $column) {
            if (array_key_exists($column, $mappedHeaders) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<int, string|null>  $cells
     * @param  array<string, int>  $columnMap
     * @return array<string, string|null>
     */
    public static function mapRowToKnownColumns(array $cells, array $columnMap): array
    {
        $row = [];

        foreach (array_merge(self::REQUIRED_COLUMNS, self::OPTIONAL_COLUMNS) as $column) {
            $index = $columnMap[$column] ?? null;
            $row[$column] = $index !== null ? ($cells[$index] ?? null) : null;
        }

        return $row;
    }

    /**
     * @param  array<int, string|null>  $cells
     */
    public static function isEmptyRow(array $cells): bool
    {
        foreach ($cells as $cell) {
            if ($cell !== null && trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }
}
