<?php

use App\Mcp\Servers\StaffServer;
use App\Mcp\Tools\Staff\Showcase\ListShowcasesTool;
use App\Models\Challenge\Challenge;
use App\Models\PracticeArea;
use App\Models\Showcase\Showcase;
use App\Models\User;

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
                ->where('items.0.user_id', $showcase->user_id)
                ->where('total_count', 1)
                ->where('next_cursor', null);

            $first = $json->toArray()['items'][0];
            expect(array_keys($first))->toEqualCanonicalizing([
                'id', 'slug', 'title', 'tagline', 'status', 'submitted_date', 'user_id',
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

it('filters by a list of ids', function (): void {
    $first = Showcase::factory()->approved()->create();
    Showcase::factory()->approved()->create();
    $third = Showcase::factory()->approved()->create();

    StaffServer::tool(ListShowcasesTool::class, ['ids' => [$first->id, $third->id]])
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
    $area = PracticeArea::factory()->create();
    $showcase = Showcase::factory()->approved()->create([
        'description' => 'A detailed description.',
        'url' => 'https://example.test',
    ]);
    $showcase->practiceAreas()->sync([$area->id]);
    $showcase->upvoters()->sync([User::factory()->create()->id]);

    StaffServer::tool(ListShowcasesTool::class, ['columns' => ['description', 'url', 'upvote_count', 'practice_areas']])
        ->assertOk()
        ->assertStructuredContent(function ($json) use ($showcase, $area): bool {
            $json->where('items.0.id', $showcase->id)
                ->where('items.0.slug', $showcase->slug)
                ->where('items.0.title', $showcase->title)
                ->where('items.0.tagline', $showcase->tagline)
                ->where('items.0.status', 'Approved')
                ->where('items.0.submitted_date', $showcase->submitted_date?->toIso8601String())
                ->where('items.0.description', 'A detailed description.')
                ->where('items.0.url', 'https://example.test')
                ->where('items.0.upvote_count', 1)
                ->where('items.0.practice_areas.0.id', $area->id)
                ->where('total_count', 1)
                ->where('next_cursor', null);

            $first = $json->toArray()['items'][0];
            expect(array_keys($first))->toEqualCanonicalizing([
                'id', 'slug', 'title', 'tagline', 'status', 'submitted_date', 'user_id',
                'description', 'url', 'upvote_count', 'practice_areas',
            ]);

            return true;
        });
});

it('returns attached challenges when the challenges column is requested', function (): void {
    $challenge = Challenge::factory()->create(['title' => 'Summer Build']);
    $showcase = Showcase::factory()->approved()->create();
    $showcase->challenges()->sync([$challenge->id]);

    StaffServer::tool(ListShowcasesTool::class, ['columns' => ['challenges']])
        ->assertOk()
        ->assertStructuredContent(function ($json) use ($showcase, $challenge): bool {
            $json->where('items.0.id', $showcase->id)
                ->where('items.0.challenges.0.id', $challenge->id)
                ->where('items.0.challenges.0.title', 'Summer Build')
                ->etc();

            return true;
        });
});

it('filters by user_id', function (): void {
    $owner = User::factory()->create();
    $owned = Showcase::factory()->approved()->create(['user_id' => $owner->id]);
    Showcase::factory()->approved()->create();

    StaffServer::tool(ListShowcasesTool::class, ['user_id' => $owner->id])
        ->assertOk()
        ->assertStructuredContent(function ($json) use ($owned, $owner): bool {
            $json->where('total_count', 1)
                ->where('items.0.id', $owned->id)
                ->where('items.0.user_id', $owner->id)
                ->etc();

            return true;
        });
});

it('returns the full user when the user column is requested', function (): void {
    $user = User::factory()->create();
    $showcase = Showcase::factory()->approved()->create(['user_id' => $user->id]);

    StaffServer::tool(ListShowcasesTool::class, ['columns' => ['user']])
        ->assertOk()
        ->assertStructuredContent(function ($json) use ($showcase, $user): bool {
            $json->where('items.0.id', $showcase->id)
                ->where('items.0.user.id', $user->id)
                ->where('items.0.user.first_name', $user->first_name)
                ->where('items.0.user.last_name', $user->last_name)
                ->etc();

            return true;
        });
});

it('rejects unknown columns', function (): void {
    StaffServer::tool(ListShowcasesTool::class, ['columns' => ['not_a_column']])
        ->assertHasErrors();
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
