<?php

namespace App\Actions\Fortify;

use App\Actions\Fortify\Concerns\PasswordValidationRules;
use App\Actions\User\GenerateUniqueUserHandleAction;
use App\Models\User;
use App\Services\User\ProfileService;
use App\Services\User\UserAvatarService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    public function __construct(
        private ProfileService $profileService,
        private GenerateUniqueUserHandleAction $handleAction,
    ) {}

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, mixed>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'first_name' => ['required', 'string', 'max:60'],
            'last_name' => ['required', 'string', 'max:60'],
            'organisation' => ['nullable', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255', 'regex:/^https:\/\/[a-z]+\.linkedin\.com\/in\//i'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
            'avatar' => ['nullable', 'image', 'mimes:png,jpg,jpeg,gif,webp', 'max:2048'],
        ])->validate();

        $user = $this->profileService->create(data: [
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name'],
            'handle' => $this->handleAction->generate(
                firstName: $input['first_name'],
                lastName: $input['last_name'],
            ),
            'organisation' => $input['organisation'] ?? null,
            'job_title' => $input['job_title'] ?? null,
            'linkedin_url' => $input['linkedin_url'] ?? null,
            'email' => $input['email'],
        ]);

        // Password is handled separately from profile data
        $user->password = Hash::make($input['password']);
        $user->save();

        if (isset($input['avatar']) && $input['avatar'] instanceof UploadedFile) {
            $service = new UserAvatarService(user: $user);
            $service->fromUploadedFile(file: $input['avatar']);
        }

        return $user;
    }
}
