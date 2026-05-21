<?php

namespace App\Mcp\Schemas\Staff\Course;

use App\Mcp\Requests\Course\ListLessonsRequest;
use App\Mcp\Shapes\Course\LessonColumn;
use Illuminate\Contracts\JsonSchema\JsonSchema;

class ListLessonsSchema
{
    /**
     * @return array<string, mixed>
     */
    public function __invoke(JsonSchema $schema): array
    {
        return [
            'course_id' => $schema->integer()
                ->min(1)
                ->description('Filter to lessons belonging to a specific course.'),
            'published' => $schema->boolean()
                ->description('When true, return only lessons with a publish_date on or before today; when false, only unpublished.'),
            'gated' => $schema->boolean()
                ->description('When true, return only gated lessons; when false, only ungated.'),
            'query' => $schema->string()
                ->description('Case-insensitive text search across title and tagline.'),
            'ids' => $schema->array()
                ->items($schema->integer()->min(1))
                ->description('Filter to specific lessons by their stable ids.'),
            'columns' => $schema->array()
                ->items($schema->string()->enum(LessonColumn::values()))
                ->description('Additional fields to return per item on top of the default summary (id, slug, title, tagline, course_id, publish_date). Choose only what you need to keep responses small.'),
            'limit' => $schema->integer()
                ->min(1)
                ->max(ListLessonsRequest::MAX_LIMIT)
                ->description('Maximum number of items to return per page. Defaults to '.ListLessonsRequest::DEFAULT_LIMIT.'.'),
            'cursor' => $schema->string()
                ->description('Opaque pagination cursor returned as next_cursor from a previous call.'),
        ];
    }
}
