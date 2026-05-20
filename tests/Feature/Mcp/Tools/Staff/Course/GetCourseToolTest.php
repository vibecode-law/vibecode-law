<?php

use App\Enums\ExperienceLevel;
use App\Mcp\Servers\StaffServer;
use App\Mcp\Tools\Staff\Course\GetCourseTool;
use App\Models\Course\Course;
use App\Models\Course\Lesson;
use App\Models\User;

it('returns the full details with engagement counts', function (): void {
    $course = Course::factory()->published()->create([
        'title' => 'Prompting Fundamentals',
        'tagline' => 'Tagline.',
        'experience_level' => ExperienceLevel::Foundation,
        'is_featured' => true,
        'duration_seconds' => 1800,
    ]);

    Lesson::factory()->count(3)->create(['course_id' => $course->id]);
    $course->refresh();

    $viewer = User::factory()->create();
    $started = User::factory()->create();
    $completed = User::factory()->create();
    $course->users()->attach($viewer->id, ['viewed_at' => now()]);
    $course->users()->attach($started->id, ['viewed_at' => now()->subHour(), 'started_at' => now()]);
    $course->users()->attach($completed->id, [
        'viewed_at' => now()->subDay(),
        'started_at' => now()->subDay(),
        'completed_at' => now(),
    ]);

    StaffServer::tool(GetCourseTool::class, ['id' => $course->id])
        ->assertOk()
        ->assertStructuredContent(function ($json) use ($course): bool {
            $json->where('id', $course->id)
                ->where('slug', $course->slug)
                ->where('title', 'Prompting Fundamentals')
                ->where('tagline', 'Tagline.')
                ->where('description', $course->description)
                ->where('learning_objectives', $course->learning_objectives)
                ->where('experience_level', 'Foundation')
                ->where('is_featured', true)
                ->where('publish_date', $course->publish_date?->toDateString())
                ->where('duration_seconds', $course->duration_seconds)
                ->where('thumbnail_url', null)
                ->where('lessons_count', 3)
                ->where('viewed_count', 3)
                ->where('started_count', 2)
                ->where('completed_count', 1)
                ->where('enrolled_count', 3)
                ->where('created_at', $course->created_at?->toIso8601String())
                ->where('updated_at', $course->updated_at?->toIso8601String())
                ->etc();

            return true;
        });
});

it('returns an error response when the id does not exist', function (): void {
    StaffServer::tool(GetCourseTool::class, ['id' => 999999])
        ->assertHasErrors(['Course with id [999999] was not found.']);
});

it('rejects invalid input', function (): void {
    StaffServer::tool(GetCourseTool::class, [])->assertHasErrors();
    StaffServer::tool(GetCourseTool::class, ['id' => 0])->assertHasErrors();
    StaffServer::tool(GetCourseTool::class, ['id' => 'abc'])->assertHasErrors();
});
