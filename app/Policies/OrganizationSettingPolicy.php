<?php

namespace App\Policies;

use App\Models\User;

class OrganizationSettingPolicy
{
    /**
     * Determine whether the user can manage organization settings.
     */
    public function manage(User $user): bool
    {
        return $user->hasPermission('settings.manage');
    }
}
