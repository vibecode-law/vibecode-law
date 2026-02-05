<?php

namespace App\Policies;

use App\Models\Testimonial;
use App\Models\User;

class TestimonialPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Testimonial $testimonial): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Moderator');
    }

    public function update(User $user, Testimonial $testimonial): bool
    {
        return $user->hasRole('Moderator');
    }

    public function delete(User $user, Testimonial $testimonial): bool
    {
        return $user->hasRole('Moderator');
    }
}
