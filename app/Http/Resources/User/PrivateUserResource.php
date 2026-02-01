<?php

namespace App\Http\Resources\User;

use App\Models\User;
use Illuminate\Support\Carbon;
use Spatie\LaravelData\Resource;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class PrivateUserResource extends Resource
{
    public function __construct(
        public int $id,
        public string $first_name,
        public string $last_name,
        public string $handle,
        public ?string $organisation,
        public ?string $job_title,
        public ?string $avatar,
        public ?string $linkedin_url,
        public ?string $bio,
        public string $email,
        public ?Carbon $email_verified_at,
        public ?Carbon $marketing_opt_out_at,
    ) {}

    public static function fromModel(User $user): self
    {
        return new self(
            id: $user->id,
            first_name: $user->first_name,
            last_name: $user->last_name,
            handle: $user->handle,
            organisation: $user->organisation,
            job_title: $user->job_title,
            avatar: $user->avatar,
            linkedin_url: $user->linkedin_url,
            bio: $user->bio,
            email: $user->email,
            email_verified_at: $user->email_verified_at,
            marketing_opt_out_at: $user->marketing_opt_out_at,
        );
    }
}
