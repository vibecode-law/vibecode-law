<?php

namespace App\Mcp\Shapes\Showcase;

use App\Mcp\Shapes\Concerns\HasColumnValues;
use App\Support\TypeScript\SkipTypeScriptTransform;

/**
 * Additional showcase columns that list_showcases can return on top of the
 * always-present summary fields. Each case maps to a field exposed by
 * ShowcaseDetailResource, which produces the value.
 */
#[SkipTypeScriptTransform]
enum ShowcaseColumn: string
{
    use HasColumnValues;

    case Description = 'description';
    case KeyFeatures = 'key_features';
    case HelpNeeded = 'help_needed';
    case Url = 'url';
    case VideoUrl = 'video_url';
    case SourceStatus = 'source_status';
    case SourceUrl = 'source_url';
    case ViewCount = 'view_count';
    case UpvoteCount = 'upvote_count';
    case ThumbnailUrl = 'thumbnail_url';
    case ImageUrls = 'image_urls';
    case User = 'user';
    case PracticeAreas = 'practice_areas';
    case Challenges = 'challenges';
    case YoutubeId = 'youtube_id';
    case CreatedAt = 'created_at';
    case UpdatedAt = 'updated_at';

    /**
     * The relation that must be eager loaded for this column to resolve without an N+1 query.
     */
    public function relationToLoad(): ?string
    {
        return match ($this) {
            self::ImageUrls => 'images',
            self::PracticeAreas => 'practiceAreas',
            self::User => 'user',
            self::Challenges => 'challenges.subChallenges',
            default => null,
        };
    }

    /**
     * The relation that must be counted for this column to resolve without an N+1 query.
     */
    public function relationToCount(): ?string
    {
        return match ($this) {
            self::UpvoteCount => 'upvoters',
            default => null,
        };
    }
}
