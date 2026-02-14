<?php

namespace App\Models\Course;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @mixin IdeHelperCourseTag
 */
class CourseTag extends Model
{
    /** @use HasFactory<\Database\Factories\Course\CourseTagFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];

    //
    // Relationships
    //

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(related: Course::class)->withTimestamps();
    }
}
