<?php

namespace App\Mcp\Schemas\Staff\Showcase;

use App\Mcp\Requests\Showcase\ListShowcaseUpvotersRequest;
use Illuminate\Contracts\JsonSchema\JsonSchema;

class ListShowcaseUpvotersSchema
{
    /**
     * @return array<string, mixed>
     */
    public function __invoke(JsonSchema $schema): array
    {
        return [
            'showcase_id' => $schema->integer()
                ->min(1)
                ->required()
                ->description('The stable showcase id.'),
            'limit' => $schema->integer()
                ->min(1)
                ->max(ListShowcaseUpvotersRequest::MAX_LIMIT)
                ->description('Maximum number of upvoters to return per page. Defaults to '.ListShowcaseUpvotersRequest::DEFAULT_LIMIT.'.'),
            'cursor' => $schema->string()
                ->description('Opaque pagination cursor returned as next_cursor from a previous call.'),
        ];
    }
}
