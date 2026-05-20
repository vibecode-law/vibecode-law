<?php

namespace App\Mcp\Schemas\Staff\Challenge;

use App\Mcp\Requests\Challenge\ListChallengeShowcasesRequest;
use Illuminate\Contracts\JsonSchema\JsonSchema;

class ListChallengeShowcasesSchema
{
    /**
     * @return array<string, mixed>
     */
    public function __invoke(JsonSchema $schema): array
    {
        return [
            'challenge_id' => $schema->integer()
                ->min(1)
                ->required()
                ->description('The stable challenge id.'),
            'status' => $schema->string()
                ->enum(['Draft', 'Pending', 'Approved', 'Rejected'])
                ->description('Filter the attached showcases by status.'),
            'limit' => $schema->integer()
                ->min(1)
                ->max(ListChallengeShowcasesRequest::MAX_LIMIT)
                ->description('Maximum number of items to return per page. Defaults to '.ListChallengeShowcasesRequest::DEFAULT_LIMIT.'.'),
            'cursor' => $schema->string()
                ->description('Opaque pagination cursor returned as next_cursor from a previous call.'),
        ];
    }
}
