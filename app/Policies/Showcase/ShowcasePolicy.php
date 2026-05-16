<?php

namespace App\Policies\Showcase;

use App\Models\Showcase\Showcase;
use App\Models\User;

class ShowcasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Showcase $showcase): bool
    {
        if ($showcase->isApproved()) {
            return true;
        }

        if ($user && $user->id === $showcase->user_id) {
            return true;
        }

        if ($user && $user->is_superadmin) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isBlockedFromSubmissions() === false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Showcase $showcase): bool
    {
        if ($user->is_superadmin) {
            return true;
        }

        // Staff with moderation permissions can update any showcase
        if ($user->can('showcase.approve-reject')) {
            return true;
        }

        if ($user->isBlockedFromSubmissions()) {
            return false;
        }

        if ($user->id === $showcase->user_id) {
            // Normal users cannot edit pending or approved showcases
            if ($showcase->isPending() === true || $showcase->isApproved() === true) {
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Showcase $showcase): bool
    {
        if ($user->is_superadmin) {
            return true;
        }

        if ($user->id === $showcase->user_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Showcase $showcase): bool
    {
        if ($user->is_superadmin) {
            return true;
        }

        if ($user->id === $showcase->user_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Showcase $showcase): bool
    {
        if ($user->is_superadmin) {
            return true;
        }

        if ($user->id === $showcase->user_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can approve or reject models
     */
    public function toggleApproval(User $user): bool
    {
        return $user->can('showcase.approve-reject');
    }

    /**
     * Determine whether the user can toggle featured status.
     */
    public function toggleFeatured(User $user): bool
    {
        return $user->can('showcase.feature');
    }

    /**
     * Determine whether the user can create a draft for the showcase.
     */
    public function createDraft(User $user, Showcase $showcase): bool
    {
        if ($user->isBlockedFromSubmissions()) {
            return false;
        }

        // Only the owner can create a draft
        if ($user->id !== $showcase->user_id) {
            return false;
        }

        // Can only create a draft for approved showcases
        if ($showcase->isApproved() === false) {
            return false;
        }

        // Note: The check for existing draft is handled in the controller
        // which redirects to the existing draft instead of throwing a 403

        return true;
    }
}
