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

    public function badgeClasses(): string
    {
        return match ($this) {
            self::REGISTERED => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            self::ARRIVED => 'bg-amber-50 text-amber-700 border-amber-200',
            self::IN_SERVICE => 'bg-sky-50 text-sky-700 border-sky-200',
            self::COMPLETED => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::CANCELLED => 'bg-rose-50 text-rose-700 border-rose-200',
        };
    }
}
