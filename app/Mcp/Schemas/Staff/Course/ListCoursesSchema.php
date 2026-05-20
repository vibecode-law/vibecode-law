<?php

namespace App\Mcp\Schemas\Staff\Course;

use App\Mcp\Requests\Course\ListCoursesRequest;
use Illuminate\Contracts\JsonSchema\JsonSchema;

class ListCoursesSchema
{
    /**
     * @return array<string, mixed>
     */
    public function __invoke(JsonSchema $schema): array
    {
        return [
            'experience_level' => $schema->string()
                ->enum(['Foundation', 'Intermediate', 'Advanced', 'Professional'])
                ->description('Filter by experience level.'),
            'is_featured' => $schema->boolean()
                ->description('When true, return only featured courses.'),
            'published' => $schema->boolean()
                ->description('When true, return only courses with a publish_date on or before today; when false, only unpublished.'),
            'query' => $schema->string()
                ->description('Case-insensitive text search across title and tagline.'),
            'limit' => $schema->integer()
                ->min(1)
                ->max(ListCoursesRequest::MAX_LIMIT)
                ->description('Maximum number of items to return per page. Defaults to '.ListCoursesRequest::DEFAULT_LIMIT.'.'),
            'cursor' => $schema->string()
                ->description('Opaque pagination cursor returned as next_cursor from a previous call.'),
        ];
    }
}
