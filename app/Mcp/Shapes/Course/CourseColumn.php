<?php

namespace App\Mcp\Shapes\Course;

use App\Mcp\Shapes\Concerns\HasColumnValues;
use App\Support\TypeScript\SkipTypeScriptTransform;

/**
 * Additional course columns that list_courses can return on top of the
 * always-present summary fields. Each case maps to a field exposed by
 * CourseDetailResource.
 */
#[SkipTypeScriptTransform]
enum CourseColumn: string
{
    use HasColumnValues;

    case Description = 'description';
    case LearningObjectives = 'learning_objectives';
    case ExperienceLevel = 'experience_level';
    case IsFeatured = 'is_featured';
    case AllowPreview = 'allow_preview';
    case DurationSeconds = 'duration_seconds';
    case ThumbnailUrl = 'thumbnail_url';
    case LessonsCount = 'lessons_count';
    case ViewedCount = 'viewed_count';
    case StartedCount = 'started_count';
    case CompletedCount = 'completed_count';
    case EnrolledCount = 'enrolled_count';
    case CreatedAt = 'created_at';
    case UpdatedAt = 'updated_at';
}
