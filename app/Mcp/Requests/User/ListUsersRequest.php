<?php

namespace App\Mcp\Requests\User;

use App\Mcp\Shapes\User\UserColumn;
use Illuminate\Validation\Rule;

class ListUsersRequest
{
    public const DEFAULT_LIMIT = 25;

    public const MAX_LIMIT = 100;

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['nullable', 'string', 'max:200'],
            'last_name' => ['nullable', 'string', 'max:200'],
            'ids' => ['nullable', 'array'],
            'ids.*' => ['integer', 'min:1'],
            'columns' => ['nullable', 'array'],
            'columns.*' => [Rule::in(UserColumn::values())],
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
            'columns.*.in' => 'Each column must be one of: '.implode(', ', UserColumn::values()).'.',
            'limit.max' => 'The limit may not be greater than '.self::MAX_LIMIT.'.',
        ];
    }
}
