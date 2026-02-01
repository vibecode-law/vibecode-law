<?php

namespace App\Services\Auth\Linkedin;

use App\Actions\User\GenerateUniqueUserHandleAction;
use App\Models\User;
use App\Services\User\ProfileService;
use Laravel\Socialite\Two\User as LinkedinUser;

class FindOrCreateLinkedinUserService
{
    public function __construct(
        protected LinkedinUser $linkedinUser,
        protected ProfileService $profileService,
        protected GenerateUniqueUserHandleAction $handleAction = new GenerateUniqueUserHandleAction,
    ) {}

    public function handle(): FindOrCreateLinkedinUserResult
    {
        return $this->findByLinkedinId()
            ?? $this->findOrCreateByEmail();
    }

    protected function findByLinkedinId(): ?FindOrCreateLinkedinUserResult
    {
        $user = User::query()
            ->where('linkedin_id', '=', $this->linkedinUser->id)
            ->first();

        if ($user === null) {
            return null;
        }

        return FindOrCreateLinkedinUserResult::success(
            user: $this->updateExistingUser(
                user: $user,
                linkProfiles: false
            ),
            wasRecentlyCreated: false,
        );
    }

    protected function findOrCreateByEmail(): FindOrCreateLinkedinUserResult
    {
        $user = User::query()
            ->where('email', '=', $this->linkedinUser->email)
            ->first();

        if ($user === null) {
            return $this->createNewUser();
        }

        if ($user->email_verified_at === null) {
            $linkedIsVerified = $this->linkedinUser->user['email_verified'] ?? false;

            if ($linkedIsVerified === false) {
                return FindOrCreateLinkedinUserResult::error(
                    message: 'Your Linkedin account does not have a verified email address. Please verify it and try again.'
                );
            }

            $user->email_verified_at = now();
        }

        return FindOrCreateLinkedinUserResult::success(
            user: $this->updateExistingUser(
                user: $user,
                linkProfiles: true
            ),
            wasRecentlyCreated: false,
        );
    }

    protected function updateExistingUser(User $user, bool $linkProfiles): User
    {
        $this->profileService->update(
            user: $user,
            data: [
                'first_name' => $this->linkedinUser->user['given_name'],
                'last_name' => $this->linkedinUser->user['family_name'],
                'email' => $this->linkedinUser->email,
            ],
        );

        $user->linkedin_token = $this->linkedinUser->token;

        if ($linkProfiles === true) {
            $user->linkedin_id = $this->linkedinUser->id;
        }

        $user->save();

        return $user;
    }

    protected function createNewUser(): FindOrCreateLinkedinUserResult
    {
        $linkedIsVerified = $this->linkedinUser->user['email_verified'] ?? false;

        if ($linkedIsVerified !== true) {
            return FindOrCreateLinkedinUserResult::error(
                message: 'Your LinkedIn email address has not been verified. Please verify your email on LinkedIn and try again.'
            );
        }

        $firstName = $this->linkedinUser->user['given_name'];
        $lastName = $this->linkedinUser->user['family_name'];

        $user = $this->profileService->create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'handle' => $this->handleAction->generate(
                firstName: $firstName,
                lastName: $lastName,
            ),
            'email' => $this->linkedinUser->email,
        ]);

        $user->linkedin_id = $this->linkedinUser->id;
        $user->linkedin_token = $this->linkedinUser->token;
        $user->email_verified_at = now();
        $user->save();

        return FindOrCreateLinkedinUserResult::success(
            user: $user,
            wasRecentlyCreated: true,
        );
    }
}
