<?php

use App\Mcp\Servers\StaffServer;
use App\Mcp\Tools\Staff\Showcase\GetShowcaseTool;
use App\Models\Challenge\Challenge;
use App\Models\Challenge\SubChallenge;
use App\Models\PracticeArea;
use App\Models\Showcase\Showcase;

it('returns the full details for a showcase by id', function (): void {
    $area = PracticeArea::factory()->create(['name' => 'IP', 'slug' => 'ip']);
    $challenge = Challenge::factory()->create(['title' => 'Summer Build']);

    $showcase = Showcase::factory()->approved()->create([
        'title' => 'Patent Drafter',
        'tagline' => 'Drafts patents.',
    ]);
    $showcase->practiceAreas()->sync([$area->id]);
    $showcase->challenges()->sync([$challenge->id]);

    StaffServer::tool(GetShowcaseTool::class, ['id' => $showcase->id])
        ->assertOk()
        ->assertStructuredContent(function ($json) use ($showcase, $area, $challenge): bool {
            $json->where('id', $showcase->id)
                ->where('slug', $showcase->slug)
                ->where('title', 'Patent Drafter')
                ->where('tagline', 'Drafts patents.')
                ->where('description', $showcase->description)
                ->where('key_features', $showcase->key_features)
                ->where('help_needed', $showcase->help_needed)
                ->where('url', $showcase->url)
                ->where('video_url', $showcase->video_url)
                ->where('source_status', $showcase->source_status->name)
                ->where('source_url', $showcase->source_url)
                ->where('status', 'Approved')
                ->where('submitted_date', $showcase->submitted_date?->toIso8601String())
                ->where('user_id', $showcase->user_id)
                ->where('view_count', $showcase->view_count)
                ->where('upvote_count', 0)
                ->where('thumbnail_url', null)
                ->where('image_urls', [])
                ->where('user.id', $showcase->user_id)
                ->where('user.first_name', $showcase->user->first_name)
                ->where('user.last_name', $showcase->user->last_name)
                ->where('practice_areas.0.id', $area->id)
                ->where('practice_areas.0.slug', 'ip')
                ->where('practice_areas.0.name', 'IP')
                ->where('challenges.0.id', $challenge->id)
                ->where('challenges.0.title', 'Summer Build')
                ->where('challenges.0.sub_challenge', null)
                ->where('youtube_id', null)
                ->where('created_at', $showcase->created_at?->toIso8601String())
                ->where('updated_at', $showcase->updated_at?->toIso8601String());

            return true;
        });
});

it('includes the sub-challenge as a child of the challenge the showcase is entered under', function (): void {
    $challenge = Challenge::factory()->create(['title' => 'Summer Build']);
    $subChallenge = SubChallenge::factory()->forChallenge($challenge)->create(['name' => 'Drafting Track']);

    $showcase = Showcase::factory()->approved()->create();
    $showcase->challenges()->sync([$challenge->id => ['sub_challenge_id' => $subChallenge->id]]);

    StaffServer::tool(GetShowcaseTool::class, ['id' => $showcase->id])
        ->assertOk()
        ->assertStructuredContent(function ($json) use ($challenge, $subChallenge): bool {
            $json->where('challenges.0.id', $challenge->id)
                ->where('challenges.0.title', 'Summer Build')
                ->where('challenges.0.sub_challenge', [
                    'id' => $subChallenge->id,
                    'name' => 'Drafting Track',
                ])
                ->etc();

            return true;
        });
});

it('returns an error response when the id does not exist', function (): void {
    StaffServer::tool(GetShowcaseTool::class, ['id' => 999999])
        ->assertHasErrors(['Showcase with id [999999] was not found.']);
});

it('rejects invalid input', function (): void {
    StaffServer::tool(GetShowcaseTool::class, [])->assertHasErrors();
    StaffServer::tool(GetShowcaseTool::class, ['id' => 0])->assertHasErrors();
    StaffServer::tool(GetShowcaseTool::class, ['id' => 'abc'])->assertHasErrors();
});
