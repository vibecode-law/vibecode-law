<?php

namespace App\Policies;

use App\Models\Challenge\Challenge;
use App\Models\User;

class ChallengePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('challenge.view');
    }

    public function view(User $user, Challenge $challenge): bool
    {
        return $user->can('challenge.view');
    }

    public function create(User $user): bool
    {
        return $user->can('challenge.create');
    }

    public function update(User $user, Challenge $challenge): bool
    {
        return $user->can('challenge.update');
    }

    public function delete(User $user, Challenge $challenge): bool
    {
        return $user->can('challenge.delete');
    }

    public function manageInviteCodes(User $user, Challenge $challenge): bool
    {
        return $user->can('challenge.update');
    }
}
