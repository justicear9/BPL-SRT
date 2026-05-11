<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;

class CustomerPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Customer $customer): bool
    {
        if ($user->canManageAllVisits()) {
            return true;
        }

        return $customer->isAssignedTo($user);
    }

    public function create(User $user): bool
    {
        return $user->canManageAllVisits();
    }

    public function import(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Customer $customer): bool
    {
        if ($user->canManageAllVisits()) {
            return true;
        }

        return $customer->isAssignedTo($user);
    }

    public function delete(User $user, Customer $customer): bool
    {
        return $user->canManageAllVisits();
    }

    public function deleteAny(User $user): bool
    {
        return $user->canManageAllVisits();
    }

    public function restore(User $user, Customer $customer): bool
    {
        return false;
    }

    public function forceDelete(User $user, Customer $customer): bool
    {
        return false;
    }
}
