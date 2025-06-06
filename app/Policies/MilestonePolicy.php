<?php

namespace App\Policies;

use App\Models\Milestone;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MilestonePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // authorize if user is admin
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Milestone $milestone): bool
    {
        // authorize if user is admin
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // authorize if user is admin
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Milestone $milestone): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Milestone $milestone): bool
    {
        return $user->isAdmin();
    }
}
