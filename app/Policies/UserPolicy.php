<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_superadmin === true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        return $user->is_superadmin === true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->is_superadmin === true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        return $user->is_superadmin === true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->is_superadmin === true && $user->id !== $model->id;
    }

    /**
     * Determine whether the user can toggle submissions for the model.
     */
    public function toggleSubmissions(User $user, User $model): bool
    {
        return $user->is_superadmin === true;
    }

    /**
     * Determine whether the user can send a password reset email to the model.
     */
    public function sendPasswordReset(User $user, User $model): bool
    {
        return $user->is_superadmin === true;
    }
}
