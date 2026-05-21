<?php

use App\Mcp\Shapes\User\UserColumn;
use App\Mcp\Shapes\User\UserSummaryResource;
use App\Mcp\Tools\Staff\User\ListUsersTool;
use App\Models\User;

it('covers every resource field exactly once across summary and columns', function (): void {
    $user = User::factory()->create();

    $fields = array_keys(UserSummaryResource::from($user)->toArray());

    expect([...ListUsersTool::SUMMARY_FIELDS, ...UserColumn::values()])
        ->toEqualCanonicalizing($fields);
});
