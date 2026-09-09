<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Visits\Enums\VisitStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\VisitSearchRequest;
use App\Models\Department;
use App\Models\OrganizationSetting;
use App\Models\Visit;
use Carbon\Carbon;
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
            $fromUtc = Carbon::parse($request->input('date_from'), $tz)->startOfDay()->utc();
            $query->where('registered_at', '>=', $fromUtc);
        }

        if ($request->filled('date_to')) {
            $toUtc = Carbon::parse($request->input('date_to'), $tz)->endOfDay()->utc();
            $query->where('registered_at', '<=', $toUtc);
        }

        $visits = $query->latest('registered_at')->paginate(15)->withQueryString();
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

        $visit->load(['department', 'createdBy', 'statusHistories.changedBy']);
        $organization = OrganizationSetting::current();
        $tz = $organization?->timezone ?? 'Asia/Jakarta';
        $allowedNextStatuses = $visit->current_status->allowedNextStatuses();

        return view('admin.visits.show', compact('visit', 'organization', 'tz', 'allowedNextStatuses'));
    }
}
