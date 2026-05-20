<?php

namespace App\Mcp\Tools\Staff\Showcase;

use App\Enums\ShowcaseStatus;
use App\Mcp\Requests\Showcase\ListShowcasesRequest;
use App\Mcp\Schemas\Staff\Showcase\ListShowcasesSchema;
use App\Mcp\Shapes\Showcase\ShowcaseSummaryResource;
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
use Spatie\LaravelData\DataCollection;

#[Name('list_showcases')]
#[Description('List showcases as a condensed index. Returns only id, slug, title, tagline, status and submitted_date per entry. Use get_showcase to fetch full details for a specific entry. Supports filters (status, practice_area, query) and cursor pagination.')]
class ListShowcasesTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $validated = $this->validated($request);

        $query = $this->buildQuery($validated);

        $totalCount = (clone $query)->toBase()->getCountForPagination();

        $paginator = $this->paginate($query, $validated);

        return Response::structured($this->formatResponse($paginator, $totalCount));
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
     * @return Builder<Showcase>
     */
    private function buildQuery(array $validated): Builder
    {
        return Showcase::query()
            ->when(
                isset($validated['status']),
                fn (Builder $builder) => $builder->where('status', $this->resolveStatus($validated['status'])),
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
     * @return array<string, mixed>
     */
    private function formatResponse(CursorPaginator $paginator, int $totalCount): array
    {
        return [
            'items' => ShowcaseSummaryResource::collect($paginator->items(), DataCollection::class)->toArray(),
            'total_count' => $totalCount,
            'next_cursor' => $paginator->nextCursor()?->encode(),
        ];
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
