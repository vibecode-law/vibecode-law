<?php

namespace App\Mcp\Shapes\Course;

use App\Models\Course\Lesson;
use Spatie\LaravelData\Resource;

class LessonSummaryResource extends Resource
{
    public int $id;

    public string $slug;

    public string $title;

    public ?string $tagline;

    public int $course_id;

    public int $order;

    public ?int $duration_seconds;

    public bool $gated;

    public bool $allow_preview;

    public ?string $publish_date;

    public int $started_count;

    public int $completed_count;

    public int $total_playback_seconds;

    public ?float $average_playback_seconds;

    public ?float $average_completion_percentage;

    public static function fromModel(Lesson $lesson): self
    {
        $totalPlayback = (int) ($lesson->users_total_playback_seconds ?? 0);
        $enrolled = (int) ($lesson->users_count ?? 0);
        $averagePlayback = $enrolled > 0 ? round($totalPlayback / $enrolled, 2) : null;
        $duration = $lesson->duration_seconds;

        return self::from([
            'id' => $lesson->id,
            'slug' => $lesson->slug,
            'title' => $lesson->title,
            'tagline' => $lesson->tagline,
            'course_id' => $lesson->course_id,
            'order' => (int) $lesson->order,
            'duration_seconds' => $duration,
            'gated' => (bool) $lesson->gated,
            'allow_preview' => (bool) $lesson->allow_preview,
            'publish_date' => $lesson->publish_date?->toDateString(),
            'started_count' => (int) ($lesson->users_started_count ?? 0),
            'completed_count' => (int) ($lesson->users_completed_count ?? 0),
            'total_playback_seconds' => $totalPlayback,
            'average_playback_seconds' => $averagePlayback,
            'average_completion_percentage' => $averagePlayback !== null && $duration !== null && $duration > 0
                ? min(100.0, round($averagePlayback / $duration * 100, 2))
                : null,
        ]);
    }
}
