<?php

namespace App\Application\Visits\DTOs;

use App\Domain\Visits\Enums\RegistrationSource;

readonly class RegisterVisitDTO
{
    public function __construct(
        public string $visitorName,
        public string $originOrganization,
        public int $departmentId,
        public string $purpose,
        public ?string $contactPhone = null,
        public ?string $visitorEmail = null,
        public ?string $visitorAddress = null,
        public ?string $visitorOccupation = null,
        public ?string $visitorGender = null,
        public ?string $visitorIdentityType = null,
        public ?string $visitorIdentityNumber = null,
        public int $partySize = 1,
        public ?string $notes = null,
        public RegistrationSource $registrationSource = RegistrationSource::PUBLIC,
        public ?int $createdByUserId = null,
        public ?string $submissionToken = null,
        public ?string $requestId = null,
        public ?string $ipAddress = null,
    ) {}
}
