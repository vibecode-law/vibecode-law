<?php

namespace App\Mcp\Shapes\User;

use App\Mcp\Shapes\Concerns\HasColumnValues;
use App\Support\TypeScript\SkipTypeScriptTransform;

/**
 * Additional user columns that list_users can return on top of the
 * always-present summary fields (id, first_name, last_name). Each case maps to
 * a field exposed by UserSummaryResource.
 */
#[SkipTypeScriptTransform]
enum UserColumn: string
{
    use HasColumnValues;

    case JobTitle = 'job_title';
    case Organisation = 'organisation';
    case Bio = 'bio';
    case LinkedinUrl = 'linkedin_url';
}
