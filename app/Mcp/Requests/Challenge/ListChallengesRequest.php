<?php

namespace App\Mcp\Requests\Challenge;

use App\Enums\ChallengeVisibility;
use Illuminate\Validation\Rule;

class ListChallengesRequest
{
    public const DEFAULT_LIMIT = 25;

    public const MAX_LIMIT = 100;

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'visibility' => ['nullable', Rule::in(array_map(fn (ChallengeVisibility $case): string => $case->name, ChallengeVisibility::cases()))],
            'active' => ['nullable', 'boolean'],
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
            'visibility.in' => 'The visibility must be one of: Public, InviteToSubmit, InviteToViewAndSubmit.',
            'limit.max' => 'The limit may not be greater than '.self::MAX_LIMIT.'.',
        ];
    }
}
