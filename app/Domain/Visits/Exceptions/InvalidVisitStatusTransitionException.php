<?php

namespace App\Domain\Visits\Exceptions;

use App\Domain\Visits\Enums\VisitStatus;
use DomainException;

class InvalidVisitStatusTransitionException extends DomainException
{
    public function __construct(
        public readonly ?VisitStatus $fromStatus,
        public readonly VisitStatus $toStatus,
        string $message = ''
    ) {
        $from = $fromStatus ? $fromStatus->value : 'NULL';
        $msg = $message ?: "Transisi status kunjungan tidak valid: tidak dapat berpindah dari [{$from}] ke [{$toStatus->value}].";
        parent::__construct($msg);
    }

    public static function forTransition(?VisitStatus $fromStatus, VisitStatus $toStatus): self
    {
        return new self($fromStatus, $toStatus);
    }
}
