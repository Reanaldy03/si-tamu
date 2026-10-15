<?php

namespace App\Policies;

use App\Models\ReportExport;
use App\Models\User;

class ReportPolicy
{
    /**
     * Determine whether the user can view report aggregates and preview.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('reports.view');
    }

    /**
     * Determine whether the user can view report details or preview.
     */
    public function view(User $user, ?ReportExport $reportExport = null): bool
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

    /**
     * Determine whether the user can create an export request.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('reports.export');
    }

    /**
     * Determine whether the user can download an exported file.
     */
    public function download(User $user, ReportExport $reportExport): bool
    {
        return $user->hasPermission('reports.export')
            && $reportExport->requested_by_user_id === $user->id;
    }
}
