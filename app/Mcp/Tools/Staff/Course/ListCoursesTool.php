<?php

namespace App\Mcp\Tools\Staff\Course;

use App\Enums\ExperienceLevel;
use App\Mcp\Requests\Course\ListCoursesRequest;
use App\Mcp\Schemas\Staff\Course\ListCoursesSchema;
use App\Mcp\Shapes\Course\CourseColumn;
use App\Mcp\Shapes\Course\CourseDetailResource;
use App\Models\Course\Course;
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

#[Name('list_courses')]
#[Description('List courses as a condensed index. Returns id, slug, title, tagline and publish_date per entry by default; request additional fields (including engagement counts) with the columns parameter. Use get_course to fetch full details. Supports filters (experience_level, is_featured, published, query, ids) and cursor pagination.')]
class ListCoursesTool extends Tool
{
    /**
     * Fields returned for every course regardless of the requested columns.
     *
     * @var array<int, string>
     */
    public const SUMMARY_FIELDS = ['id', 'slug', 'title', 'tagline', 'publish_date'];

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
        return (new ListCoursesSchema)($schema);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $listRequest = new ListCoursesRequest;

        return $request->validate($listRequest->rules(), $listRequest->messages());
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<int, CourseColumn>
     */
    private function resolveColumns(array $validated): array
    {
        return array_map(
            fn (string $column): CourseColumn => CourseColumn::from($column),
            $validated['columns'] ?? [],
        );
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return Builder<Course>
     */
    private function buildQuery(array $validated): Builder
    {
        return Course::query()
            ->withCount('lessons')
            ->withCount('users')
            ->withCount([
                'users as users_viewed_count' => fn (Builder $q) => $q->whereNotNull('viewed_at'),
                'users as users_started_count' => fn (Builder $q) => $q->whereNotNull('started_at'),
                'users as users_completed_count' => fn (Builder $q) => $q->whereNotNull('completed_at'),
            ])
            ->when(
                isset($validated['experience_level']),
                fn (Builder $builder) => $builder->where('experience_level', $this->resolveExperienceLevel($validated['experience_level'])),
            )
            ->when(
                array_key_exists('is_featured', $validated) && $validated['is_featured'] !== null,
                fn (Builder $builder) => $builder->where('is_featured', (bool) $validated['is_featured']),
            )
            ->when(
                array_key_exists('published', $validated) && $validated['published'] !== null,
                fn (Builder $builder) => $this->applyPublishedFilter($builder, (bool) $validated['published']),
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
     * @param  Builder<Course>  $query
     * @return Builder<Course>
     */
    private function applyPublishedFilter(Builder $query, bool $published): Builder
    {
        if ($published === true) {
            return $query->whereNotNull('publish_date')->where('publish_date', '<=', now());
        }

        return $query->where(fn (Builder $sub) => $sub->whereNull('publish_date')->orWhere('publish_date', '>', now()));
    }

    /**
     * @param  Builder<Course>  $query
     * @return Builder<Course>
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
     * @param  Builder<Course>  $query
     * @param  array<string, mixed>  $validated
     */
    private function paginate(Builder $query, array $validated): CursorPaginator
    {
        $limit = (int) ($validated['limit'] ?? ListCoursesRequest::DEFAULT_LIMIT);

        return $query
            ->orderByDesc('id')
            ->cursorPaginate(perPage: $limit, cursorName: 'cursor', cursor: $validated['cursor'] ?? null);
    }

    /**
     * @param  array<int, CourseColumn>  $columns
     * @return array<string, mixed>
     */
    private function formatResponse(CursorPaginator $paginator, int $totalCount, array $columns): array
    {
        $requested = array_map(fn (CourseColumn $column): string => $column->value, $columns);

        $fields = [...self::SUMMARY_FIELDS, ...$requested];

        $items = array_map(
            fn (Course $course): array => CourseDetailResource::from($course)->only(...$fields)->toArray(),
            $paginator->items(),
        );

        return [
            'items' => $items,
            'total_count' => $totalCount,
            'next_cursor' => $paginator->nextCursor()?->encode(),
        ];
    }

    private function resolveExperienceLevel(string $name): ExperienceLevel
    {
        foreach (ExperienceLevel::cases() as $case) {
            if ($case->name === $name) {
                return $case;
            }
        }

        throw new InvalidArgumentException("Unknown experience level [{$name}].");
    }
}
