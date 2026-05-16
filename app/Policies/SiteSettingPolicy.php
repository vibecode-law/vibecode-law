<?php

namespace App\Policies;

use App\Models\User;

class SiteSettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_superadmin === true;
    }

    public function update(User $user): bool
    {
        return $user->is_superadmin === true;
    }
}
