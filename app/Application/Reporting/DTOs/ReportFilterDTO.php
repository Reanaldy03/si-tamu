<?php

namespace App\Application\Reporting\DTOs;

use Illuminate\Support\Carbon;

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
     * Human-readable report period, shared by the PDF and XLSX writers.
     */
    public function periodLabel(): string
    {
        $label = fn (?string $date): string => Carbon::parse($date)->locale('id')->translatedFormat('j F Y');

        return match (true) {
            $this->dateFrom && $this->dateTo => $label($this->dateFrom).' s.d. '.$label($this->dateTo),
            (bool) $this->dateFrom => 'Sejak '.$label($this->dateFrom),
            (bool) $this->dateTo => 'Sampai dengan '.$label($this->dateTo),
            default => 'Seluruh Periode',
        };
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
}
