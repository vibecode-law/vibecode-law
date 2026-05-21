<?php

namespace App\Mcp\Tools\Staff\User;

use App\Mcp\Requests\User\ListUsersRequest;
use App\Mcp\Schemas\Staff\User\ListUsersSchema;
use App\Mcp\Shapes\User\UserColumn;
use App\Mcp\Shapes\User\UserSummaryResource;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('list_users')]
#[Description('List users as a condensed index. Returns id, first_name and last_name per entry by default; request additional fields with the columns parameter. Supports filters (first_name, last_name, ids) and cursor pagination.')]
class ListUsersTool extends Tool
{
    /**
     * Fields returned for every user regardless of the requested columns.
     *
     * @var array<int, string>
     */
    public const SUMMARY_FIELDS = ['id', 'first_name', 'last_name'];

    public function handle(Request $request): ResponseFactory
    {
        $validated = $this->validated($request);

        $columns = $this->resolveColumns($validated);

        $query = $this->buildQuery($validated);

        $totalCount = (clone $query)->toBase()->getCountForPagination();

        $paginator = $this->paginate($query, $validated);

        return Response::structured($this->formatResponse($paginator, $totalCount, $columns));
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return (new ListUsersSchema)($schema);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $listRequest = new ListUsersRequest;

        return $request->validate($listRequest->rules(), $listRequest->messages());
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<int, UserColumn>
     */
    private function resolveColumns(array $validated): array
    {
        return array_map(
            fn (string $column): UserColumn => UserColumn::from($column),
            $validated['columns'] ?? [],
        );
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return Builder<User>
     */
    private function buildQuery(array $validated): Builder
    {
        return User::query()
            ->when(
                isset($validated['first_name']),
                fn (Builder $builder) => $builder->where('first_name', 'like', '%'.$validated['first_name'].'%'),
            )
            ->when(
                isset($validated['last_name']),
                fn (Builder $builder) => $builder->where('last_name', 'like', '%'.$validated['last_name'].'%'),
            )
            ->when(
                isset($validated['ids']),
                fn (Builder $builder) => $builder->whereIn('id', $validated['ids']),
            );
    }

    /**
     * @param  Builder<User>  $query
     * @param  array<string, mixed>  $validated
     */
    private function paginate(Builder $query, array $validated): CursorPaginator
    {
        $limit = (int) ($validated['limit'] ?? ListUsersRequest::DEFAULT_LIMIT);

        return $query
            ->orderByDesc('id')
            ->cursorPaginate(perPage: $limit, cursorName: 'cursor', cursor: $validated['cursor'] ?? null);
    }

    /**
     * @param  array<int, UserColumn>  $columns
     * @return array<string, mixed>
     */
    private function formatResponse(CursorPaginator $paginator, int $totalCount, array $columns): array
    {
        $items = array_map(
            fn (User $user): array => $this->formatItem($user, $columns),
            $paginator->items(),
        );

        return [
            'items' => $items,
            'total_count' => $totalCount,
            'next_cursor' => $paginator->nextCursor()?->encode(),
        ];
    }

    /**
     * @param  array<int, UserColumn>  $columns
     * @return array<string, mixed>
     */
    private function formatItem(User $user, array $columns): array
    {
        $requested = array_map(fn (UserColumn $column): string => $column->value, $columns);

        $fields = [...self::SUMMARY_FIELDS, ...$requested];

        return UserSummaryResource::from($user)->only(...$fields)->toArray();
    }
}
