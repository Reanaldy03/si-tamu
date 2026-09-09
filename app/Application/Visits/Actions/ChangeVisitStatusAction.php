<?php

namespace App\Application\Visits\Actions;

use App\Application\Audit\Actions\RecordAuditEventAction;
use App\Application\Visits\DTOs\ChangeVisitStatusDTO;
use App\Domain\Visits\Rules\VisitStatusTransitionRule;
use App\Models\Visit;
use App\Models\VisitStatusHistory;
use Illuminate\Support\Facades\DB;

class ChangeVisitStatusAction
{
    public function __construct(
        protected VisitStatusTransitionRule $transitionRule,
        protected RecordAuditEventAction $recordAuditEvent,
    ) {}

    /**
     * Execute atomic status transition with row-level locking.
     */
    public function execute(ChangeVisitStatusDTO $dto): Visit
    {
        return DB::transaction(function () use ($dto) {
            // 1. Lock visit record for update to prevent concurrent race conditions
            $visit = Visit::where('id', $dto->visitId)
                ->lockForUpdate()
                ->firstOrFail();

            // 2. Validate status transition according to state machine rules
            $fromStatus = $visit->current_status;
            $this->transitionRule->validate($fromStatus, $dto->toStatus);

            $now = now();

            // 3. Update current status and timestamp on visit
            $visit->current_status = $dto->toStatus;
            $visit->status_updated_at = $now;
            $visit->save();

            // 4. Record history entry
            VisitStatusHistory::create([
                'visit_id' => $visit->id,
                'from_status' => $fromStatus,
                'to_status' => $dto->toStatus,
                'changed_by_user_id' => $dto->changedByUserId,
                'source' => $dto->source,
                'reason' => $dto->reason,
                'request_id' => $dto->requestId,
                'changed_at' => $now,
            ]);

            // 5. Append audit log
            $this->recordAuditEvent->execute(
                eventType: 'visit.status_changed',
                actorType: $dto->changedByUserId ? 'USER' : 'SYSTEM',
                actorUserId: $dto->changedByUserId,
                targetType: 'visit',
                targetId: $visit->id,
                targetPublicId: $visit->public_id,
                requestId: $dto->requestId,
                ipAddress: $dto->ipAddress,
                metadata: [
                    'reason' => $dto->reason,
                    'source' => $dto->source,
                ],
                beforeState: [
                    'current_status' => $fromStatus->value,
                ],
                afterState: [
                    'current_status' => $dto->toStatus->value,
                    'status_updated_at' => $now->toIso8601String(),
                ],
                occurredAt: $now,
            );

            return $visit;
        });
    }
}
