<?php

namespace App\Policies;

use App\Models\User;

class ReportPolicy
{
    /**
     * Determine whether the user can view report aggregates and preview.
     */
    public function view(User $user): bool
    {
        return $user->hasPermission('reports.view');
    }

    /**
     * Determine whether the user can generate and export reports.
     */
    public function export(User $user): bool
    {
        return $user->hasPermission('reports.export');
    }
}
