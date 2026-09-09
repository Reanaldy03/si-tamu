<?php

namespace App\Application\Visits\Actions;

use App\Application\Audit\Actions\RecordAuditEventAction;
use App\Application\Visits\DTOs\RegisterVisitDTO;
use App\Application\Visits\Services\VisitQrTokenService;
use App\Domain\Shared\Contracts\PublicIdGenerator;
use App\Domain\Visits\Enums\VisitStatus;
use App\Models\Department;
use App\Models\Visit;
use App\Models\VisitStatusHistory;
use Illuminate\Support\Facades\DB;

class RegisterVisitAction
{
    public function __construct(
        protected PublicIdGenerator $publicIdGenerator,
        protected RecordAuditEventAction $recordAuditEvent,
        protected VisitQrTokenService $qrTokenService,
    ) {}

    /**
     * Execute atomic visit registration.
     */
    public function execute(RegisterVisitDTO $dto): Visit
    {
        // 1. Idempotency check: if token hash already exists, return existing visit
        $tokenHash = $dto->submissionToken ? hash('sha256', $dto->submissionToken) : null;
        if ($tokenHash) {
            $existing = Visit::where('submission_token_hash', $tokenHash)->first();
            if ($existing) {
                return $existing;
            }
        }

        return DB::transaction(function () use ($dto, $tokenHash) {
            // 2. Validate department existence and active status
            $department = Department::where('id', $dto->departmentId)
                ->where('is_active', true)
                ->firstOrFail();

            // 3. Generate ULID public ID and cryptographic QR token hash
            $publicId = $this->publicIdGenerator->generate();
            $rawToken = $this->qrTokenService->generateRawToken($publicId);
            $qrTokenHash = $this->qrTokenService->hashToken($rawToken);
            $now = now();

            // 4. Create Visit record
            $visit = Visit::create([
                'public_id' => $publicId,
                'submission_token_hash' => $tokenHash,
                'qr_token' => $qrTokenHash,
                'visitor_name' => $dto->visitorName,
                'origin_organization' => $dto->originOrganization,
                'contact_phone' => $dto->contactPhone,
                'visitor_email' => $dto->visitorEmail,
                'visitor_address' => $dto->visitorAddress,
                'visitor_occupation' => $dto->visitorOccupation,
                'visitor_gender' => $dto->visitorGender,
                'visitor_identity_type' => $dto->visitorIdentityType,
                'visitor_identity_encrypted' => $dto->visitorIdentityNumber,
                'party_size' => max(1, $dto->partySize),
                'department_id' => $department->id,
                'department_name_snapshot' => $department->name,
                'purpose' => $dto->purpose,
                'notes' => $dto->notes,
                'current_status' => VisitStatus::REGISTERED,
                'registration_source' => $dto->registrationSource,
                'created_by_user_id' => $dto->createdByUserId,
                'registered_at' => $now,
                'status_updated_at' => $now,
            ]);

            // 5. Create initial history record
            VisitStatusHistory::create([
                'visit_id' => $visit->id,
                'from_status' => null,
                'to_status' => VisitStatus::REGISTERED,
                'changed_by_user_id' => $dto->createdByUserId,
                'source' => $dto->registrationSource->value,
                'reason' => 'Registrasi awal kunjungan',
                'request_id' => $dto->requestId,
                'changed_at' => $now,
            ]);

            // 6. Record business audit event
            $this->recordAuditEvent->execute(
                eventType: 'visit.registered',
                actorType: $dto->createdByUserId ? 'USER' : 'PUBLIC',
                actorUserId: $dto->createdByUserId,
                targetType: 'visit',
                targetId: $visit->id,
                targetPublicId: $visit->public_id,
                requestId: $dto->requestId,
                ipAddress: $dto->ipAddress,
                metadata: [
                    'department_id' => $department->id,
                    'department_name' => $department->name,
                    'party_size' => $visit->party_size,
                    'registration_source' => $dto->registrationSource->value,
                ],
                afterState: [
                    'public_id' => $visit->public_id,
                    'visitor_name' => $visit->visitor_name,
                    'origin_organization' => $visit->origin_organization,
                    'current_status' => $visit->current_status->value,
                    'department_name_snapshot' => $visit->department_name_snapshot,
                ],
                occurredAt: $now,
            );

            return $visit;
        });
    }
}
