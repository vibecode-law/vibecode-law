<?php

namespace App\Mcp\Tools\Staff\Challenge;

use App\Enums\ShowcaseStatus;
use App\Mcp\Requests\Challenge\ListChallengeShowcasesRequest;
use App\Mcp\Schemas\Staff\Challenge\ListChallengeShowcasesSchema;
use App\Mcp\Shapes\Showcase\ShowcaseColumn;
use App\Mcp\Shapes\Showcase\ShowcaseDetailResource;
use App\Models\Challenge\Challenge;
use App\Models\Showcase\Showcase;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use InvalidArgumentException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('list_challenge_showcases')]
#[Description('List the showcases attached to a given challenge. Returns id, slug, title, tagline, status and submitted_date per entry by default; request additional fields with the columns parameter. Supports filtering by showcase status and cursor pagination.')]
class ListChallengeShowcasesTool extends Tool
{
    public function handle(Request $request): Response|ResponseFactory
    {
        $validated = $this->validated($request);

        $challenge = Challenge::query()->find($validated['challenge_id']);

        if ($challenge === null) {
            return Response::error("Challenge with id [{$validated['challenge_id']}] was not found.");
        }

        $columns = $this->resolveColumns($validated);

        $relation = $this->buildRelation($challenge, $validated, $columns);

        $totalCount = (clone $relation)->count();

        $paginator = $this->paginate($relation, $validated);

        return Response::structured($this->formatResponse($paginator, $totalCount, $columns));
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return (new ListChallengeShowcasesSchema)($schema);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $listRequest = new ListChallengeShowcasesRequest;

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
     */
    private function buildRelation(Challenge $challenge, array $validated, array $columns): BelongsToMany
    {
        $relationsToLoad = array_values(array_filter(array_map(
            fn (ShowcaseColumn $column): ?string => $column->relationToLoad(),
            $columns,
        )));

        $relationsToCount = array_values(array_filter(array_map(
            fn (ShowcaseColumn $column): ?string => $column->relationToCount(),
            $columns,
        )));

        $relation = $challenge->showcases()
            ->with($relationsToLoad)
            ->withCount($relationsToCount);

        if (isset($validated['status'])) {
            $relation->where('showcases.status', $this->resolveStatus($validated['status']));
        }

        return $relation;
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function paginate(BelongsToMany $relation, array $validated): CursorPaginator
    {
        $limit = (int) ($validated['limit'] ?? ListChallengeShowcasesRequest::DEFAULT_LIMIT);

        return $relation
            ->orderByDesc('showcases.id')
            ->cursorPaginate(perPage: $limit, cursorName: 'cursor', cursor: $validated['cursor'] ?? null);
    }

    /**
     * @param  array<int, ShowcaseColumn>  $columns
     * @return array<string, mixed>
     */
    private function formatResponse(CursorPaginator $paginator, int $totalCount, array $columns): array
    {
        $requested = array_map(fn (ShowcaseColumn $column): string => $column->value, $columns);

        $items = array_map(
            fn (Showcase $showcase): array => ShowcaseDetailResource::from($showcase)->include(...$requested)->toArray(),
            $paginator->items(),
        );

        return [
            'items' => $items,
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
