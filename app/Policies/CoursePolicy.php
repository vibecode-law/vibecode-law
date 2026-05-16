<?php

namespace App\Policies;

use App\Models\Course\Course;
use App\Models\User;

class CoursePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('course.view');
    }

    public function view(User $user, Course $course): bool
    {
        return $user->can('course.view');
    }

    public function create(User $user): bool
    {
        return $user->can('course.create');
    }

    public function update(User $user, Course $course): bool
    {
        return $user->can('course.update');
    }
}
