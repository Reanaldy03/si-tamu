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
     * A visit that is physically at the desk and therefore needs an operator
     * decision now. Drives the dashboard worklist.
     *
     * @return array<self>
     */
    public static function awaitingOperator(): array
    {
        return [self::ARRIVED, self::IN_SERVICE];
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

    /**
     * DESIGN.md §21.4 — status colours, aligned to the semantic palette.
     * Colour is never the only signal; the label always ships with it.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::REGISTERED => 'bg-slate-100 text-slate-700 border-slate-200',
            self::ARRIVED => 'bg-primary-50 text-primary-700 border-primary-200',
            self::IN_SERVICE => 'bg-warning-50 text-warning-800 border-warning-200',
            self::COMPLETED => 'bg-success-50 text-success-800 border-success-200',
            self::CANCELLED => 'bg-danger-50 text-danger-800 border-danger-200',
        };
    }

    /**
     * The dot that precedes the label in compact contexts (lists, steppers).
     */
    public function dotClasses(): string
    {
        return match ($this) {
            self::REGISTERED => 'bg-slate-400',
            self::ARRIVED => 'bg-primary-600',
            self::IN_SERVICE => 'bg-warning-500',
            self::COMPLETED => 'bg-success-600',
            self::CANCELLED => 'bg-danger-600',
        };
    }
}
