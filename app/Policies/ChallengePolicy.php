<?php

namespace App\Policies;

use App\Models\Challenge\Challenge;
use App\Models\User;

class ChallengePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_admin === true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Challenge $challenge): bool
    {
        return $user->is_admin === true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->is_admin === true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Challenge $challenge): bool
    {
        return $user->is_admin === true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Challenge $challenge): bool
    {
        return $user->is_admin === true;
    }
}
