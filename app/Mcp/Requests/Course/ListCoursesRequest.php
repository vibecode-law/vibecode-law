<?php

namespace App\Mcp\Requests\Course;

use App\Enums\ExperienceLevel;
use Illuminate\Validation\Rule;

class ListCoursesRequest
{
    public const DEFAULT_LIMIT = 25;

    public const MAX_LIMIT = 100;

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'experience_level' => ['nullable', Rule::in(array_map(fn (ExperienceLevel $case): string => $case->name, ExperienceLevel::cases()))],
            'is_featured' => ['nullable', 'boolean'],
            'published' => ['nullable', 'boolean'],
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
            'experience_level.in' => 'The experience_level must be one of: Foundation, Intermediate, Advanced, Professional.',
            'limit.max' => 'The limit may not be greater than '.self::MAX_LIMIT.'.',
        ];
    }
}
