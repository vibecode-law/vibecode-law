<?php

namespace App\Mcp\Tools\Staff\Course;

use App\Mcp\Requests\Course\GetLessonRequest;
use App\Mcp\Shapes\Course\LessonDetailResource;
use App\Models\Course\Lesson;
use App\Models\Course\LessonUser;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('get_lesson')]
#[Description('Fetch the full details of a single lesson by its stable id, including engagement counts and aggregate playback time.')]
class GetLessonTool extends Tool
{
    public function handle(Request $request): Response|ResponseFactory
    {
        $validated = $request->validate((new GetLessonRequest)->rules());

        $lesson = Lesson::query()
            ->with('instructors:id')
            ->withCount('users')
            ->withCount([
                'users as users_viewed_count' => fn (Builder $q) => $q->whereNotNull('viewed_at'),
                'users as users_started_count' => fn (Builder $q) => $q->whereNotNull('started_at'),
                'users as users_completed_count' => fn (Builder $q) => $q->whereNotNull('completed_at'),
            ])
            ->find($validated['id']);

        if ($lesson === null) {
            return Response::error("Lesson with id [{$validated['id']}] was not found.");
        }

        $lesson->setAttribute(
            'users_total_playback_seconds',
            (int) LessonUser::query()
                ->where('lesson_id', $lesson->id)
                ->sum('playback_time_seconds'),
        );

        return Response::structured(LessonDetailResource::from($lesson)->toArray());
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
                ->description('The stable lesson id.'),
        ];
    }
}
