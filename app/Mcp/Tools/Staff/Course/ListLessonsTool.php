<?php

namespace App\Mcp\Tools\Staff\Course;

use App\Mcp\Requests\Course\ListLessonsRequest;
use App\Mcp\Schemas\Staff\Course\ListLessonsSchema;
use App\Mcp\Shapes\Course\LessonSummaryResource;
use App\Models\Course\Lesson;
use App\Models\Course\LessonUser;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Spatie\LaravelData\DataCollection;

#[Name('list_lessons')]
#[Description('List lessons as a condensed index with engagement counts. Returns id, slug, title, tagline, course_id, order, duration_seconds, gated, allow_preview, publish_date, started_count and completed_count per entry. Use get_lesson to fetch full details. Supports filters (course_id, published, gated, query) and cursor pagination.')]
class ListLessonsTool extends Tool
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
        return (new ListLessonsSchema)($schema);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $listRequest = new ListLessonsRequest;

        return $request->validate($listRequest->rules(), $listRequest->messages());
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return Builder<Lesson>
     */
    private function buildQuery(array $validated): Builder
    {
        return Lesson::query()
            ->withCount('users')
            ->withCount([
                'users as users_started_count' => fn (Builder $q) => $q->whereNotNull('started_at'),
                'users as users_completed_count' => fn (Builder $q) => $q->whereNotNull('completed_at'),
            ])
            ->addSelect([
                'users_total_playback_seconds' => LessonUser::query()
                    ->selectRaw('coalesce(sum(playback_time_seconds), 0)')
                    ->whereColumn('lesson_id', 'lessons.id'),
            ])
            ->when(
                isset($validated['course_id']),
                fn (Builder $builder) => $builder->where('course_id', (int) $validated['course_id']),
            )
            ->when(
                array_key_exists('gated', $validated) && $validated['gated'] !== null,
                fn (Builder $builder) => $builder->where('gated', (bool) $validated['gated']),
            )
            ->when(
                array_key_exists('published', $validated) && $validated['published'] !== null,
                fn (Builder $builder) => $this->applyPublishedFilter($builder, (bool) $validated['published']),
            )
            ->when(
                isset($validated['query']),
                fn (Builder $builder) => $this->applyTextSearch($builder, $validated['query']),
            );
    }

    /**
     * @param  Builder<Lesson>  $query
     * @return Builder<Lesson>
     */
    private function applyPublishedFilter(Builder $query, bool $published): Builder
    {
        if ($published === true) {
            return $query->whereNotNull('publish_date')->where('publish_date', '<=', now());
        }

        return $query->where(fn (Builder $sub) => $sub->whereNull('publish_date')->orWhere('publish_date', '>', now()));
    }

    /**
     * @param  Builder<Lesson>  $query
     * @return Builder<Lesson>
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
     * @param  Builder<Lesson>  $query
     * @param  array<string, mixed>  $validated
     */
    private function paginate(Builder $query, array $validated): CursorPaginator
    {
        $limit = (int) ($validated['limit'] ?? ListLessonsRequest::DEFAULT_LIMIT);

        return $query
            ->orderBy('course_id')
            ->orderBy('order')
            ->orderBy('id')
            ->cursorPaginate(perPage: $limit, cursorName: 'cursor', cursor: $validated['cursor'] ?? null);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatResponse(CursorPaginator $paginator, int $totalCount): array
    {
        return [
            'items' => LessonSummaryResource::collect($paginator->items(), DataCollection::class)->toArray(),
            'total_count' => $totalCount,
            'next_cursor' => $paginator->nextCursor()?->encode(),
        ];
    }
}
