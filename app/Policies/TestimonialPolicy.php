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
        return $user->can('testimonial.create');
    }

    public function update(User $user, Testimonial $testimonial): bool
    {
        return $user->can('testimonial.update');
    }

    public function delete(User $user, Testimonial $testimonial): bool
    {
        return $user->can('testimonial.delete');
    }
}
