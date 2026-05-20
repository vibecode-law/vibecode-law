<?php

namespace App\Mcp\Requests\Showcase;

class ListShowcaseUpvotersRequest
{
    public const DEFAULT_LIMIT = 25;

    public const MAX_LIMIT = 100;

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'showcase_id' => ['required', 'integer', 'min:1'],
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
            'limit.max' => 'The limit may not be greater than '.self::MAX_LIMIT.'.',
        ];
    }
}
