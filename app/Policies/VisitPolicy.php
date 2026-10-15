<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Visit;

class VisitPolicy
{
    /**
     * Determine whether the user can view any visits.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('visits.view');
    }

    /**
     * Determine whether the user can view the visit.
     */
    public function view(User $user, Visit $visit): bool
    {
        return $user->hasPermission('visits.view');
    }

    /**
     * Determine whether the user can update the status of the visit.
     */
    public function updateStatus(User $user, Visit $visit): bool
    {
        return $user->hasPermission('visits.update_status');
    }

    /**
     * Determine whether the user can perform QR/manual check-in.
     */
    public function checkIn(User $user): bool
    {
        return $user->hasPermission('visits.update_status');
    }

    /**
     * Determine whether the user can correct visit data.
     */
    public function correct(User $user, Visit $visit): bool
    {
        return $user->hasPermission('visits.correct');
    }
}
