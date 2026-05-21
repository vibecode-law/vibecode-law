<?php

use App\Mcp\Servers\StaffServer;
use App\Mcp\Tools\Staff\Course\ListLessonsTool;
use App\Models\Course\Course;
use App\Models\Course\Lesson;
use App\Models\User;

it('returns only the default summary fields', function (): void {
    $lesson = Lesson::factory()->published()->create([
        'title' => 'Intro to Prompts',
        'tagline' => 'First steps.',
    ]);

    StaffServer::tool(ListLessonsTool::class)
        ->assertOk()
        ->assertStructuredContent(function ($json) use ($lesson): bool {
            $json->where('items.0.id', $lesson->id)
                ->where('items.0.slug', $lesson->slug)
                ->where('items.0.title', 'Intro to Prompts')
                ->where('items.0.tagline', 'First steps.')
                ->where('items.0.course_id', $lesson->course_id)
                ->where('items.0.publish_date', $lesson->publish_date?->toDateString())
                ->where('total_count', 1)
                ->where('next_cursor', null);

            $first = $json->toArray()['items'][0];
            expect(array_keys($first))->toEqualCanonicalizing([
                'id', 'slug', 'title', 'tagline', 'course_id', 'publish_date',
            ]);

            return true;
        });
});

it('returns engagement counts and playback stats when requested as columns', function (): void {
    $lesson = Lesson::factory()->published()->create([
        'order' => 1,
        'gated' => true,
        'duration_seconds' => 600,
    ]);

    $started = User::factory()->create();
    $completed = User::factory()->create();
    $lesson->users()->attach($started->id, ['started_at' => now(), 'playback_time_seconds' => 120]);
    $lesson->users()->attach($completed->id, ['started_at' => now()->subDay(), 'completed_at' => now(), 'playback_time_seconds' => 600]);

    StaffServer::tool(ListLessonsTool::class, [
        'columns' => ['order', 'duration_seconds', 'gated', 'started_count', 'completed_count', 'total_playback_seconds', 'average_playback_seconds', 'average_completion_percentage'],
    ])
        ->assertOk()
        ->assertStructuredContent(function ($json) use ($lesson): bool {
            $json->where('items.0.id', $lesson->id)
                ->where('items.0.order', 1)
                ->where('items.0.duration_seconds', 600)
                ->where('items.0.gated', true)
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

it('filters by a list of ids', function (): void {
    $first = Lesson::factory()->create();
    Lesson::factory()->create();
    $third = Lesson::factory()->create();

    StaffServer::tool(ListLessonsTool::class, ['ids' => [$first->id, $third->id]])
        ->assertOk()
        ->assertStructuredContent(function ($json) use ($first, $third): bool {
            $ids = collect($json->toArray()['items'])->pluck('id')->all();
            expect($ids)->toEqualCanonicalizing([$first->id, $third->id]);

            $json->where('total_count', 2)->etc();

            return true;
        });
});

it('returns additional columns when requested', function (): void {
    $lesson = Lesson::factory()->create(['description' => 'Lesson body.']);
    $instructor = User::factory()->create();
    $lesson->instructors()->attach($instructor);
    $lesson->users()->attach(User::factory()->create()->id, ['viewed_at' => now()]);

    StaffServer::tool(ListLessonsTool::class, ['columns' => ['description', 'viewed_count', 'enrolled_count', 'instructor_user_ids']])
        ->assertOk()
        ->assertStructuredContent(function ($json) use ($lesson, $instructor): bool {
            $json->where('items.0.id', $lesson->id)
                ->where('items.0.description', 'Lesson body.')
                ->where('items.0.viewed_count', 1)
                ->where('items.0.enrolled_count', 1)
                ->where('items.0.instructor_user_ids', [$instructor->id])
                ->where('total_count', 1)
                ->where('next_cursor', null)
                ->etc();

            return true;
        });
});

it('rejects unknown columns', function (): void {
    StaffServer::tool(ListLessonsTool::class, ['columns' => ['not_a_column']])->assertHasErrors();
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
