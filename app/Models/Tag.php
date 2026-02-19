<?php

namespace App\Models;

use App\Enums\TagType;
use App\Models\Course\Course;
use App\Models\Course\Lesson;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @mixin IdeHelperTag
 */
class Tag extends Model
{
    /** @use HasFactory<\Database\Factories\TagFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'type' => TagType::class,
        ];
    }

    //
    // Relationships
    //

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(related: Course::class);
    }

    public function lessons(): BelongsToMany
    {
        return $this->belongsToMany(related: Lesson::class);
    }
}
