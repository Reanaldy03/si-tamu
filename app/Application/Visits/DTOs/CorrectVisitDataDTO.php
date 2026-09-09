<?php

namespace App\Application\Visits\DTOs;

readonly class CorrectVisitDataDTO
{
    /**
     * @param  array<string, mixed>  $correctedAttributes  Allowlisted fields to update
     */
    public function __construct(
        public int $visitId,
        public int $actorUserId,
        public array $correctedAttributes,
        public string $reason,
        public ?string $requestId = null,
        public ?string $ipAddress = null,
    ) {}
}
