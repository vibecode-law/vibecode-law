<?php

use App\Enums\ExperienceLevel;
use App\Mcp\Servers\StaffServer;
use App\Mcp\Tools\Staff\Course\ListCoursesTool;
use App\Models\Course\Course;
use App\Models\Course\Lesson;
use App\Models\User;

it('returns only the default summary fields', function (): void {
    $course = Course::factory()->published()->create([
        'title' => 'Prompting Fundamentals',
        'tagline' => 'Talk to LLMs well.',
    ]);

    StaffServer::tool(ListCoursesTool::class)
        ->assertOk()
        ->assertStructuredContent(function ($json) use ($course): bool {
            $json->where('items.0.id', $course->id)
                ->where('items.0.slug', $course->slug)
                ->where('items.0.title', 'Prompting Fundamentals')
                ->where('items.0.tagline', 'Talk to LLMs well.')
                ->where('items.0.publish_date', $course->publish_date?->toDateString())
                ->where('total_count', 1)
                ->where('next_cursor', null);

            $first = $json->toArray()['items'][0];
            expect(array_keys($first))->toEqualCanonicalizing([
                'id', 'slug', 'title', 'tagline', 'publish_date',
            ]);

            return true;
        });
});

it('returns engagement counts and metadata when requested as columns', function (): void {
    $course = Course::factory()->published()->create([
        'experience_level' => ExperienceLevel::Foundation,
        'is_featured' => true,
        'duration_seconds' => 1800,
    ]);

    Lesson::factory()->count(2)->create(['course_id' => $course->id]);
    $course->refresh();

    $started = User::factory()->create();
    $completed = User::factory()->create();
    $course->users()->attach($started->id, ['started_at' => now()]);
    $course->users()->attach($completed->id, ['started_at' => now()->subDay(), 'completed_at' => now()]);

    StaffServer::tool(ListCoursesTool::class, [
        'columns' => ['experience_level', 'is_featured', 'duration_seconds', 'lessons_count', 'started_count', 'completed_count'],
    ])
        ->assertOk()
        ->assertStructuredContent(function ($json) use ($course): bool {
            $json->where('items.0.id', $course->id)
                ->where('items.0.experience_level', 'Foundation')
                ->where('items.0.is_featured', true)
                ->where('items.0.duration_seconds', $course->duration_seconds)
                ->where('items.0.lessons_count', 2)
                ->where('items.0.started_count', 2)
                ->where('items.0.completed_count', 1)
                ->where('total_count', 1)
                ->where('next_cursor', null);

            $first = $json->toArray()['items'][0];
            expect(array_keys($first))->toEqualCanonicalizing([
                'id', 'slug', 'title', 'tagline', 'publish_date',
                'experience_level', 'is_featured', 'duration_seconds', 'lessons_count', 'started_count', 'completed_count',
            ]);

            return true;
        });
});

it('filters by a list of ids', function (): void {
    $first = Course::factory()->create();
    Course::factory()->create();
    $third = Course::factory()->create();

    StaffServer::tool(ListCoursesTool::class, ['ids' => [$first->id, $third->id]])
        ->assertOk()
        ->assertStructuredContent(function ($json) use ($first, $third): bool {
            $json->where('total_count', 2)
                ->where('items.0.id', $third->id)
                ->where('items.1.id', $first->id)
                ->etc();

            return true;
        });
});

it('returns additional columns when requested', function (): void {
    $course = Course::factory()->create([
        'description' => 'Deep dive.',
        'allow_preview' => true,
    ]);
    $course->users()->attach(User::factory()->create()->id, ['viewed_at' => now()]);
    $course->users()->attach(User::factory()->create()->id, ['started_at' => now()]);

    StaffServer::tool(ListCoursesTool::class, ['columns' => ['description', 'allow_preview', 'viewed_count', 'enrolled_count']])
        ->assertOk()
        ->assertStructuredContent(function ($json) use ($course): bool {
            $json->where('items.0.id', $course->id)
                ->where('items.0.description', 'Deep dive.')
                ->where('items.0.allow_preview', true)
                ->where('items.0.viewed_count', 1)
                ->where('items.0.enrolled_count', 2)
                ->where('total_count', 1)
                ->where('next_cursor', null);

            $first = $json->toArray()['items'][0];
            expect(array_keys($first))->toEqualCanonicalizing([
                'id', 'slug', 'title', 'tagline', 'publish_date',
                'description', 'allow_preview', 'viewed_count', 'enrolled_count',
            ]);

            return true;
        });
});

it('rejects unknown columns', function (): void {
    StaffServer::tool(ListCoursesTool::class, ['columns' => ['not_a_column']])->assertHasErrors();
});

it('filters by experience_level', function (): void {
    Course::factory()->create(['experience_level' => ExperienceLevel::Foundation]);
    $advanced = Course::factory()->create(['experience_level' => ExperienceLevel::Advanced]);

    StaffServer::tool(ListCoursesTool::class, ['experience_level' => 'Advanced'])
        ->assertOk()
        ->assertStructuredContent(function ($json) use ($advanced): bool {
            $json->where('total_count', 1)
                ->where('items.0.id', $advanced->id)
                ->etc();

            return true;
        });
});

it('filters by published', function (): void {
    Course::factory()->draft()->create();
    $published = Course::factory()->published()->create();

    StaffServer::tool(ListCoursesTool::class, ['published' => true])
        ->assertOk()
        ->assertStructuredContent(function ($json) use ($published): bool {
            $json->where('total_count', 1)
                ->where('items.0.id', $published->id)
                ->etc();

            return true;
        });
});

it('filters by is_featured', function (): void {
    Course::factory()->create(['is_featured' => false]);
    $featured = Course::factory()->create(['is_featured' => true]);

    StaffServer::tool(ListCoursesTool::class, ['is_featured' => true])
        ->assertOk()
        ->assertStructuredContent(function ($json) use ($featured): bool {
            $json->where('total_count', 1)
                ->where('items.0.id', $featured->id)
                ->etc();

            return true;
        });
});

it('filters by text query', function (): void {
    Course::factory()->create(['title' => 'Unrelated', 'tagline' => 'Other']);
    $match = Course::factory()->create(['title' => 'Vibe Coding Mastery', 'tagline' => 'Tips']);

    StaffServer::tool(ListCoursesTool::class, ['query' => 'Vibe'])
        ->assertOk()
        ->assertStructuredContent(function ($json) use ($match): bool {
            $json->where('total_count', 1)
                ->where('items.0.id', $match->id)
                ->etc();

            return true;
        });
});

it('paginates with limit and next_cursor', function (): void {
    Course::factory()->count(3)->create();

    $first = StaffServer::tool(ListCoursesTool::class, ['limit' => 2]);

    $cursor = null;
    $first->assertOk()->assertStructuredContent(function ($json) use (&$cursor): bool {
        $json->has('items', 2)
            ->where('total_count', 3)
            ->whereNot('next_cursor', null);

        $cursor = $json->toArray()['next_cursor'];

        return true;
    });

    StaffServer::tool(ListCoursesTool::class, ['limit' => 2, 'cursor' => $cursor])
        ->assertOk()
        ->assertStructuredContent(function ($json): bool {
            $json->has('items', 1)
                ->where('total_count', 3)
                ->where('next_cursor', null);

            return true;
        });
});

it('rejects invalid input', function (): void {
    StaffServer::tool(ListCoursesTool::class, ['experience_level' => 'Bogus'])->assertHasErrors();
    StaffServer::tool(ListCoursesTool::class, ['limit' => 0])->assertHasErrors();
});
