<?php

namespace App\Mcp\Schemas\Staff\Challenge;

use App\Mcp\Requests\Challenge\ListChallengesRequest;
use App\Mcp\Shapes\Challenge\ChallengeColumn;
use Illuminate\Contracts\JsonSchema\JsonSchema;

class ListChallengesSchema
{
    /**
     * @return array<string, mixed>
     */
    public function __invoke(JsonSchema $schema): array
    {
        return [
            'visibility' => $schema->string()
                ->enum(['Public', 'InviteToSubmit', 'InviteToViewAndSubmit'])
                ->description('Filter by challenge visibility.'),
            'active' => $schema->boolean()
                ->description('When true, return only active challenges; when false, return only inactive challenges.'),
            'query' => $schema->string()
                ->description('Case-insensitive text search across title and tagline.'),
            'ids' => $schema->array()
                ->items($schema->integer()->min(1))
                ->description('Filter to specific challenges by their stable ids.'),
            'columns' => $schema->array()
                ->items($schema->string()->enum(ChallengeColumn::values()))
                ->description('Additional fields to return per item on top of the default summary (id, slug, title, tagline, starts_at, ends_at, is_active, visibility). Choose only what you need to keep responses small.'),
            'limit' => $schema->integer()
                ->min(1)
                ->max(ListChallengesRequest::MAX_LIMIT)
                ->description('Maximum number of items to return per page. Defaults to '.ListChallengesRequest::DEFAULT_LIMIT.'.'),
            'cursor' => $schema->string()
                ->description('Opaque pagination cursor returned as next_cursor from a previous call.'),
        ];
    }
}
