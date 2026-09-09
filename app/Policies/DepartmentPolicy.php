<?php

namespace App\Policies;

use App\Models\User;

class DepartmentPolicy
{
    /**
     * Determine whether the user can view departments list.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('departments.manage') || $user->hasPermission('visits.view');
    }

    /**
     * Determine whether the user can manage departments (create, edit, reorder, toggle active).
     */
    public function manage(User $user): bool
    {
        return $user->hasPermission('departments.manage');
    }
}
