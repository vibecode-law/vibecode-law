<?php

namespace App\Policies;

use App\Models\User;

class SiteSettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('site-setting.view');
    }

    public function update(User $user): bool
    {
        return $user->can('site-setting.update');
    }
}
