<?php

namespace App\Application\Audit\Actions;

use App\Models\AuditLog;
use Carbon\CarbonInterface;

class RecordAuditEventAction
{
    /**
     * Sensitive keys that must never be recorded in audit logs in plaintext.
     *
     * @var list<string>
     */
    protected const SENSITIVE_KEYS = [
        'password',
        'password_confirmation',
        'token',
        'remember_token',
        'visitor_identity_encrypted',
        'nik',
        'identity_number',
        'visitor_identity_number',
        'secret',
    ];

    /**
     * Record an audit event into audit_logs table.
     *
     * @param  array<string, mixed>|null  $metadata
     * @param  array<string, mixed>|null  $beforeState
     * @param  array<string, mixed>|null  $afterState
     */
    public function execute(
        string $eventType,
        string $actorType = 'SYSTEM',
        ?int $actorUserId = null,
        ?string $targetType = null,
        ?int $targetId = null,
        ?string $targetPublicId = null,
        ?string $requestId = null,
        ?string $ipAddress = null,
        ?array $metadata = null,
        ?array $beforeState = null,
        ?array $afterState = null,
        ?CarbonInterface $occurredAt = null,
    ): AuditLog {
        $ipHash = $ipAddress ? hash('sha256', $ipAddress) : null;

        return AuditLog::create([
            'event_type' => $eventType,
            'actor_type' => $actorType,
            'actor_user_id' => $actorUserId,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'target_public_id' => $targetPublicId,
            'request_id' => $requestId,
            'ip_hash' => $ipHash,
            'metadata' => $metadata ? $this->sanitize($metadata) : null,
            'before_state' => $beforeState ? $this->sanitize($beforeState) : null,
            'after_state' => $afterState ? $this->sanitize($afterState) : null,
            'occurred_at' => $occurredAt ?? now(),
        ]);
    }

    /**
     * Recursively sanitize sensitive keys from arrays.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function sanitize(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            $lowerKey = strtolower((string) $key);

            if (in_array($lowerKey, self::SENSITIVE_KEYS, true)) {
                $sanitized[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitize($value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }
}
