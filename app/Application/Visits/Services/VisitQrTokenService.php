<?php

namespace App\Application\Visits\Services;

use App\Models\Visit;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class VisitQrTokenService
{
    /**
     * Generate secure raw token for the given visit public ID using HMAC-SHA256.
     */
    public function generateRawToken(string $publicId): string
    {
        $appKey = (string) config('app.key');

        return hash_hmac('sha256', $publicId, $appKey);
    }

    /**
     * Compute SHA-256 hash of the raw token for safe database persistence.
     */
    public function hashToken(string $rawToken): string
    {
        return hash('sha256', $rawToken);
    }

    /**
     * Generate JSON string payload to be encoded into the QR code.
     */
    public function generateQrPayload(Visit $visit): string
    {
        $rawToken = $this->generateRawToken($visit->public_id);

        return (string) json_encode([
            'app' => 'SI-TAMU',
            'v' => 1,
            'public_id' => $visit->public_id,
            'token' => $rawToken,
        ], JSON_UNESCAPED_SLASHES);
    }

    /**
     * Render QR Code SVG string for display or printing.
     */
    public function renderSvg(string $payload, int $size = 250): string
    {
        $svg = (string) QrCode::size($size)
            ->errorCorrection('H')
            ->margin(1)
            ->generate($payload);

        // Strip xml declaration if present for clean inline embedding in HTML
        return preg_replace('/<\?xml.*?\?>/i', '', $svg) ?? $svg;
    }

    /**
     * Parse raw scan data (JSON payload, delimited string, or plain ULID).
     *
     * @return array{public_id: string, token: ?string}
     */
    public function parsePayload(string $rawInput): array
    {
        $trimmed = trim($rawInput);

        // 1. Try JSON decoding
        if (str_starts_with($trimmed, '{') && str_ends_with($trimmed, '}')) {
            $decoded = json_decode($trimmed, true);
            if (is_array($decoded) && ! empty($decoded['public_id'])) {
                return [
                    'public_id' => trim((string) $decoded['public_id']),
                    'token' => ! empty($decoded['token']) ? trim((string) $decoded['token']) : null,
                ];
            }
        }

        // 2. Delimited string format: SI-TAMU:public_id:token
        if (str_contains($trimmed, ':')) {
            $parts = explode(':', $trimmed);
            if (count($parts) >= 3 && strtoupper($parts[0]) === 'SI-TAMU') {
                return [
                    'public_id' => trim($parts[1]),
                    'token' => trim($parts[2]),
                ];
            }
            if (count($parts) === 2) {
                return [
                    'public_id' => trim($parts[0]),
                    'token' => trim($parts[1]),
                ];
            }
        }

        // 3. Fallback: treat as raw public_id (manual ULID entry)
        return [
            'public_id' => $trimmed,
            'token' => null,
        ];
    }
}
