<?php

namespace App\Mcp\Tools\Staff\Showcase;

use App\Enums\ShowcaseStatus;
use App\Mcp\Requests\Showcase\ListShowcasesRequest;
use App\Mcp\Schemas\Staff\Showcase\ListShowcasesSchema;
use App\Mcp\Shapes\Showcase\ShowcaseColumn;
use App\Mcp\Shapes\Showcase\ShowcaseDetailResource;
use App\Models\Showcase\Showcase;
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

#[Name('list_showcases')]
#[Description('List showcases as a condensed index. Returns id, slug, title, tagline, status, submitted_date and user_id per entry by default; request additional fields with the columns parameter. Use get_showcase to fetch full details for a single entry. Supports filters (status, practice_area, query, user_id, ids) and cursor pagination.')]
class ListShowcasesTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $validated = $this->validated($request);

        $columns = $this->resolveColumns($validated);

        $query = $this->buildQuery($validated, $columns);

        $totalCount = (clone $query)->toBase()->getCountForPagination();

        $paginator = $this->paginate($query, $validated);

        return Response::structured($this->formatResponse($paginator, $totalCount, $columns));
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return (new ListShowcasesSchema)($schema);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $listRequest = new ListShowcasesRequest;

        return $request->validate($listRequest->rules(), $listRequest->messages());
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<int, ShowcaseColumn>
     */
    private function resolveColumns(array $validated): array
    {
        return array_map(
            fn (string $column): ShowcaseColumn => ShowcaseColumn::from($column),
            $validated['columns'] ?? [],
        );
    }

    /**
     * @param  array<string, mixed>  $validated
     * @param  array<int, ShowcaseColumn>  $columns
     * @return Builder<Showcase>
     */
    private function buildQuery(array $validated, array $columns): Builder
    {
        $relationsToLoad = array_values(array_filter(array_map(
            fn (ShowcaseColumn $column): ?string => $column->relationToLoad(),
            $columns,
        )));

        $relationsToCount = array_values(array_filter(array_map(
            fn (ShowcaseColumn $column): ?string => $column->relationToCount(),
            $columns,
        )));

        return Showcase::query()
            ->with($relationsToLoad)
            ->withCount($relationsToCount)
            ->when(
                isset($validated['status']),
                fn (Builder $builder) => $builder->where('status', $this->resolveStatus($validated['status'])),
            )
            ->when(
                isset($validated['user_id']),
                fn (Builder $builder) => $builder->where('user_id', $validated['user_id']),
            )
            ->when(
                isset($validated['ids']),
                fn (Builder $builder) => $builder->whereIn('id', $validated['ids']),
            )
            ->when(
                isset($validated['practice_area']),
                fn (Builder $builder) => $builder->whereHas(
                    'practiceAreas',
                    fn (Builder $sub) => $sub->where('slug', $validated['practice_area']),
                ),
            )
            ->when(
                isset($validated['query']),
                fn (Builder $builder) => $this->applyTextSearch($builder, $validated['query']),
            );
    }

    /**
     * @param  Builder<Showcase>  $query
     * @return Builder<Showcase>
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
     * @param  Builder<Showcase>  $query
     * @param  array<string, mixed>  $validated
     */
    private function paginate(Builder $query, array $validated): CursorPaginator
    {
        $limit = (int) ($validated['limit'] ?? ListShowcasesRequest::DEFAULT_LIMIT);

        return $query
            ->orderByDesc('id')
            ->cursorPaginate(perPage: $limit, cursorName: 'cursor', cursor: $validated['cursor'] ?? null);
    }

    /**
     * @param  array<int, ShowcaseColumn>  $columns
     * @return array<string, mixed>
     */
    private function formatResponse(CursorPaginator $paginator, int $totalCount, array $columns): array
    {
        $items = array_map(
            fn (Showcase $showcase): array => $this->formatItem($showcase, $columns),
            $paginator->items(),
        );

        return [
            'items' => $items,
            'total_count' => $totalCount,
            'next_cursor' => $paginator->nextCursor()?->encode(),
        ];
    }

    /**
     * Render a showcase using the detail resource, including only the
     * additional columns that were requested. The summary fields are always
     * rendered; every other field is lazy and resolved only when included.
     *
     * @param  array<int, ShowcaseColumn>  $columns
     * @return array<string, mixed>
     */
    private function formatItem(Showcase $showcase, array $columns): array
    {
        $requested = array_map(fn (ShowcaseColumn $column): string => $column->value, $columns);

        return ShowcaseDetailResource::from($showcase)->include(...$requested)->toArray();
    }

    private function resolveStatus(string $name): ShowcaseStatus
    {
        foreach (ShowcaseStatus::cases() as $case) {
            if ($case->name === $name) {
                return $case;
            }
        }

        throw new InvalidArgumentException("Unknown showcase status [{$name}].");
    }
}
