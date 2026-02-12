<?php

namespace App\Policies;

use App\Models\Organisation\Organisation;
use App\Models\User;

class OrganisationPolicy
{
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
    public function update(User $user, Organisation $organisation): bool
    {
        return $user->is_admin === true;
    }
}
