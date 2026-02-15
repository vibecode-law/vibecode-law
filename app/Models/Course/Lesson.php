<?php

namespace App\Models\Course;

use App\Enums\VideoHost;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @mixin IdeHelperLesson
 */
class Lesson extends Model
{
    /** @use HasFactory<\Database\Factories\Course\LessonFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'tagline',
        'description',
        'copy',
        'transcript',
        'track_id',
        'embed',
        'host',
        'gated',
        'order',
        'course_id',
    ];

    protected function casts(): array
    {
        return [
            'host' => VideoHost::class,
            'gated' => 'boolean',
            'order' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    //
    // Relationships
    //

    public function course(): BelongsTo
    {
        return $this->belongsTo(related: Course::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(related: User::class)
            ->using(LessonUser::class)
            ->withPivot('viewed_at', 'started_at', 'completed_at')
            ->withTimestamps();
    }
}
