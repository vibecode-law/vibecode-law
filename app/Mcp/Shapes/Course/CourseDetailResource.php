<?php

namespace App\Mcp\Shapes\Course;

use App\Models\Course\Course;
use Spatie\LaravelData\Resource;

class CourseDetailResource extends Resource
{
    public int $id;

    public string $slug;

    public string $title;

    public ?string $tagline;

    public ?string $description;

    public ?string $learning_objectives;

    public ?string $experience_level;

    public bool $is_featured;

    public bool $allow_preview;

    public ?string $publish_date;

    public ?int $duration_seconds;

    public ?string $thumbnail_url;

    public int $lessons_count;

    public int $viewed_count;

    public int $started_count;

    public int $completed_count;

    public int $enrolled_count;

    public string $created_at;

    public string $updated_at;

    public static function fromModel(Course $course): self
    {
        return self::from([
            'id' => $course->id,
            'slug' => $course->slug,
            'title' => $course->title,
            'tagline' => $course->tagline,
            'description' => $course->description,
            'learning_objectives' => $course->learning_objectives,
            'experience_level' => $course->experience_level?->name,
            'is_featured' => (bool) $course->is_featured,
            'allow_preview' => (bool) $course->allow_preview,
            'publish_date' => $course->publish_date?->toDateString(),
            'duration_seconds' => $course->duration_seconds,
            'thumbnail_url' => $course->thumbnail_url,
            'lessons_count' => (int) ($course->lessons_count ?? $course->lessons()->count()),
            'viewed_count' => (int) ($course->users_viewed_count ?? 0),
            'started_count' => (int) ($course->users_started_count ?? 0),
            'completed_count' => (int) ($course->users_completed_count ?? 0),
            'enrolled_count' => (int) ($course->users_count ?? 0),
            'created_at' => $course->created_at?->toIso8601String() ?? '',
            'updated_at' => $course->updated_at?->toIso8601String() ?? '',
        ]);
    }
}
