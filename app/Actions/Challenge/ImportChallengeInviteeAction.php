<?php

namespace App\Actions\Challenge;

use App\Actions\User\GenerateUniqueUserHandleAction;
use App\Exceptions\SkippedImportRowException;
use App\Models\Challenge\ChallengeInviteCode;
use App\Models\User;
use App\Notifications\Challenge\ChallengeInvitation;
use App\Services\User\ProfileService;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ImportChallengeInviteeAction
{
    public function __construct(
        private GenerateUniqueUserHandleAction $generateHandle,
        private ProfileService $profileService,
        private AcceptChallengeInviteCodeAction $acceptInviteCode,
    ) {}

    /**
     * @param  array<string, string|null>  $row
     *
     * @throws SkippedImportRowException
     */
    public function import(ChallengeInviteCode $inviteCode, array $row, ?string $customMessage): void
    {
        $data = $this->validatedRow($row);

        $existingUser = $this->findExistingUser($data['email']);

        if ($existingUser !== null) {
            $this->inviteExistingUser(
                inviteCode: $inviteCode,
                user: $existingUser,
                customMessage: $customMessage,
            );

            return;
        }

        $this->inviteNewUser(
            inviteCode: $inviteCode,
            data: $data,
            customMessage: $customMessage,
        );
    }

    private function findExistingUser(string $email): ?User
    {
        return User::query()
            ->where('email', Str::lower($email))
            ->first();
    }

    /**
     * @throws SkippedImportRowException
     */
    private function inviteExistingUser(ChallengeInviteCode $inviteCode, User $user, ?string $customMessage): void
    {
        if ($this->acceptInviteCode->alreadyHasSufficientAccess(inviteCode: $inviteCode, user: $user) === true) {
            throw new SkippedImportRowException('Already has access to this challenge.');
        }

        $this->acceptInviteCode->accept(inviteCode: $inviteCode, user: $user);

        $user->notify(new ChallengeInvitation(
            inviteCode: $inviteCode,
            isNewUser: false,
            customMessage: $customMessage,
        ));
    }

    /**
     * @param  array<string, string|null>  $data
     */
    private function inviteNewUser(ChallengeInviteCode $inviteCode, array $data, ?string $customMessage): void
    {
        $user = $this->createUser($data);

        $this->acceptInviteCode->accept(inviteCode: $inviteCode, user: $user);

        /** @var PasswordBroker $broker */
        $broker = Password::broker();

        $user->notify(new ChallengeInvitation(
            inviteCode: $inviteCode,
            isNewUser: true,
            passwordToken: $broker->createToken(user: $user),
            customMessage: $customMessage,
        ));
    }

    /**
     * @param  array<string, string|null>  $data
     */
    private function createUser(array $data): User
    {
        return $this->profileService->create(
            data: [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'handle' => $this->generateHandle->generate(
                    firstName: $data['first_name'],
                    lastName: $data['last_name'],
                ),
                'email' => $data['email'],
                'organisation' => $data['organisation'],
                'job_title' => $data['job_title'],
                'linkedin_url' => $data['linkedin_url'],
                'bio' => $data['bio'],
            ],
            emailVerified: true,
        );
    }

    /**
     * @param  array<string, string|null>  $row
     * @return array<string, string|null>
     *
     * @throws SkippedImportRowException
     */
    private function validatedRow(array $row): array
    {
        $data = [
            'email' => $this->trimmed($row['email'] ?? null),
            'first_name' => $this->trimmed($row['first_name'] ?? null),
            'last_name' => $this->trimmed($row['last_name'] ?? null),
            'organisation' => $this->trimmed($row['organisation'] ?? null),
            'job_title' => $this->trimmed($row['job_title'] ?? null),
            'linkedin_url' => $this->trimmed($row['linkedin_url'] ?? null),
            'bio' => $this->trimmed($row['bio'] ?? null),
        ];

        $validator = Validator::make($data, [
            'email' => ['required', 'string', 'email'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'organisation' => ['nullable', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'linkedin_url' => ['nullable', 'string', 'url', 'max:255'],
            'bio' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($validator->fails() === true) {
            throw new SkippedImportRowException($validator->errors()->first());
        }

        return $data;
    }

    private function trimmed(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}
