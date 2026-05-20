<?php

use App\Mcp\Servers\StaffServer;
use App\Mcp\Tools\Staff\Showcase\ListShowcaseUpvotersTool;
use App\Models\Showcase\Showcase;
use App\Models\User;

it('returns the upvoters with full user details', function (): void {
    $showcase = Showcase::factory()->approved()->create();

    $upvoter = User::factory()->create([
        'first_name' => 'Grace',
        'last_name' => 'Hopper',
        'job_title' => 'Rear Admiral',
        'organisation' => 'US Navy',
        'bio' => 'COBOL pioneer.',
        'linkedin_url' => 'https://linkedin.com/in/grace',
    ]);

    $showcase->upvoters()->attach($upvoter);

    StaffServer::tool(ListShowcaseUpvotersTool::class, ['showcase_id' => $showcase->id])
        ->assertOk()
        ->assertStructuredContent([
            'items' => [
                [
                    'id' => $upvoter->id,
                    'first_name' => 'Grace',
                    'last_name' => 'Hopper',
                    'job_title' => 'Rear Admiral',
                    'organisation' => 'US Navy',
                    'bio' => 'COBOL pioneer.',
                    'linkedin_url' => 'https://linkedin.com/in/grace',
                ],
            ],
            'total_count' => 1,
            'next_cursor' => null,
        ]);
});

it('returns an empty list when the showcase has no upvoters', function (): void {
    $showcase = Showcase::factory()->approved()->create();

    StaffServer::tool(ListShowcaseUpvotersTool::class, ['showcase_id' => $showcase->id])
        ->assertOk()
        ->assertStructuredContent([
            'items' => [],
            'total_count' => 0,
            'next_cursor' => null,
        ]);
});

it('paginates results and returns a next_cursor when more remain', function (): void {
    $showcase = Showcase::factory()->approved()->create();
    $upvoters = User::factory()->count(3)->create();
    $showcase->upvoters()->attach($upvoters->pluck('id'));

    $first = StaffServer::tool(ListShowcaseUpvotersTool::class, [
        'showcase_id' => $showcase->id,
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

    StaffServer::tool(ListShowcaseUpvotersTool::class, [
        'showcase_id' => $showcase->id,
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

it('returns an error when the showcase does not exist', function (): void {
    StaffServer::tool(ListShowcaseUpvotersTool::class, ['showcase_id' => 999999])
        ->assertHasErrors(['Showcase with id [999999] was not found.']);
});

it('rejects invalid input', function (): void {
    StaffServer::tool(ListShowcaseUpvotersTool::class, [])->assertHasErrors();
    StaffServer::tool(ListShowcaseUpvotersTool::class, ['showcase_id' => 0])->assertHasErrors();
    StaffServer::tool(ListShowcaseUpvotersTool::class, ['showcase_id' => 1, 'limit' => 0])
        ->assertHasErrors();
});
