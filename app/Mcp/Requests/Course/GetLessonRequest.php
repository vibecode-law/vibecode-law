<?php

namespace App\Mcp\Requests\Course;

class GetLessonRequest
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
