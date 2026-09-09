<?php

namespace App\Application\Visits\DTOs;

use App\Domain\Visits\Enums\VisitStatus;

readonly class ChangeVisitStatusDTO
{
    public function __construct(
        public int $visitId,
        public VisitStatus $toStatus,
        public ?int $changedByUserId = null,
        public string $source = 'STAFF',
        public ?string $reason = null,
        public ?string $requestId = null,
        public ?string $ipAddress = null,
    ) {}
}
