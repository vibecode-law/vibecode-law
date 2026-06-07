<?php

use App\Mcp\Servers\StaffServer;
use App\Mcp\Tools\Staff\Challenge\ListChallengeShowcasesTool;
use App\Models\Challenge\Challenge;
use App\Models\Challenge\SubChallenge;
use App\Models\PracticeArea;
use App\Models\Showcase\Showcase;
use App\Models\User;

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
                ->where('items.0.user_id', $showcase->user_id)
                ->where('items.0.sub_challenge', null)
                ->where('total_count', 1)
                ->where('next_cursor', null);

            $first = $json->toArray()['items'][0];
            expect(array_keys($first))->toEqualCanonicalizing([
                'id', 'slug', 'title', 'tagline', 'status', 'submitted_date', 'user_id', 'sub_challenge',
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

it('returns additional columns when requested', function (): void {
    $challenge = Challenge::factory()->create();

    $area = PracticeArea::factory()->create();
    $showcase = Showcase::factory()->approved()->create([
        'description' => 'A detailed description.',
        'url' => 'https://example.test',
    ]);
    $showcase->practiceAreas()->sync([$area->id]);
    $showcase->upvoters()->sync([User::factory()->create()->id]);
    $challenge->showcases()->attach($showcase);

    StaffServer::tool(ListChallengeShowcasesTool::class, [
        'challenge_id' => $challenge->id,
        'columns' => ['description', 'url', 'upvote_count', 'practice_areas'],
    ])
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
                'id', 'slug', 'title', 'tagline', 'status', 'submitted_date', 'user_id', 'sub_challenge',
                'description', 'url', 'upvote_count', 'practice_areas',
            ]);

            return true;
        });
});

it('filters attached showcases by sub_challenge_id', function (): void {
    $challenge = Challenge::factory()->create();
    $subChallenge = SubChallenge::factory()->forChallenge($challenge)->create();
    $otherSubChallenge = SubChallenge::factory()->forChallenge($challenge)->create();

    $entered = Showcase::factory()->approved()->create();
    $otherEntered = Showcase::factory()->approved()->create();
    $unsorted = Showcase::factory()->approved()->create();

    $challenge->showcases()->attach($entered, ['sub_challenge_id' => $subChallenge->id]);
    $challenge->showcases()->attach($otherEntered, ['sub_challenge_id' => $otherSubChallenge->id]);
    $challenge->showcases()->attach($unsorted);

    StaffServer::tool(ListChallengeShowcasesTool::class, [
        'challenge_id' => $challenge->id,
        'sub_challenge_id' => $subChallenge->id,
    ])
        ->assertOk()
        ->assertStructuredContent(function ($json) use ($entered, $subChallenge): bool {
            $json->where('total_count', 1)
                ->where('items.0.id', $entered->id)
                ->where('items.0.sub_challenge', [
                    'id' => $subChallenge->id,
                    'name' => $subChallenge->name,
                ])
                ->etc();

            return true;
        });
});

it('returns each entry under the sub-challenge it was attached to', function (): void {
    $challenge = Challenge::factory()->create();
    $subChallenge = SubChallenge::factory()->forChallenge($challenge)->create(['name' => 'Drafting']);
    $otherSubChallenge = SubChallenge::factory()->forChallenge($challenge)->create(['name' => 'Research']);

    $entered = Showcase::factory()->approved()->create();
    $otherEntered = Showcase::factory()->approved()->create();
    $unsorted = Showcase::factory()->approved()->create();

    $challenge->showcases()->attach($entered, ['sub_challenge_id' => $subChallenge->id]);
    $challenge->showcases()->attach($otherEntered, ['sub_challenge_id' => $otherSubChallenge->id]);
    $challenge->showcases()->attach($unsorted);

    StaffServer::tool(ListChallengeShowcasesTool::class, ['challenge_id' => $challenge->id])
        ->assertOk()
        ->assertStructuredContent(function ($json) use ($entered, $otherEntered, $unsorted, $subChallenge, $otherSubChallenge): bool {
            $subChallengesByShowcaseId = collect($json->toArray()['items'])
                ->pluck('sub_challenge', 'id');

            expect($subChallengesByShowcaseId->get($entered->id))->toBe([
                'id' => $subChallenge->id,
                'name' => 'Drafting',
            ]);
            expect($subChallengesByShowcaseId->get($otherEntered->id))->toBe([
                'id' => $otherSubChallenge->id,
                'name' => 'Research',
            ]);
            expect($subChallengesByShowcaseId->get($unsorted->id))->toBeNull();

            $json->where('total_count', 3)->etc();

            return true;
        });
});

it('returns an error when the sub-challenge does not belong to the challenge', function (): void {
    $challenge = Challenge::factory()->create();
    $foreignSubChallenge = SubChallenge::factory()->create();

    StaffServer::tool(ListChallengeShowcasesTool::class, [
        'challenge_id' => $challenge->id,
        'sub_challenge_id' => $foreignSubChallenge->id,
    ])
        ->assertHasErrors(["Sub-challenge with id [{$foreignSubChallenge->id}] does not belong to challenge [{$challenge->id}]."]);
});

it('rejects unknown columns', function (): void {
    $challenge = Challenge::factory()->create();

    StaffServer::tool(ListChallengeShowcasesTool::class, [
        'challenge_id' => $challenge->id,
        'columns' => ['not_a_column'],
    ])->assertHasErrors();
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
