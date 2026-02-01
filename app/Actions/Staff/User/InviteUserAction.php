<?php

namespace App\Actions\Staff\User;

use App\Actions\User\GenerateUniqueUserHandleAction;
use App\Models\User;
use App\Notifications\UserInvitation;
use App\Services\User\ProfileService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Password;

class InviteUserAction
{
    public function __construct(
        private GenerateUniqueUserHandleAction $generateHandle,
        private ProfileService $profileService,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string>  $roles
     */
    public function invite(array $data, array $roles = []): User
    {
        $handle = $data['handle'] ?? $this->generateHandle->generate(
            firstName: $data['first_name'],
            lastName: $data['last_name'],
        );

        $user = $this->profileService->create(
            data: [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'handle' => $handle,
                'email' => $data['email'],
                'organisation' => $data['organisation'] ?? null,
                'job_title' => $data['job_title'] ?? null,
                'bio' => $data['bio'] ?? null,
                'linkedin_url' => $data['linkedin_url'] ?? null,
                'marketing_opt_out_at' => (bool) data_get($data, 'marketing_opt_out', false) === true
                    ? Carbon::now()
                    : null,
            ],
            emailVerified: true
        );

        // Team fields are admin-only, handled separately from profile
        $user->team_type = $data['team_type'] ?? null;
        $user->team_role = $data['team_role'] ?? null;
        $user->save();

        if (count($roles) > 0) {
            $user->syncRoles($roles);
        }

        $this->sendInvitation(user: $user);

        return $user;
    }

    private function sendInvitation(User $user): void
    {
        /** @var \Illuminate\Auth\Passwords\PasswordBroker $broker */
        $broker = Password::broker();
        $token = $broker->createToken(user: $user);

        $user->notify(new UserInvitation(token: $token));
    }
}
