<?php

namespace App\Mcp\Requests\Showcase;

class GetShowcaseRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', 'min:1'],
        ];
    }
}
