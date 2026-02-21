<?php

namespace App\Models\Course;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperLessonUser
 */
class LessonUser extends Pivot
{
    /** @use HasFactory<\Database\Factories\Course\LessonUserFactory> */
    use HasFactory;

    protected $table = 'lesson_user';

    public $incrementing = true;

    protected function casts(): array
    {
        return [
            'viewed_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'playback_time_seconds' => 'integer',
        ];
    }
}
