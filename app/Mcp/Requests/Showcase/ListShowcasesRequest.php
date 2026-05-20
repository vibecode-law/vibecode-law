<?php

namespace App\Mcp\Requests\Showcase;

use App\Enums\ShowcaseStatus;
use Illuminate\Validation\Rule;

class ListShowcasesRequest
{
    public const DEFAULT_LIMIT = 25;

    public const MAX_LIMIT = 100;

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::in(array_map(fn (ShowcaseStatus $status): string => $status->name, ShowcaseStatus::cases()))],
            'practice_area' => ['nullable', 'string'],
            'query' => ['nullable', 'string', 'max:200'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:'.self::MAX_LIMIT],
            'cursor' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.in' => 'The status must be one of: Draft, Pending, Approved, Rejected.',
            'limit.max' => 'The limit may not be greater than '.self::MAX_LIMIT.'.',
        ];
    }
}
