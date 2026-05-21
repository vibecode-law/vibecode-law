<?php

namespace App\Mcp\Schemas\Staff\Showcase;

use App\Mcp\Requests\Showcase\ListShowcasesRequest;
use App\Mcp\Shapes\Showcase\ShowcaseColumn;
use Illuminate\Contracts\JsonSchema\JsonSchema;

class ListShowcasesSchema
{
    /**
     * @return array<string, mixed>
     */
    public function __invoke(JsonSchema $schema): array
    {
        return [
            'status' => $schema->string()
                ->enum(['Draft', 'Pending', 'Approved', 'Rejected'])
                ->description('Filter by showcase status.'),
            'practice_area' => $schema->string()
                ->description('Filter by practice area slug. Use list_practice_areas to discover valid slugs.'),
            'query' => $schema->string()
                ->description('Case-insensitive text search across title and tagline.'),
            'user_id' => $schema->integer()
                ->min(1)
                ->description('Filter to showcases owned by a specific user id.'),
            'ids' => $schema->array()
                ->items($schema->integer()->min(1))
                ->description('Filter to specific showcases by their stable ids.'),
            'columns' => $schema->array()
                ->items($schema->string()->enum(ShowcaseColumn::values()))
                ->description('Additional fields to return per item on top of the default summary (id, slug, title, tagline, status, submitted_date, user_id). Choose only what you need to keep responses small.'),
            'limit' => $schema->integer()
                ->min(1)
                ->max(ListShowcasesRequest::MAX_LIMIT)
                ->description('Maximum number of items to return per page. Defaults to '.ListShowcasesRequest::DEFAULT_LIMIT.'.'),
            'cursor' => $schema->string()
                ->description('Opaque pagination cursor returned as next_cursor from a previous call.'),
        ];
    }
}
