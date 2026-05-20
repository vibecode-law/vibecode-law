<?php

namespace App\Mcp\Tools\Staff\Showcase;

use App\Mcp\Requests\Showcase\ListShowcaseUpvotersRequest;
use App\Mcp\Schemas\Staff\Showcase\ListShowcaseUpvotersSchema;
use App\Mcp\Shapes\User\UserSummaryResource;
use App\Models\Showcase\Showcase;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Spatie\LaravelData\DataCollection;

#[Name('list_showcase_upvoters')]
#[Description('List the users who have upvoted a given showcase, returning the full user profile for each. Supports cursor pagination.')]
class ListShowcaseUpvotersTool extends Tool
{
    public function handle(Request $request): Response|ResponseFactory
    {
        $validated = $this->validated($request);

        $showcase = Showcase::query()->find($validated['showcase_id']);

        if ($showcase === null) {
            return Response::error("Showcase with id [{$validated['showcase_id']}] was not found.");
        }

        $totalCount = $showcase->upvoters()->count();

        $paginator = $this->paginate($showcase, $validated);

        return Response::structured($this->formatResponse($paginator, $totalCount));
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return (new ListShowcaseUpvotersSchema)($schema);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $upvotersRequest = new ListShowcaseUpvotersRequest;

        return $request->validate($upvotersRequest->rules(), $upvotersRequest->messages());
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function paginate(Showcase $showcase, array $validated): CursorPaginator
    {
        $limit = (int) ($validated['limit'] ?? ListShowcaseUpvotersRequest::DEFAULT_LIMIT);

        return $showcase->upvoters()
            ->orderByDesc('users.id')
            ->cursorPaginate(perPage: $limit, cursorName: 'cursor', cursor: $validated['cursor'] ?? null);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatResponse(CursorPaginator $paginator, int $totalCount): array
    {
        return [
            'items' => UserSummaryResource::collect($paginator->items(), DataCollection::class)->toArray(),
            'total_count' => $totalCount,
            'next_cursor' => $paginator->nextCursor()?->encode(),
        ];
    }
}
