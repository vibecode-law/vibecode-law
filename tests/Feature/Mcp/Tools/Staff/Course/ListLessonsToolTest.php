<?php

use App\Mcp\Servers\StaffServer;
use App\Mcp\Tools\Staff\Course\ListLessonsTool;
use App\Models\Course\Course;
use App\Models\Course\Lesson;
use App\Models\User;

it('returns a condensed lesson index with engagement counts and playback stats', function (): void {
    $lesson = Lesson::factory()->published()->create([
        'title' => 'Intro to Prompts',
        'tagline' => 'First steps.',
        'order' => 1,
        'gated' => true,
        'duration_seconds' => 600,
    ]);

    $started = User::factory()->create();
    $completed = User::factory()->create();
    $lesson->users()->attach($started->id, ['started_at' => now(), 'playback_time_seconds' => 120]);
    $lesson->users()->attach($completed->id, ['started_at' => now()->subDay(), 'completed_at' => now(), 'playback_time_seconds' => 600]);

    StaffServer::tool(ListLessonsTool::class)
        ->assertOk()
        ->assertStructuredContent(function ($json) use ($lesson): bool {
            $json->where('items.0.id', $lesson->id)
                ->where('items.0.slug', $lesson->slug)
                ->where('items.0.title', 'Intro to Prompts')
                ->where('items.0.tagline', 'First steps.')
                ->where('items.0.course_id', $lesson->course_id)
                ->where('items.0.order', 1)
                ->where('items.0.duration_seconds', 600)
                ->where('items.0.gated', true)
                ->where('items.0.publish_date', $lesson->publish_date?->toDateString())
                ->where('items.0.started_count', 2)
                ->where('items.0.completed_count', 1)
                ->where('items.0.total_playback_seconds', 720)
                ->where('items.0.average_playback_seconds', 360.0)
                ->where('items.0.average_completion_percentage', 60.0)
                ->where('total_count', 1)
                ->where('next_cursor', null)
                ->etc();

            return true;
        });
});

it('filters by course_id', function (): void {
    $course = Course::factory()->create();
    $otherCourse = Course::factory()->create();

    $lesson = Lesson::factory()->create(['course_id' => $course->id]);
    Lesson::factory()->create(['course_id' => $otherCourse->id]);

    StaffServer::tool(ListLessonsTool::class, ['course_id' => $course->id])
        ->assertOk()
        ->assertStructuredContent(function ($json) use ($lesson): bool {
            $json->where('total_count', 1)
                ->where('items.0.id', $lesson->id)
                ->etc();

            return true;
        });
});

it('filters by gated', function (): void {
    Lesson::factory()->create(['gated' => true]);
    $ungated = Lesson::factory()->ungated()->create();

    StaffServer::tool(ListLessonsTool::class, ['gated' => false])
        ->assertOk()
        ->assertStructuredContent(function ($json) use ($ungated): bool {
            $json->where('total_count', 1)
                ->where('items.0.id', $ungated->id)
                ->etc();

            return true;
        });
});

it('filters by published', function (): void {
    Lesson::factory()->draft()->create();
    $published = Lesson::factory()->published()->create();

    StaffServer::tool(ListLessonsTool::class, ['published' => true])
        ->assertOk()
        ->assertStructuredContent(function ($json) use ($published): bool {
            $json->where('total_count', 1)
                ->where('items.0.id', $published->id)
                ->etc();

            return true;
        });
});

it('paginates with limit and next_cursor', function (): void {
    Lesson::factory()->count(3)->create();

    $first = StaffServer::tool(ListLessonsTool::class, ['limit' => 2]);

    $cursor = null;
    $first->assertOk()->assertStructuredContent(function ($json) use (&$cursor): bool {
        $json->has('items', 2)
            ->where('total_count', 3)
            ->whereNot('next_cursor', null);

        $cursor = $json->toArray()['next_cursor'];

        return true;
    });

    StaffServer::tool(ListLessonsTool::class, ['limit' => 2, 'cursor' => $cursor])
        ->assertOk()
        ->assertStructuredContent(function ($json): bool {
            $json->has('items', 1)
                ->where('total_count', 3)
                ->where('next_cursor', null);

            return true;
        });
});

it('rejects invalid input', function (): void {
    StaffServer::tool(ListLessonsTool::class, ['limit' => 0])->assertHasErrors();
    StaffServer::tool(ListLessonsTool::class, ['course_id' => 0])->assertHasErrors();
});
