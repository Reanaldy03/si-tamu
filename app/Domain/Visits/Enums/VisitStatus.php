<?php

namespace App\Domain\Visits\Enums;

enum VisitStatus: string
{
    case REGISTERED = 'REGISTERED';
    case ARRIVED = 'ARRIVED';
    case IN_SERVICE = 'IN_SERVICE';
    case COMPLETED = 'COMPLETED';
    case CANCELLED = 'CANCELLED';

    public function label(): string
    {
        return match ($this) {
            self::REGISTERED => 'Terdaftar',
            self::ARRIVED => 'Tiba',
            self::IN_SERVICE => 'Sedang Dilayani',
            self::COMPLETED => 'Selesai',
            self::CANCELLED => 'Dibatalkan',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::COMPLETED, self::CANCELLED], true);
    }

    /**
     * @return array<self>
     */
    public function allowedNextStatuses(): array
    {
        return match ($this) {
            self::REGISTERED => [self::ARRIVED, self::CANCELLED],
            self::ARRIVED => [self::IN_SERVICE, self::CANCELLED],
            self::IN_SERVICE => [self::COMPLETED, self::CANCELLED],
            self::COMPLETED, self::CANCELLED => [],
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, $this->allowedNextStatuses(), true);
    }
}
