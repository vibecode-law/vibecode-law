<?php

namespace App\Mcp\Shapes\Challenge;

use App\Mcp\Shapes\Concerns\HasColumnValues;
use App\Support\TypeScript\SkipTypeScriptTransform;

/**
 * Additional challenge columns that list_challenges can return on top of the
 * always-present summary fields. Each case maps to a field exposed by
 * ChallengeDetailResource.
 */
#[SkipTypeScriptTransform]
enum ChallengeColumn: string
{
    use HasColumnValues;

    case Description = 'description';
    case IsFeatured = 'is_featured';
    case OrganisationId = 'organisation_id';
    case UserId = 'user_id';
    case ThumbnailUrl = 'thumbnail_url';
    case ShowcasesCount = 'showcases_count';
    case TotalUpvotesCount = 'total_upvotes_count';
    case CreatedAt = 'created_at';
    case UpdatedAt = 'updated_at';
}
