<?php

namespace App\Application\Visits\Actions;

use App\Application\Audit\Actions\RecordAuditEventAction;
use App\Application\Visits\DTOs\CorrectVisitDataDTO;
use App\Models\Visit;
use Illuminate\Support\Facades\DB;

class CorrectVisitDataAction
{
    /**
     * Fields allowed to be corrected by authorized administrator.
     *
     * @var list<string>
     */
    protected const ALLOWED_FIELDS = [
        'visitor_name',
        'origin_organization',
        'contact_phone',
        'visitor_email',
        'visitor_address',
        'visitor_occupation',
        'visitor_gender',
        'visitor_identity_type',
        'visitor_identity_encrypted',
        'party_size',
        'purpose',
        'notes',
    ];

    public function __construct(
        protected RecordAuditEventAction $recordAuditEvent,
    ) {}

    /**
     * Execute atomic correction of visit record with before/after audit diff.
     */
    public function execute(CorrectVisitDataDTO $dto): Visit
    {
        return DB::transaction(function () use ($dto) {
            $visit = Visit::where('id', $dto->visitId)
                ->lockForUpdate()
                ->firstOrFail();

            $beforeState = [];
            $afterState = [];
            $updates = [];

            foreach ($dto->correctedAttributes as $field => $newValue) {
                if (in_array($field, self::ALLOWED_FIELDS, true)) {
                    $oldValue = $visit->getAttribute($field);

                    if ($oldValue !== $newValue) {
                        $beforeState[$field] = $oldValue;
                        $afterState[$field] = $newValue;
                        $updates[$field] = $newValue;
                    }
                }
            }

            if (! empty($updates)) {
                $visit->fill($updates);
                $visit->save();

                $this->recordAuditEvent->execute(
                    eventType: 'visit.data_corrected',
                    actorType: 'USER',
                    actorUserId: $dto->actorUserId,
                    targetType: 'visit',
                    targetId: $visit->id,
                    targetPublicId: $visit->public_id,
                    requestId: $dto->requestId,
                    ipAddress: $dto->ipAddress,
                    metadata: [
                        'reason' => $dto->reason,
                        'fields_modified' => array_keys($updates),
                    ],
                    beforeState: $beforeState,
                    afterState: $afterState,
                    occurredAt: now(),
                );
            }

            return $visit;
        });
    }
}
