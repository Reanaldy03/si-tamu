<?php

namespace App\Application\Reporting\DTOs;

use Carbon\Carbon;

final class ReportFilterDTO
{
    public function __construct(
        public readonly ?string $dateFrom = null,
        public readonly ?string $dateTo = null,
        public readonly ?int $departmentId = null,
        public readonly ?string $status = null,
        public readonly ?string $query = null,
        public readonly string $timezone = 'Asia/Jakarta',
    ) {}

    /**
     * Build instance from validated array.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data, string $timezone = 'Asia/Jakarta'): self
    {
        return new self(
            dateFrom: ! empty($data['date_from']) ? (string) $data['date_from'] : null,
            dateTo: ! empty($data['date_to']) ? (string) $data['date_to'] : null,
            departmentId: ! empty($data['department_id']) ? (int) $data['department_id'] : null,
            status: ! empty($data['current_status']) ? (string) $data['current_status'] : (! empty($data['status']) ? (string) $data['status'] : null),
            query: ! empty($data['q']) ? trim((string) $data['q']) : null,
            timezone: $timezone,
        );
    }

    /**
     * Convert DTO to array for JSON persistence in report_exports.filters.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
            'department_id' => $this->departmentId,
            'current_status' => $this->status,
            'q' => $this->query,
            'timezone' => $this->timezone,
        ];
    }

    /**
     * Get UTC start-of-day for the dateFrom filter.
     */
    public function dateFromUtc(): ?Carbon
    {
        if (! $this->dateFrom) {
            return null;
        }

        return Carbon::parse($this->dateFrom, $this->timezone)->startOfDay()->utc();
    }

    /**
     * Get UTC end-of-day for the dateTo filter.
     */
    public function dateToUtc(): ?Carbon
    {
        if (! $this->dateTo) {
            return null;
        }

        return Carbon::parse($this->dateTo, $this->timezone)->endOfDay()->utc();
    }
}
