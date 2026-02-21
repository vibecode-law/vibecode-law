<?php

namespace App\Models\Course;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperLessonTranscriptLine
 */
class LessonTranscriptLine extends Model
{
    /** @use HasFactory<\Database\Factories\Course\LessonTranscriptLineFactory> */
    use HasFactory;

    protected $fillable = [
        'lesson_id',
        'start_seconds',
        'end_seconds',
        'text',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'start_seconds' => 'decimal:3',
            'end_seconds' => 'decimal:3',
            'order' => 'integer',
        ];
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(related: Lesson::class);
    }
}
