<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageAllVisits();
    }

    public function view(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return true;
        }

        return $user->canManageAllVisits();
    }

    public function create(User $user): bool
    {
        return $user->canManageAllVisits();
    }

    public function update(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return true;
        }

        return $user->canManageAllVisits();
    }

    public function delete(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return false;
        }

        return $user->canManageAllVisits();
    }

    public function deleteAny(User $user): bool
    {
        return $user->canManageAllVisits();
    }

    public function restore(User $user, User $model): bool
    {
        return false;
    }

    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Whether the authenticated user may assign the given role when creating/updating users.
     */
    public function assignRole(User $user, UserRole $role): bool
    {
        if (! $user->canManageAllVisits()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        // Manager: promote reps or peers, but never create another admin.
        return in_array($role, [UserRole::SalesRep, UserRole::Manager], true);
    }
}
