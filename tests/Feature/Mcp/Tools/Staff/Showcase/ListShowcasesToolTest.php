<?php

use App\Mcp\Servers\StaffServer;
use App\Mcp\Tools\Staff\Showcase\ListShowcasesTool;
use App\Models\PracticeArea;
use App\Models\Showcase\Showcase;

it('returns a condensed index with only the expected fields per item', function (): void {
    $showcase = Showcase::factory()->approved()->create([
        'title' => 'AI Legal Brief Assistant',
        'tagline' => 'Drafts briefs in seconds.',
    ]);

    StaffServer::tool(ListShowcasesTool::class)
        ->assertOk()
        ->assertStructuredContent(function ($json) use ($showcase): bool {
            $json->where('items.0.id', $showcase->id)
                ->where('items.0.slug', $showcase->slug)
                ->where('items.0.title', $showcase->title)
                ->where('items.0.tagline', $showcase->tagline)
                ->where('items.0.status', 'Approved')
                ->where('items.0.submitted_date', $showcase->submitted_date?->toIso8601String())
                ->where('total_count', 1)
                ->where('next_cursor', null);

            $first = $json->toArray()['items'][0];
            expect(array_keys($first))->toEqualCanonicalizing([
                'id', 'slug', 'title', 'tagline', 'status', 'submitted_date',
            ]);

            return true;
        });
});

it('filters by status', function (): void {
    Showcase::factory()->approved()->create();
    $pending = Showcase::factory()->pending()->create();

    StaffServer::tool(ListShowcasesTool::class, ['status' => 'Pending'])
        ->assertOk()
        ->assertStructuredContent(function ($json) use ($pending): bool {
            $json->where('total_count', 1)
                ->where('items.0.id', $pending->id)
                ->where('items.0.status', 'Pending')
                ->etc();

            return true;
        });
});

it('filters by practice area slug', function (): void {
    $area = PracticeArea::factory()->create(['slug' => 'litigation']);

    $other = Showcase::factory()->approved()->create();
    $other->practiceAreas()->sync([]);

    $match = Showcase::factory()->approved()->create();
    $match->practiceAreas()->sync([$area->id]);

    StaffServer::tool(ListShowcasesTool::class, ['practice_area' => 'litigation'])
        ->assertOk()
        ->assertStructuredContent(function ($json) use ($match): bool {
            $json->where('total_count', 1)
                ->where('items.0.id', $match->id)
                ->etc();

            return true;
        });
});

it('filters by text query against title and tagline', function (): void {
    Showcase::factory()->approved()->create(['title' => 'Unrelated', 'tagline' => 'Other thing']);
    $match = Showcase::factory()->approved()->create(['title' => 'Contract Wizard', 'tagline' => 'Magic']);

    StaffServer::tool(ListShowcasesTool::class, ['query' => 'Wizard'])
        ->assertOk()
        ->assertStructuredContent(function ($json) use ($match): bool {
            $json->where('total_count', 1)
                ->where('items.0.id', $match->id)
                ->etc();

            return true;
        });
});

it('paginates results with limit and returns a next_cursor when more remain', function (): void {
    Showcase::factory()->count(3)->approved()->create();

    $first = StaffServer::tool(ListShowcasesTool::class, ['limit' => 2]);

    $cursor = null;
    $first->assertOk()->assertStructuredContent(function ($json) use (&$cursor): bool {
        $json->has('items', 2)
            ->where('total_count', 3)
            ->whereNot('next_cursor', null);

        $cursor = $json->toArray()['next_cursor'];

        return true;
    });

    StaffServer::tool(ListShowcasesTool::class, ['limit' => 2, 'cursor' => $cursor])
        ->assertOk()
        ->assertStructuredContent(function ($json): bool {
            $json->has('items', 1)
                ->where('total_count', 3)
                ->where('next_cursor', null);

            return true;
        });
});

it('rejects invalid input', function (): void {
    StaffServer::tool(ListShowcasesTool::class, ['status' => 'Bogus'])
        ->assertHasErrors();

    StaffServer::tool(ListShowcasesTool::class, ['limit' => 0])
        ->assertHasErrors();
});
