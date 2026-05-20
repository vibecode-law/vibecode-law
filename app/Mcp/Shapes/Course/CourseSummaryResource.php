<?php

namespace App\Mcp\Shapes\Course;

use App\Models\Course\Course;
use Spatie\LaravelData\Resource;

class CourseSummaryResource extends Resource
{
    public int $id;

    public string $slug;

    public string $title;

    public ?string $tagline;

    public ?string $experience_level;

    public bool $is_featured;

    public ?string $publish_date;

    public ?int $duration_seconds;

    public int $lessons_count;

    public int $started_count;

    public int $completed_count;

    public static function fromModel(Course $course): self
    {
        return self::from([
            'id' => $course->id,
            'slug' => $course->slug,
            'title' => $course->title,
            'tagline' => $course->tagline,
            'experience_level' => $course->experience_level?->name,
            'is_featured' => (bool) $course->is_featured,
            'publish_date' => $course->publish_date?->toDateString(),
            'duration_seconds' => $course->duration_seconds,
            'lessons_count' => (int) ($course->lessons_count ?? $course->lessons()->count()),
            'started_count' => (int) ($course->users_started_count ?? 0),
            'completed_count' => (int) ($course->users_completed_count ?? 0),
        ]);
    }
}
