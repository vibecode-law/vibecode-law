<?php

namespace App\Policies;

use App\Models\Course\Lesson;
use App\Models\User;

class LessonPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('lesson.view');
    }

    public function view(User $user, Lesson $lesson): bool
    {
        return $user->can('lesson.view');
    }

    public function create(User $user): bool
    {
        return $user->can('lesson.create');
    }

    public function update(User $user, Lesson $lesson): bool
    {
        return $user->can('lesson.update');
    }
}
