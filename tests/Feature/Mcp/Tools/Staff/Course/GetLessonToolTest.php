<?php

use App\Enums\VideoHost;
use App\Mcp\Servers\StaffServer;
use App\Mcp\Tools\Staff\Course\GetLessonTool;
use App\Models\Course\Lesson;
use App\Models\User;

it('returns the full details with engagement counts and aggregate playback', function (): void {
    $lesson = Lesson::factory()->published()->create([
        'title' => 'Intro to Prompts',
        'tagline' => 'First steps.',
        'order' => 1,
        'gated' => true,
        'duration_seconds' => 600,
        'host' => VideoHost::Mux,
    ]);

    $instructor = User::factory()->create();
    $lesson->instructors()->attach($instructor);

    $viewer = User::factory()->create();
    $started = User::factory()->create();
    $completed = User::factory()->create();
    $lesson->users()->attach($viewer->id, ['viewed_at' => now(), 'playback_time_seconds' => 30]);
    $lesson->users()->attach($started->id, ['viewed_at' => now()->subHour(), 'started_at' => now(), 'playback_time_seconds' => 200]);
    $lesson->users()->attach($completed->id, [
        'viewed_at' => now()->subDay(),
        'started_at' => now()->subDay(),
        'completed_at' => now(),
        'playback_time_seconds' => 600,
    ]);

    StaffServer::tool(GetLessonTool::class, ['id' => $lesson->id])
        ->assertOk()
        ->assertStructuredContent(function ($json) use ($lesson, $instructor): bool {
            $json->where('id', $lesson->id)
                ->where('slug', $lesson->slug)
                ->where('title', 'Intro to Prompts')
                ->where('tagline', 'First steps.')
                ->where('description', $lesson->description)
                ->where('learning_objectives', $lesson->learning_objectives)
                ->where('course_id', $lesson->course_id)
                ->where('order', 1)
                ->where('duration_seconds', 600)
                ->where('gated', true)
                ->where('publish_date', $lesson->publish_date?->toDateString())
                ->where('host', 'Mux')
                ->where('thumbnail_url', null)
                ->where('viewed_count', 3)
                ->where('started_count', 2)
                ->where('completed_count', 1)
                ->where('enrolled_count', 3)
                ->where('total_playback_seconds', 830)
                ->where('average_playback_seconds', round(830 / 3, 2))
                ->where('average_completion_percentage', min(100.0, round(round(830 / 3, 2) / 600 * 100, 2)))
                ->where('instructor_user_ids', [$instructor->id])
                ->where('created_at', $lesson->created_at?->toIso8601String())
                ->where('updated_at', $lesson->updated_at?->toIso8601String())
                ->etc();

            return true;
        });
});

it('returns null average_playback_seconds when no one is enrolled', function (): void {
    $lesson = Lesson::factory()->create();

    StaffServer::tool(GetLessonTool::class, ['id' => $lesson->id])
        ->assertOk()
        ->assertStructuredContent(function ($json): bool {
            $json->where('enrolled_count', 0)
                ->where('total_playback_seconds', 0)
                ->where('average_playback_seconds', null)
                ->where('average_completion_percentage', null)
                ->etc();

            return true;
        });
});

it('returns an error response when the id does not exist', function (): void {
    StaffServer::tool(GetLessonTool::class, ['id' => 999999])
        ->assertHasErrors(['Lesson with id [999999] was not found.']);
});

it('rejects invalid input', function (): void {
    StaffServer::tool(GetLessonTool::class, [])->assertHasErrors();
    StaffServer::tool(GetLessonTool::class, ['id' => 0])->assertHasErrors();
    StaffServer::tool(GetLessonTool::class, ['id' => 'abc'])->assertHasErrors();
});
