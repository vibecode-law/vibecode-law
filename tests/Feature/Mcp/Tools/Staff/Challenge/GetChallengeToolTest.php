<?php

use App\Mcp\Servers\StaffServer;
use App\Mcp\Tools\Staff\Challenge\GetChallengeTool;
use App\Models\Challenge\Challenge;
use App\Models\Showcase\Showcase;
use App\Models\User;

it('returns the full details for a challenge by id', function (): void {
    $owner = User::factory()->create();
    $challenge = Challenge::factory()->active()->forUser($owner)->create([
        'title' => 'Legaltech Sprint',
        'tagline' => 'Ship in 7 days.',
    ]);

    $showcase = Showcase::factory()->approved()->create();
    $challenge->showcases()->attach($showcase);

    $upvoter = User::factory()->create();
    $showcase->upvoters()->attach($upvoter);

    StaffServer::tool(GetChallengeTool::class, ['id' => $challenge->id])
        ->assertOk()
        ->assertStructuredContent(function ($json) use ($challenge, $owner): bool {
            $json->where('id', $challenge->id)
                ->where('slug', $challenge->slug)
                ->where('title', 'Legaltech Sprint')
                ->where('tagline', 'Ship in 7 days.')
                ->where('description', $challenge->description)
                ->where('starts_at', null)
                ->where('ends_at', null)
                ->where('is_active', true)
                ->where('is_featured', false)
                ->where('visibility', 'Public')
                ->where('organisation_id', null)
                ->where('user_id', $owner->id)
                ->where('thumbnail_url', null)
                ->where('showcases_count', 1)
                ->where('total_upvotes_count', 1)
                ->where('created_at', $challenge->created_at?->toIso8601String())
                ->where('updated_at', $challenge->updated_at?->toIso8601String());

            return true;
        });
});

it('returns an error response when the id does not exist', function (): void {
    StaffServer::tool(GetChallengeTool::class, ['id' => 999999])
        ->assertHasErrors(['Challenge with id [999999] was not found.']);
});

it('rejects invalid input', function (): void {
    StaffServer::tool(GetChallengeTool::class, [])->assertHasErrors();
    StaffServer::tool(GetChallengeTool::class, ['id' => 0])->assertHasErrors();
    StaffServer::tool(GetChallengeTool::class, ['id' => 'abc'])->assertHasErrors();
});
