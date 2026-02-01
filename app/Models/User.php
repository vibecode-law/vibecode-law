<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\ShowcaseStatus;
use App\Enums\TeamType;
use App\Models\Showcase\Showcase;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property \Illuminate\Support\Carbon|null $marketing_opt_out_at
 *
 * @mixin IdeHelperUser
 */
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'handle',
        'organisation',
        'job_title',
        'linkedin_url',
        'bio',
        'email',
        'password',
        'blocked_from_submissions_at',
        'team_type',
        'team_role',
        'team_order',
        'marketing_opt_out_at',
        'external_subscriber_uuid',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
        'linkedin_id',
        'linkedin_token',
        'avatar_path',
        'external_subscriber_uuid',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'two_factor_confirmed_at' => 'datetime',
            'blocked_from_submissions_at' => 'datetime',
            'team_type' => TeamType::class,
            'marketing_opt_out_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'handle';
    }

    //
    // Relationships
    //

    public function showcases(): HasMany
    {
        return $this->hasMany(Showcase::class);
    }

    public function upvotedShowcases(): BelongsToMany
    {
        return $this->belongsToMany(Showcase::class, 'showcase_upvotes')->withTimestamps();
    }

    //
    // Scopes
    //

    #[Scope]
    protected function teamMembers(Builder $query): void
    {
        $query->whereNotNull('team_type');
    }

    #[Scope]
    protected function coreTeam(Builder $query): void
    {
        $query->where('team_type', TeamType::CoreTeam);
    }

    #[Scope]
    protected function collaborators(Builder $query): void
    {
        $query->where('team_type', TeamType::Collaborator);
    }

    //
    // Helpers
    //

    public function isTeamMember(): bool
    {
        return $this->team_type !== null;
    }

    public function hasApprovedShowcase(): bool
    {
        return $this->showcases()->where('status', ShowcaseStatus::Approved)->exists();
    }

    public function hasPublicProfile(): bool
    {
        return $this->isTeamMember() === true || $this->hasApprovedShowcase() === true;
    }

    public function isBlockedFromSubmissions(): bool
    {
        return $this->blocked_from_submissions_at !== null;
    }

    public function isSubscribedToMarketing(): bool
    {
        return $this->marketing_opt_out_at === null;
    }

    //
    // Attributes
    //

    protected function avatar(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                if ($this->avatar_path === null) {
                    return null;
                }

                $imageTransformBase = Config::get('services.image-transform.base_url');

                return $imageTransformBase
                    ? $imageTransformBase.'/'.$this->avatar_path
                    : Storage::disk('public')->url($this->avatar_path);
            }
        );
    }
}
