<?php

namespace App\Policies\Showcase;

use App\Models\Showcase\Showcase;
use App\Models\Showcase\ShowcaseDraft;
use App\Models\User;

class ShowcaseDraftPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ShowcaseDraft $draft): bool
    {
        if ($user->is_admin === true) {
            return true;
        }

        if ($user->can('showcase.approve-reject')) {
            return true;
        }

        /** @var Showcase $showcase */
        $showcase = $draft->showcase;

        return $user->id === $showcase->user_id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ShowcaseDraft $draft): bool
    {
        if ($user->is_admin === true) {
            return true;
        }

        if ($user->can('showcase.approve-reject')) {
            return true;
        }

        if ($user->isBlockedFromSubmissions()) {
            return false;
        }

        /** @var Showcase $showcase */
        $showcase = $draft->showcase;

        // Only the showcase owner can update the draft
        if ($user->id !== $showcase->user_id) {
            return false;
        }

        // Can only edit drafts that are in Draft or Rejected status
        return $draft->canBeEdited();
    }

    /**
     * Determine whether the user can delete (discard) the model.
     */
    public function delete(User $user, ShowcaseDraft $draft): bool
    {
        if ($user->is_admin === true) {
            return true;
        }

        /** @var Showcase $showcase */
        $showcase = $draft->showcase;

        // Only the showcase owner can discard their draft
        return $user->id === $showcase->user_id;
    }

    /**
     * Determine whether the user can submit the draft for approval.
     */
    public function submit(User $user, ShowcaseDraft $draft): bool
    {
        if ($user->isBlockedFromSubmissions()) {
            return false;
        }

        /** @var Showcase $showcase */
        $showcase = $draft->showcase;

        // Only the showcase owner can submit the draft
        if ($user->id !== $showcase->user_id) {
            return false;
        }

        return $draft->canBeSubmitted();
    }

    /**
     * Determine whether the user can approve or reject drafts.
     */
    public function moderate(User $user): bool
    {
        return $user->can('showcase.approve-reject');
    }
}
