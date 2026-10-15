<?php

namespace App\Application\Reporting\Queries;

use App\Application\Reporting\DTOs\ReportFilterDTO;
use App\Domain\Visits\Enums\VisitStatus;
use App\Models\Visit;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\LazyCollection;

class VisitReportQuery
{
    /**
     * Build the query builder with all validated filter criteria.
     *
     * @return Builder<Visit>
     */
    public function build(ReportFilterDTO $filter): Builder
    {
        $query = Visit::query()->with('department');

        if ($filter->query) {
            $kw = $filter->query;
            $query->where(function (Builder $sub) use ($kw) {
                $sub->where('visitor_name', 'like', "%{$kw}%")
                    ->orWhere('origin_organization', 'like', "%{$kw}%")
                    ->orWhere('public_id', 'like', "%{$kw}%");
            });
        }

        if ($filter->departmentId) {
            $query->where('department_id', $filter->departmentId);
        }

        if ($filter->status) {
            $query->where('current_status', $filter->status);
        }

        if ($filter->dateFrom) {
            $query->where('visit_date', '>=', $filter->dateFrom);
        }

        if ($filter->dateTo) {
            $query->where('visit_date', '<=', $filter->dateTo);
        }

        return $query->orderByDesc('visit_date')->orderByDesc('registered_at');
    }

    /**
     * Get aggregate statistics matching the filter criteria.
     *
     * @return array<string, int>
     */
    public function getAggregates(ReportFilterDTO $filter): array
    {
        $baseQuery = $this->build($filter);

        $totalVisits = (clone $baseQuery)->count();
        $completedCount = (clone $baseQuery)->where('current_status', VisitStatus::COMPLETED->value)->count();
        $cancelledCount = (clone $baseQuery)->where('current_status', VisitStatus::CANCELLED->value)->count();
        $activeCount = (clone $baseQuery)->whereIn('current_status', [
            VisitStatus::REGISTERED->value,
            VisitStatus::ARRIVED->value,
            VisitStatus::IN_SERVICE->value,
        ])->count();

        return [
            'total_visits' => $totalVisits,
            'completed_count' => $completedCount,
            'cancelled_rejected_count' => $cancelledCount,
            'active_count' => $activeCount,
        ];
    }

    /**
     * Paginate results for preview.
     *
     * @return LengthAwarePaginator<Visit>
     */
    public function paginate(ReportFilterDTO $filter, int $perPage = 20): LengthAwarePaginator
    {
        return $this->build($filter)->paginate($perPage)->withQueryString();
    }

    /**
     * Stream records using cursor for memory-efficient export.
     *
     * @return LazyCollection<int, Visit>
     */
    public function cursor(ReportFilterDTO $filter): LazyCollection
    {
        return $this->build($filter)->cursor();
    }
}
