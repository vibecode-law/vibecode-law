<?php

namespace App\Policies;

use App\Models\PressCoverage;
use App\Models\User;

class PressCoveragePolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, PressCoverage $pressCoverage): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->can('press-coverage.create');
    }

    public function update(User $user, PressCoverage $pressCoverage): bool
    {
        return $user->can('press-coverage.update');
    }

    public function delete(User $user, PressCoverage $pressCoverage): bool
    {
        return $user->can('press-coverage.delete');
    }
}
