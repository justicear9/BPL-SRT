<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VisitOrder;

class VisitOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return ! $user->isSalesRep();
    }

    public function view(User $user, VisitOrder $visitOrder): bool
    {
        return ! $user->isSalesRep();
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, VisitOrder $visitOrder): bool
    {
        return false;
    }

    public function delete(User $user, VisitOrder $visitOrder): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }

    public function restore(User $user, VisitOrder $visitOrder): bool
    {
        return false;
    }

    public function forceDelete(User $user, VisitOrder $visitOrder): bool
    {
        return false;
    }
}
