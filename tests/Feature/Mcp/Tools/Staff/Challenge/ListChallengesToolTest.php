<?php

use App\Enums\ChallengeVisibility;
use App\Mcp\Servers\StaffServer;
use App\Mcp\Tools\Staff\Challenge\ListChallengesTool;
use App\Models\Challenge\Challenge;
use App\Models\Challenge\SubChallenge;
use App\Models\Showcase\Showcase;

it('returns a condensed index with only the expected fields per item', function (): void {
    $challenge = Challenge::factory()->active()->create([
        'title' => 'Build a Brief Drafter',
        'tagline' => 'AI for litigators.',
    ]);

    StaffServer::tool(ListChallengesTool::class)
        ->assertOk()
        ->assertStructuredContent(function ($json) use ($challenge): bool {
            $json->where('items.0.id', $challenge->id)
                ->where('items.0.slug', $challenge->slug)
                ->where('items.0.title', $challenge->title)
                ->where('items.0.tagline', $challenge->tagline)
                ->where('items.0.starts_at', null)
                ->where('items.0.ends_at', null)
                ->where('items.0.is_active', true)
                ->where('items.0.visibility', 'Public')
                ->where('total_count', 1)
                ->where('next_cursor', null);

            $first = $json->toArray()['items'][0];
            expect(array_keys($first))->toEqualCanonicalizing([
                'id', 'slug', 'title', 'tagline', 'starts_at', 'ends_at', 'is_active', 'visibility',
            ]);

            return true;
        });
});

it('filters by visibility', function (): void {
    Challenge::factory()->create(['visibility' => ChallengeVisibility::Public]);
    $invite = Challenge::factory()->create(['visibility' => ChallengeVisibility::InviteToSubmit]);

    StaffServer::tool(ListChallengesTool::class, ['visibility' => 'InviteToSubmit'])
        ->assertOk()
        ->assertStructuredContent(function ($json) use ($invite): bool {
            $json->where('total_count', 1)
                ->where('items.0.id', $invite->id)
                ->etc();

            return true;
        });
});

it('filters by active flag', function (): void {
    Challenge::factory()->create(['is_active' => false]);
    $active = Challenge::factory()->active()->create();

    StaffServer::tool(ListChallengesTool::class, ['active' => true])
        ->assertOk()
        ->assertStructuredContent(function ($json) use ($active): bool {
            $json->where('total_count', 1)
                ->where('items.0.id', $active->id)
                ->etc();

            return true;
        });
});

it('filters by text query against title and tagline', function (): void {
    Challenge::factory()->create(['title' => 'Unrelated', 'tagline' => 'Other']);
    $match = Challenge::factory()->create(['title' => 'Contract Wizard', 'tagline' => 'Magic']);

    StaffServer::tool(ListChallengesTool::class, ['query' => 'Wizard'])
        ->assertOk()
        ->assertStructuredContent(function ($json) use ($match): bool {
            $json->where('total_count', 1)
                ->where('items.0.id', $match->id)
                ->etc();

            return true;
        });
});

it('filters by a list of ids', function (): void {
    $first = Challenge::factory()->create();
    Challenge::factory()->create();
    $third = Challenge::factory()->create();

    StaffServer::tool(ListChallengesTool::class, ['ids' => [$first->id, $third->id]])
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
    $challenge = Challenge::factory()->create(['description' => 'A detailed brief.']);
    $challenge->showcases()->attach(Showcase::factory()->approved()->create());

    StaffServer::tool(ListChallengesTool::class, ['columns' => ['description', 'showcases_count', 'total_upvotes_count']])
        ->assertOk()
        ->assertStructuredContent(function ($json) use ($challenge): bool {
            $json->where('items.0.id', $challenge->id)
                ->where('items.0.description', 'A detailed brief.')
                ->where('items.0.showcases_count', 1)
                ->where('items.0.total_upvotes_count', 0)
                ->where('total_count', 1)
                ->where('next_cursor', null);

            $first = $json->toArray()['items'][0];
            expect(array_keys($first))->toEqualCanonicalizing([
                'id', 'slug', 'title', 'tagline', 'starts_at', 'ends_at', 'is_active', 'visibility',
                'description', 'showcases_count', 'total_upvotes_count',
            ]);

            return true;
        });
});

it('returns sub_challenges when requested as a column', function (): void {
    $challenge = Challenge::factory()->create();
    $subChallenge = SubChallenge::factory()->forChallenge($challenge)->create([
        'name' => 'Drafting Track',
        'order' => 0,
    ]);

    StaffServer::tool(ListChallengesTool::class, ['columns' => ['sub_challenges']])
        ->assertOk()
        ->assertStructuredContent(function ($json) use ($challenge, $subChallenge): bool {
            $json->where('items.0.id', $challenge->id)
                ->where('items.0.sub_challenges.0.id', $subChallenge->id)
                ->where('items.0.sub_challenges.0.name', 'Drafting Track')
                ->where('total_count', 1)
                ->where('next_cursor', null);

            $first = $json->toArray()['items'][0];
            expect(array_keys($first))->toEqualCanonicalizing([
                'id', 'slug', 'title', 'tagline', 'starts_at', 'ends_at', 'is_active', 'visibility',
                'sub_challenges',
            ]);

            return true;
        });
});

it('omits sub_challenges when not requested', function (): void {
    $challenge = Challenge::factory()->create();
    SubChallenge::factory()->forChallenge($challenge)->create();

    StaffServer::tool(ListChallengesTool::class)
        ->assertOk()
        ->assertStructuredContent(function ($json): bool {
            $json->where('total_count', 1)->etc();

            expect(array_keys($json->toArray()['items'][0]))->not->toContain('sub_challenges');

            return true;
        });
});

it('rejects unknown columns', function (): void {
    StaffServer::tool(ListChallengesTool::class, ['columns' => ['not_a_column']])
        ->assertHasErrors();
});

it('paginates results with limit and returns a next_cursor when more remain', function (): void {
    Challenge::factory()->count(3)->create();

    $first = StaffServer::tool(ListChallengesTool::class, ['limit' => 2]);

    $cursor = null;
    $first->assertOk()->assertStructuredContent(function ($json) use (&$cursor): bool {
        $json->has('items', 2)
            ->where('total_count', 3)
            ->whereNot('next_cursor', null);

        $cursor = $json->toArray()['next_cursor'];

        return true;
    });

    StaffServer::tool(ListChallengesTool::class, ['limit' => 2, 'cursor' => $cursor])
        ->assertOk()
        ->assertStructuredContent(function ($json): bool {
            $json->has('items', 1)
                ->where('total_count', 3)
                ->where('next_cursor', null);

            return true;
        });
});

it('rejects invalid input', function (): void {
    StaffServer::tool(ListChallengesTool::class, ['visibility' => 'Bogus'])
        ->assertHasErrors();

    StaffServer::tool(ListChallengesTool::class, ['limit' => 0])
        ->assertHasErrors();
});
