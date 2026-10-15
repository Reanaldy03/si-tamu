<?php

namespace App\Application\Visits\Actions;

use App\Application\Audit\Actions\RecordAuditEventAction;
use App\Application\Visits\Services\VisitQrTokenService;
use App\Domain\Visits\Enums\VisitStatus;
use App\Models\OrganizationSetting;
use App\Models\Visit;
use App\Models\VisitStatusHistory;
use DomainException;
use Illuminate\Support\Facades\DB;

class CheckInVisitViaQrAction
{
    public function __construct(
        protected VisitQrTokenService $qrTokenService,
        protected RecordAuditEventAction $recordAuditEvent,
    ) {}

    /**
     * Execute atomic check-in transaction via QR scan payload or manual ULID.
     *
     * @throws DomainException
     */
    public function execute(string $rawInput, int $actorUserId, ?string $requestId = null, ?string $ipAddress = null): Visit
    {
        $parsed = $this->qrTokenService->parsePayload($rawInput);

        if (empty($parsed['public_id'])) {
            throw new DomainException('Data QR Code atau nomor kunjungan tidak valid.');
        }

        return DB::transaction(function () use ($parsed, $actorUserId, $requestId, $ipAddress) {
            // 1. Lock visit record for update to guarantee atomicity and race-condition safety
            $visit = Visit::where('public_id', $parsed['public_id'])
                ->lockForUpdate()
                ->first();

            if (! $visit) {
                throw new DomainException("Data kunjungan dengan ID '{$parsed['public_id']}' tidak ditemukan.");
            }

            // 2. Validate cryptographic QR token if provided
            if ($parsed['token'] !== null) {
                $computedHash = $this->qrTokenService->hashToken($parsed['token']);
                if ($visit->qr_token && ! hash_equals($visit->qr_token, $computedHash)) {
                    throw new DomainException('QR Code tidak valid atau telah dimanipulasi.');
                }
            }

            // 3. Invariant check: only REGISTERED status can be checked-in
            if ($visit->current_status === VisitStatus::ARRIVED) {
                $tz = 'Asia/Jakarta';
                try {
                    $tz = OrganizationSetting::current()?->timezone ?? config('app.timezone', 'Asia/Jakarta');
                } catch (\Throwable) {
                }
                $tzAbbr = match ($tz) {
                    'Asia/Makassar' => 'WITA',
                    'Asia/Jayapura' => 'WIT',
                    default => 'WIB',
                };
                $checkedTime = $visit->checked_in_at ? $visit->checked_in_at->timezone($tz)->format('H:i') : 'sebelumnya';
                throw new DomainException("Tamu '{$visit->visitor_name}' sudah melakukan check-in pada pukul {$checkedTime} {$tzAbbr}.");
            }

            if ($visit->current_status === VisitStatus::IN_SERVICE || $visit->current_status === VisitStatus::COMPLETED) {
                throw new DomainException('Kunjungan ini sudah dalam proses pelayanan atau telah selesai.');
            }

            if ($visit->current_status === VisitStatus::CANCELLED) {
                throw new DomainException('Kunjungan ini telah dibatalkan dan tidak dapat melakukan check-in.');
            }

            if ($visit->current_status !== VisitStatus::REGISTERED) {
                throw new DomainException("Status kunjungan ({$visit->current_status->label()}) tidak valid untuk check-in.");
            }

            $now = now();
            $fromStatus = $visit->current_status;

            // 4. Update status and checked_in_at
            $visit->current_status = VisitStatus::ARRIVED;
            $visit->checked_in_at = $now;
            $visit->status_updated_at = $now;
            $visit->save();

            $method = $parsed['token'] !== null ? 'QR_SCAN' : 'MANUAL_ULID';

            // 5. Append status history
            VisitStatusHistory::create([
                'visit_id' => $visit->id,
                'from_status' => $fromStatus,
                'to_status' => VisitStatus::ARRIVED,
                'changed_by_user_id' => $actorUserId,
                'source' => 'STAFF',
                'reason' => 'Check-in resepsionis via '.($method === 'QR_SCAN' ? 'pemindaian QR Code' : 'input ULID manual'),
                'request_id' => $requestId,
                'changed_at' => $now,
            ]);

            // 6. Record audit event
            $this->recordAuditEvent->execute(
                eventType: 'visit.checked_in_qr',
                actorType: 'USER',
                actorUserId: $actorUserId,
                targetType: 'visit',
                targetId: $visit->id,
                targetPublicId: $visit->public_id,
                requestId: $requestId,
                ipAddress: $ipAddress,
                metadata: [
                    'method' => $method,
                    'department' => $visit->department_name_snapshot,
                    'visitor_name' => $visit->visitor_name,
                ],
                beforeState: [
                    'current_status' => $fromStatus->value,
                ],
                afterState: [
                    'current_status' => VisitStatus::ARRIVED->value,
                    'checked_in_at' => $now->toIso8601String(),
                ],
                occurredAt: $now,
            );

            return $visit;
        });
    }
}
