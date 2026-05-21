<?php

namespace App\Mcp\Shapes\Course;

use App\Mcp\Shapes\Concerns\HasColumnValues;
use App\Support\TypeScript\SkipTypeScriptTransform;

/**
 * Additional lesson columns that list_lessons can return on top of the
 * always-present summary fields. Each case maps to a field exposed by
 * LessonDetailResource.
 */
#[SkipTypeScriptTransform]
enum LessonColumn: string
{
    use HasColumnValues;

    case Description = 'description';
    case LearningObjectives = 'learning_objectives';
    case Order = 'order';
    case DurationSeconds = 'duration_seconds';
    case Gated = 'gated';
    case AllowPreview = 'allow_preview';
    case Host = 'host';
    case ThumbnailUrl = 'thumbnail_url';
    case ViewedCount = 'viewed_count';
    case StartedCount = 'started_count';
    case CompletedCount = 'completed_count';
    case EnrolledCount = 'enrolled_count';
    case TotalPlaybackSeconds = 'total_playback_seconds';
    case AveragePlaybackSeconds = 'average_playback_seconds';
    case AverageCompletionPercentage = 'average_completion_percentage';
    case InstructorUserIds = 'instructor_user_ids';
    case CreatedAt = 'created_at';
    case UpdatedAt = 'updated_at';
}
