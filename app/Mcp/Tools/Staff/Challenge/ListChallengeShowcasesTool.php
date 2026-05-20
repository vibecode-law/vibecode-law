<?php

namespace App\Mcp\Tools\Staff\Challenge;

use App\Enums\ShowcaseStatus;
use App\Mcp\Requests\Challenge\ListChallengeShowcasesRequest;
use App\Mcp\Schemas\Staff\Challenge\ListChallengeShowcasesSchema;
use App\Mcp\Shapes\Showcase\ShowcaseSummaryResource;
use App\Models\Challenge\Challenge;
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
use Spatie\LaravelData\DataCollection;

#[Name('list_challenge_showcases')]
#[Description('List the showcases attached to a given challenge, returning the condensed showcase summary for each. Supports filtering by showcase status and cursor pagination.')]
class ListChallengeShowcasesTool extends Tool
{
    public function handle(Request $request): Response|ResponseFactory
    {
        $validated = $this->validated($request);

        $challenge = Challenge::query()->find($validated['challenge_id']);

        if ($challenge === null) {
            return Response::error("Challenge with id [{$validated['challenge_id']}] was not found.");
        }

        $relation = $this->buildRelation($challenge, $validated);

        $totalCount = (clone $relation)->count();

        $paginator = $this->paginate($relation, $validated);

        return Response::structured($this->formatResponse($paginator, $totalCount));
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
     */
    private function buildRelation(Challenge $challenge, array $validated): BelongsToMany
    {
        $relation = $challenge->showcases();

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
