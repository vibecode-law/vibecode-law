<?php

namespace App\Mcp\Shapes\User;

use App\Models\User;
use Spatie\LaravelData\Resource;

class UserSummaryResource extends Resource
{
    public int $id;

    public string $first_name;

    public string $last_name;

    public ?string $job_title;

    public ?string $organisation;

    public ?string $bio;

    public ?string $linkedin_url;

    public static function fromModel(User $user): self
    {
        return self::from([
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'job_title' => $user->job_title,
            'organisation' => $user->organisation,
            'bio' => $user->bio,
            'linkedin_url' => $user->linkedin_url,
        ]);
    }
}
