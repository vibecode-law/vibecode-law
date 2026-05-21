<?php

use App\Mcp\Servers\StaffServer;
use App\Mcp\Tools\Staff\User\ListUsersTool;
use App\Models\User;

it('returns a condensed index with only the expected fields per item', function (): void {
    $user = User::factory()->create([
        'first_name' => 'Grace',
        'last_name' => 'Hopper',
    ]);

    StaffServer::tool(ListUsersTool::class)
        ->assertOk()
        ->assertStructuredContent(function ($json) use ($user): bool {
            $json->where('items.0.id', $user->id)
                ->where('items.0.first_name', 'Grace')
                ->where('items.0.last_name', 'Hopper')
                ->where('total_count', 1)
                ->where('next_cursor', null);

            $first = $json->toArray()['items'][0];
            expect(array_keys($first))->toEqualCanonicalizing(['id', 'first_name', 'last_name']);

            return true;
        });
});

it('returns additional columns when requested', function (): void {
    $user = User::factory()->create([
        'first_name' => 'Grace',
        'last_name' => 'Hopper',
        'job_title' => 'Rear Admiral',
        'organisation' => 'US Navy',
        'bio' => 'COBOL pioneer.',
        'linkedin_url' => 'https://linkedin.com/in/grace',
    ]);

    StaffServer::tool(ListUsersTool::class, ['columns' => ['job_title', 'organisation', 'bio', 'linkedin_url']])
        ->assertOk()
        ->assertStructuredContent(function ($json) use ($user): bool {
            $json->where('items.0.id', $user->id)
                ->where('items.0.first_name', 'Grace')
                ->where('items.0.last_name', 'Hopper')
                ->where('items.0.job_title', 'Rear Admiral')
                ->where('items.0.organisation', 'US Navy')
                ->where('items.0.bio', 'COBOL pioneer.')
                ->where('items.0.linkedin_url', 'https://linkedin.com/in/grace')
                ->where('total_count', 1)
                ->where('next_cursor', null);

            $first = $json->toArray()['items'][0];
            expect(array_keys($first))->toEqualCanonicalizing([
                'id', 'first_name', 'last_name', 'job_title', 'organisation', 'bio', 'linkedin_url',
            ]);

            return true;
        });
});

it('filters by first name with a partial match', function (): void {
    $match = User::factory()->create(['first_name' => 'Grace']);
    User::factory()->create(['first_name' => 'Alan']);

    StaffServer::tool(ListUsersTool::class, ['first_name' => 'rac'])
        ->assertOk()
        ->assertStructuredContent(function ($json) use ($match): bool {
            $json->where('total_count', 1)
                ->where('items.0.id', $match->id)
                ->etc();

            return true;
        });
});

it('filters by last name with a partial match', function (): void {
    $match = User::factory()->create(['last_name' => 'Hopper']);
    User::factory()->create(['last_name' => 'Turing']);

    StaffServer::tool(ListUsersTool::class, ['last_name' => 'opp'])
        ->assertOk()
        ->assertStructuredContent(function ($json) use ($match): bool {
            $json->where('total_count', 1)
                ->where('items.0.id', $match->id)
                ->etc();

            return true;
        });
});

it('filters by a list of ids', function (): void {
    $first = User::factory()->create();
    User::factory()->create();
    $third = User::factory()->create();

    StaffServer::tool(ListUsersTool::class, ['ids' => [$first->id, $third->id]])
        ->assertOk()
        ->assertStructuredContent(function ($json) use ($first, $third): bool {
            $json->where('total_count', 2)
                ->where('items.0.id', $third->id)
                ->where('items.1.id', $first->id)
                ->etc();

            return true;
        });
});

it('paginates results with limit and returns a next_cursor when more remain', function (): void {
    User::factory()->count(3)->create();

    $cursor = null;
    StaffServer::tool(ListUsersTool::class, ['limit' => 2])
        ->assertOk()
        ->assertStructuredContent(function ($json) use (&$cursor): bool {
            $json->has('items', 2)
                ->where('total_count', 3)
                ->whereNot('next_cursor', null);

            $cursor = $json->toArray()['next_cursor'];

            return true;
        });

    StaffServer::tool(ListUsersTool::class, ['limit' => 2, 'cursor' => $cursor])
        ->assertOk()
        ->assertStructuredContent(function ($json): bool {
            $json->has('items', 1)
                ->where('total_count', 3)
                ->where('next_cursor', null);

            return true;
        });
});

it('rejects unknown columns', function (): void {
    StaffServer::tool(ListUsersTool::class, ['columns' => ['email']])
        ->assertHasErrors();
});

it('rejects invalid input', function (): void {
    StaffServer::tool(ListUsersTool::class, ['limit' => 0])->assertHasErrors();
    StaffServer::tool(ListUsersTool::class, ['ids' => ['abc']])->assertHasErrors();
});
