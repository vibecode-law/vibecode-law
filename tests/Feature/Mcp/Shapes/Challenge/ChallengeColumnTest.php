<?php

use App\Mcp\Shapes\Challenge\ChallengeColumn;
use App\Mcp\Shapes\Challenge\ChallengeDetailResource;
use App\Mcp\Tools\Staff\Challenge\ListChallengesTool;
use App\Models\Challenge\Challenge;

it('covers every detail field exactly once across summary and columns', function (): void {
    $challenge = Challenge::factory()->create();

    $fields = array_keys(ChallengeDetailResource::from($challenge)->toArray());

    expect([...ListChallengesTool::SUMMARY_FIELDS, ...ChallengeColumn::values()])
        ->toEqualCanonicalizing($fields);
});
