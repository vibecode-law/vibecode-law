<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

class Testimonial extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'job_title',
        'organisation',
        'content',
        'avatar_path',
        'is_published',
        'display_order',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'display_order' => 'integer',
        ];
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    #[Scope]
    public function published(Builder $query): void
    {
        $query->where('is_published', true);
    }

    // Computed Attributes - smart resolution of name/job/org
    protected function displayName(): Attribute
    {
        return Attribute::make(
            get: fn (): string => $this->user?->first_name
                ? "{$this->user->first_name} {$this->user->last_name}"
                : $this->name ?? 'Anonymous'
        );
    }

    protected function displayJobTitle(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->user?->job_title ?? $this->job_title
        );
    }

    protected function displayOrganisation(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->user?->organisation ?? $this->organisation
        );
    }

    // Avatar URL with image transformation
    protected function avatar(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                // If linked to user, use their avatar
                if ($this->user?->avatar !== null) {
                    return $this->user->avatar;
                }

                // Otherwise use testimonial's own avatar
                if ($this->avatar_path === null) {
                    return null;
                }

                // If avatar_path is already a full URL, return it as-is
                if (str_starts_with($this->avatar_path, 'http://') || str_starts_with($this->avatar_path, 'https://')) {
                    return $this->avatar_path;
                }

                // Otherwise, transform local path
                $imageTransformBase = Config::get('services.image-transform.base_url');

                return $imageTransformBase !== null
                    ? $imageTransformBase.'/'.$this->avatar_path
                    : Storage::disk('public')->url($this->avatar_path);
            }
        );
    }
}
