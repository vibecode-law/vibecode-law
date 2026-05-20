<?php

use App\Mcp\Servers\StaffServer;
use App\Mcp\Tools\Staff\Challenge\ListChallengeShowcasesTool;
use App\Models\Challenge\Challenge;
use App\Models\Showcase\Showcase;

it('returns the showcases attached to a challenge in the condensed summary form', function (): void {
    $challenge = Challenge::factory()->create();

    $showcase = Showcase::factory()->approved()->create([
        'title' => 'Pleadings Assistant',
        'tagline' => 'Drafts pleadings.',
    ]);
    $challenge->showcases()->attach($showcase);

    StaffServer::tool(ListChallengeShowcasesTool::class, ['challenge_id' => $challenge->id])
        ->assertOk()
        ->assertStructuredContent(function ($json) use ($showcase): bool {
            $json->where('items.0.id', $showcase->id)
                ->where('items.0.slug', $showcase->slug)
                ->where('items.0.title', 'Pleadings Assistant')
                ->where('items.0.tagline', 'Drafts pleadings.')
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

it('filters attached showcases by status', function (): void {
    $challenge = Challenge::factory()->create();

    $approved = Showcase::factory()->approved()->create();
    $pending = Showcase::factory()->pending()->create();
    $challenge->showcases()->attach([$approved->id, $pending->id]);

    StaffServer::tool(ListChallengeShowcasesTool::class, [
        'challenge_id' => $challenge->id,
        'status' => 'Approved',
    ])
        ->assertOk()
        ->assertStructuredContent(function ($json) use ($approved): bool {
            $json->where('total_count', 1)
                ->where('items.0.id', $approved->id)
                ->where('items.0.status', 'Approved')
                ->etc();

            return true;
        });
});

it('returns an empty list when the challenge has no showcases', function (): void {
    $challenge = Challenge::factory()->create();

    StaffServer::tool(ListChallengeShowcasesTool::class, ['challenge_id' => $challenge->id])
        ->assertOk()
        ->assertStructuredContent([
            'items' => [],
            'total_count' => 0,
            'next_cursor' => null,
        ]);
});

it('paginates results and returns a next_cursor when more remain', function (): void {
    $challenge = Challenge::factory()->create();
    $showcases = Showcase::factory()->count(3)->approved()->create();
    $challenge->showcases()->attach($showcases->pluck('id'));

    $first = StaffServer::tool(ListChallengeShowcasesTool::class, [
        'challenge_id' => $challenge->id,
        'limit' => 2,
    ]);

    $cursor = null;
    $first->assertOk()->assertStructuredContent(function ($json) use (&$cursor): bool {
        $json->has('items', 2)
            ->where('total_count', 3)
            ->whereNot('next_cursor', null);

        $cursor = $json->toArray()['next_cursor'];

        return true;
    });

    StaffServer::tool(ListChallengeShowcasesTool::class, [
        'challenge_id' => $challenge->id,
        'limit' => 2,
        'cursor' => $cursor,
    ])
        ->assertOk()
        ->assertStructuredContent(function ($json): bool {
            $json->has('items', 1)
                ->where('total_count', 3)
                ->where('next_cursor', null);

            return true;
        });
});

it('returns an error when the challenge does not exist', function (): void {
    StaffServer::tool(ListChallengeShowcasesTool::class, ['challenge_id' => 999999])
        ->assertHasErrors(['Challenge with id [999999] was not found.']);
});

it('rejects invalid input', function (): void {
    StaffServer::tool(ListChallengeShowcasesTool::class, [])->assertHasErrors();
    StaffServer::tool(ListChallengeShowcasesTool::class, ['challenge_id' => 0])->assertHasErrors();
    StaffServer::tool(ListChallengeShowcasesTool::class, ['challenge_id' => 1, 'limit' => 0])
        ->assertHasErrors();
    StaffServer::tool(ListChallengeShowcasesTool::class, ['challenge_id' => 1, 'status' => 'Bogus'])
        ->assertHasErrors();
});
