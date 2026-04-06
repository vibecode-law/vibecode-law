<?php

namespace App\Models;

use App\Models\Showcase\Showcase;
use Database\Factories\PracticeAreaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @mixin IdeHelperPracticeArea
 */
class PracticeArea extends Model
{
    /** @use HasFactory<PracticeAreaFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];

    public function showcases(): BelongsToMany
    {
        return $this->belongsToMany(related: Showcase::class)->withTimestamps();
    }
}
