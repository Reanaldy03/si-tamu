<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view the list of back-office users.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('users.manage');
    }

    /**
     * Determine whether the user can manage back-office users (create, update, toggle active, assign roles).
     */
    public function manage(User $user): bool
    {
        return $user->hasPermission('users.manage');
    }
}
