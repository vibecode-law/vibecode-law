<?php

namespace App\Models\Course;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperCourseUser
 */
class CourseUser extends Pivot
{
    /** @use HasFactory<\Database\Factories\Course\CourseUserFactory> */
    use HasFactory;

    protected $table = 'course_user';

    public $incrementing = true;

    protected function casts(): array
    {
        return [
            'viewed_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }
}
