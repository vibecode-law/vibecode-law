<?php

namespace App\Http\Resources\User;

use App\Enums\TeamType;
use App\Models\User;
use Illuminate\Support\Carbon;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Resource;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class AdminUserResource extends Resource
{
    public int $id;

    public string $first_name;

    public string $last_name;

    public string $handle;

    public ?string $organisation;

    public ?string $job_title;

    public ?string $avatar;

    public ?string $linkedin_url;

    public ?string $bio;

    public string $email;

    public bool $is_admin;

    public ?Carbon $blocked_from_submissions_at;

    public ?Carbon $marketing_opt_out_at;

    public Carbon $created_at;

    public ?TeamType $team_type;

    public ?string $team_role;

    /** @var string[] */
    public array $roles;

    public Lazy|int $showcases_count;

    public static function fromModel(User $user): self
    {
        return self::from([
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'handle' => $user->handle,
            'organisation' => $user->organisation,
            'job_title' => $user->job_title,
            'avatar' => $user->avatar,
            'linkedin_url' => $user->linkedin_url,
            'bio' => $user->bio,
            'email' => $user->email,
            'is_admin' => $user->is_admin,
            'blocked_from_submissions_at' => $user->blocked_from_submissions_at,
            'marketing_opt_out_at' => $user->marketing_opt_out_at,
            'created_at' => $user->created_at,
            'team_type' => $user->team_type,
            'team_role' => $user->team_role,
            'roles' => $user->getRoleNames()->values()->all(),
            'showcases_count' => Lazy::when(
                condition: fn () => $user->hasAttribute('showcases_count'),
                value: fn () => $user->showcases_count,
            ),
        ]);
    }
}
