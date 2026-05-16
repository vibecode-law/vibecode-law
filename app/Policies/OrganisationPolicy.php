<?php

namespace App\Policies;

use App\Models\Organisation\Organisation;
use App\Models\User;

class OrganisationPolicy
{
    public function create(User $user): bool
    {
        return $user->can('organisation.create');
    }

    public function update(User $user, Organisation $organisation): bool
    {
        return $user->can('organisation.update');
    }
}
