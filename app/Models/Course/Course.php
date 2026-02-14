<?php

namespace App\Models\Course;

use App\Enums\ExperienceLevel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperCourse
 */
class Course extends Model
{
    /** @use HasFactory<\Database\Factories\Course\CourseFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'tagline',
        'description',
        'order',
        'experience_level',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'experience_level' => ExperienceLevel::class,
            'started_count' => 'integer',
            'completed_count' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    //
    // Relationships
    //

    public function user(): BelongsTo
    {
        return $this->belongsTo(related: User::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(related: Lesson::class)->orderBy('order');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(related: CourseTag::class)->withTimestamps();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(related: User::class)
            ->using(CourseUser::class)
            ->withPivot('viewed_at', 'started_at', 'completed_at')
            ->withTimestamps();
    }
}
