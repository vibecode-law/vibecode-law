<?php

use App\Mcp\Shapes\Challenge\ChallengeColumn;
use App\Mcp\Shapes\Challenge\ChallengeDetailResource;
use App\Mcp\Tools\Staff\Challenge\ListChallengesTool;
use App\Models\Challenge\Challenge;

it('covers every detail field exactly once across summary and columns', function (): void {
    $challenge = Challenge::factory()->create()->load('subChallenges');

    $fields = array_keys(
        ChallengeDetailResource::from($challenge)->include(...ChallengeColumn::values())->toArray()
    );

    expect([...ListChallengesTool::SUMMARY_FIELDS, ...ChallengeColumn::values()])
        ->toEqualCanonicalizing($fields);
});
