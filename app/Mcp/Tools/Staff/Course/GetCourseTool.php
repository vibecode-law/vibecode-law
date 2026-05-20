<?php

namespace App\Mcp\Tools\Staff\Course;

use App\Mcp\Requests\Course\GetCourseRequest;
use App\Mcp\Shapes\Course\CourseDetailResource;
use App\Models\Course\Course;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('get_course')]
#[Description('Fetch the full details of a single course by its stable id, including engagement counts (viewed, started, completed, enrolled).')]
class GetCourseTool extends Tool
{
    public function handle(Request $request): Response|ResponseFactory
    {
        $validated = $request->validate((new GetCourseRequest)->rules());

        $course = Course::query()
            ->withCount('lessons')
            ->withCount('users')
            ->withCount([
                'users as users_viewed_count' => fn (Builder $q) => $q->whereNotNull('viewed_at'),
                'users as users_started_count' => fn (Builder $q) => $q->whereNotNull('started_at'),
                'users as users_completed_count' => fn (Builder $q) => $q->whereNotNull('completed_at'),
            ])
            ->find($validated['id']);

        if ($course === null) {
            return Response::error("Course with id [{$validated['id']}] was not found.");
        }

        return Response::structured(CourseDetailResource::from($course)->toArray());
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()
                ->min(1)
                ->required()
                ->description('The stable course id.'),
        ];
    }
}
