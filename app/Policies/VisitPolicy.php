<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Visit;

class VisitPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Visit $visit): bool
    {
        return $user->canManageAllVisits() || $visit->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Visit $visit): bool
    {
        return $user->canManageAllVisits() || $visit->user_id === $user->id;
    }

    public function delete(User $user, Visit $visit): bool
    {
        return $user->canManageAllVisits() || $visit->user_id === $user->id;
    }

    public function deleteAny(User $user): bool
    {
        return true;
    }

    public function restore(User $user, Visit $visit): bool
    {
        return false;
    }

    public function forceDelete(User $user, Visit $visit): bool
    {
        return false;
    }
}
