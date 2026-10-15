<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Visits\Enums\VisitStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\VisitSearchRequest;
use App\Models\Department;
use App\Models\OrganizationSetting;
use App\Models\Visit;
use Illuminate\View\View;

class VisitController extends Controller
{
    /**
     * Display a paginated listing of visits with filtering.
     */
    public function index(VisitSearchRequest $request): View
    {
        $organization = OrganizationSetting::current();
        $tz = $organization?->timezone ?? 'Asia/Jakarta';

        $query = Visit::with('department');

        if ($request->filled('q')) {
            $q = trim($request->input('q'));
            $query->where(function ($sub) use ($q) {
                $sub->where('visitor_name', 'like', "%{$q}%")
                    ->orWhere('origin_organization', 'like', "%{$q}%")
                    ->orWhere('public_id', 'like', "%{$q}%");
            });
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->integer('department_id'));
        }

        if ($request->filled('current_status')) {
            $query->where('current_status', $request->input('current_status'));
        }

        if ($request->filled('date_from')) {
            $query->where('visit_date', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('visit_date', '<=', $request->input('date_to'));
        }

        $visits = $query->orderByDesc('visit_date')
            ->orderByDesc('registered_at')
            ->paginate(15)
            ->withQueryString();
        $departments = Department::orderBy('name')->get();
        $statuses = VisitStatus::cases();

        return view('admin.visits.index', compact('visits', 'departments', 'statuses', 'tz'));
    }

    /**
     * Display the detailed view of a single visit.
     */
    public function show(Visit $visit): View
    {
        $this->authorize('view', $visit);

        $visit->load(['department', 'createdBy', 'statusHistories.changedBy', 'feedback']);
        $organization = OrganizationSetting::current();
        $tz = $organization?->timezone ?? 'Asia/Jakarta';
        $allowedNextStatuses = $visit->current_status->allowedNextStatuses();

        return view('admin.visits.show', compact('visit', 'organization', 'tz', 'allowedNextStatuses'));
    }
}
