<?php

namespace App\Domain\Visits\Rules;

use App\Domain\Visits\Enums\VisitStatus;
use App\Domain\Visits\Exceptions\InvalidVisitStatusTransitionException;

class VisitStatusTransitionRule
{
    /**
     * Determine whether transition from $fromStatus to $toStatus is allowed.
     */
    public function isAllowed(?VisitStatus $fromStatus, VisitStatus $toStatus): bool
    {
        // Initial creation transition: null -> REGISTERED
        if ($fromStatus === null) {
            return $toStatus === VisitStatus::REGISTERED;
        }

        return $fromStatus->canTransitionTo($toStatus);
    }

    /**
     * Validate transition and throw exception if illegal.
     *
     * @throws InvalidVisitStatusTransitionException
     */
    public function validate(?VisitStatus $fromStatus, VisitStatus $toStatus): void
    {
        if (! $this->isAllowed($fromStatus, $toStatus)) {
            throw InvalidVisitStatusTransitionException::forTransition($fromStatus, $toStatus);
        }
    }
}
