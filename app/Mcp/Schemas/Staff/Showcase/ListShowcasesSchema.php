<?php

namespace App\Mcp\Schemas\Staff\Showcase;

use App\Mcp\Requests\Showcase\ListShowcasesRequest;
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
            'limit' => $schema->integer()
                ->min(1)
                ->max(ListShowcasesRequest::MAX_LIMIT)
                ->description('Maximum number of items to return per page. Defaults to '.ListShowcasesRequest::DEFAULT_LIMIT.'.'),
            'cursor' => $schema->string()
                ->description('Opaque pagination cursor returned as next_cursor from a previous call.'),
        ];
    }
}
