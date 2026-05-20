<?php

namespace App\Mcp\Requests\Challenge;

use App\Enums\ShowcaseStatus;
use Illuminate\Validation\Rule;

class ListChallengeShowcasesRequest
{
    public const DEFAULT_LIMIT = 25;

    public const MAX_LIMIT = 100;

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'challenge_id' => ['required', 'integer', 'min:1'],
            'status' => ['nullable', Rule::in(array_map(fn (ShowcaseStatus $case): string => $case->name, ShowcaseStatus::cases()))],
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
