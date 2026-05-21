<?php

namespace App\Mcp\Tools\Staff\Challenge;

use App\Enums\ChallengeVisibility;
use App\Mcp\Requests\Challenge\ListChallengesRequest;
use App\Mcp\Schemas\Staff\Challenge\ListChallengesSchema;
use App\Mcp\Shapes\Challenge\ChallengeColumn;
use App\Mcp\Shapes\Challenge\ChallengeDetailResource;
use App\Models\Challenge\Challenge;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('list_challenges')]
#[Description('List challenges as a condensed index. Returns id, slug, title, tagline, starts_at, ends_at, is_active and visibility per entry by default; request additional fields with the columns parameter. Use get_challenge to fetch full details. Supports filters (visibility, active, query, ids) and cursor pagination.')]
class ListChallengesTool extends Tool
{
    /**
     * Fields returned for every challenge regardless of the requested columns.
     *
     * @var array<int, string>
     */
    public const SUMMARY_FIELDS = ['id', 'slug', 'title', 'tagline', 'starts_at', 'ends_at', 'is_active', 'visibility'];

    public function handle(Request $request): ResponseFactory
    {
        $validated = $this->validated($request);

        $query = $this->buildQuery($validated);

        $totalCount = (clone $query)->toBase()->getCountForPagination();

        $paginator = $this->paginate($query, $validated);

        return Response::structured($this->formatResponse($paginator, $totalCount, $this->resolveColumns($validated)));
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return (new ListChallengesSchema)($schema);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $listRequest = new ListChallengesRequest;

        return $request->validate($listRequest->rules(), $listRequest->messages());
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<int, ChallengeColumn>
     */
    private function resolveColumns(array $validated): array
    {
        return array_map(
            fn (string $column): ChallengeColumn => ChallengeColumn::from($column),
            $validated['columns'] ?? [],
        );
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return Builder<Challenge>
     */
    private function buildQuery(array $validated): Builder
    {
        return Challenge::query()
            ->withTotalUpvotesCount()
            ->withCount('showcases')
            ->when(
                isset($validated['visibility']),
                fn (Builder $builder) => $builder->where('visibility', $this->resolveVisibility($validated['visibility'])),
            )
            ->when(
                array_key_exists('active', $validated) && $validated['active'] !== null,
                fn (Builder $builder) => $builder->where('is_active', (bool) $validated['active']),
            )
            ->when(
                isset($validated['ids']),
                fn (Builder $builder) => $builder->whereIn('id', $validated['ids']),
            )
            ->when(
                isset($validated['query']),
                fn (Builder $builder) => $this->applyTextSearch($builder, $validated['query']),
            );
    }

    /**
     * @param  Builder<Challenge>  $query
     * @return Builder<Challenge>
     */
    private function applyTextSearch(Builder $query, string $term): Builder
    {
        $like = '%'.$term.'%';

        return $query->where(function (Builder $sub) use ($like): void {
            $sub->where('title', 'like', $like)
                ->orWhere('tagline', 'like', $like);
        });
    }

    /**
     * @param  Builder<Challenge>  $query
     * @param  array<string, mixed>  $validated
     */
    private function paginate(Builder $query, array $validated): CursorPaginator
    {
        $limit = (int) ($validated['limit'] ?? ListChallengesRequest::DEFAULT_LIMIT);

        return $query
            ->orderByDesc('id')
            ->cursorPaginate(perPage: $limit, cursorName: 'cursor', cursor: $validated['cursor'] ?? null);
    }

    /**
     * @param  array<int, ChallengeColumn>  $columns
     * @return array<string, mixed>
     */
    private function formatResponse(CursorPaginator $paginator, int $totalCount, array $columns): array
    {
        $requested = array_map(fn (ChallengeColumn $column): string => $column->value, $columns);

        $fields = [...self::SUMMARY_FIELDS, ...$requested];

        $items = array_map(
            fn (Challenge $challenge): array => ChallengeDetailResource::from($challenge)->only(...$fields)->toArray(),
            $paginator->items(),
        );

        return [
            'items' => $items,
            'total_count' => $totalCount,
            'next_cursor' => $paginator->nextCursor()?->encode(),
        ];
    }

    private function resolveVisibility(string $name): ChallengeVisibility
    {
        foreach (ChallengeVisibility::cases() as $case) {
            if ($case->name === $name) {
                return $case;
            }
        }

        throw new InvalidArgumentException("Unknown challenge visibility [{$name}].");
    }
}
