<?php

namespace App\Policies;

use App\Models\AuditLog;
use App\Models\User;

class AuditLogPolicy
{
    /**
     * Determine whether the user can view any audit logs.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('audit.view');
    }

    /**
     * Determine whether the user can view a specific audit log entry.
     */
    public function view(User $user, AuditLog $auditLog): bool
    {
        return $user->hasPermission('audit.view');
    }
}
