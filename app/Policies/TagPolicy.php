<?php

namespace App\Policies;

use App\Models\Tag;
use App\Models\User;

class TagPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('tag.view');
    }

    public function view(User $user, Tag $tag): bool
    {
        return $user->can('tag.view');
    }

    public function create(User $user): bool
    {
        return $user->can('tag.create');
    }

    public function update(User $user, Tag $tag): bool
    {
        return $user->can('tag.update');
    }

    public function delete(User $user, Tag $tag): bool
    {
        return $user->can('tag.delete');
    }
}
