<?php

namespace App\Mcp\Schemas\Staff\User;

use App\Mcp\Requests\User\ListUsersRequest;
use App\Mcp\Shapes\User\UserColumn;
use Illuminate\Contracts\JsonSchema\JsonSchema;

class ListUsersSchema
{
    /**
     * @return array<string, mixed>
     */
    public function __invoke(JsonSchema $schema): array
    {
        return [
            'first_name' => $schema->string()
                ->description('Filter by first name (case-insensitive, partial match).'),
            'last_name' => $schema->string()
                ->description('Filter by last name (case-insensitive, partial match).'),
            'ids' => $schema->array()
                ->items($schema->integer()->min(1))
                ->description('Filter to specific users by their stable ids.'),
            'columns' => $schema->array()
                ->items($schema->string()->enum(UserColumn::values()))
                ->description('Additional fields to return per user on top of the default summary (id, first_name, last_name). Choose only what you need to keep responses small.'),
            'limit' => $schema->integer()
                ->min(1)
                ->max(ListUsersRequest::MAX_LIMIT)
                ->description('Maximum number of items to return per page. Defaults to '.ListUsersRequest::DEFAULT_LIMIT.'.'),
            'cursor' => $schema->string()
                ->description('Opaque pagination cursor returned as next_cursor from a previous call.'),
        ];
    }
}
