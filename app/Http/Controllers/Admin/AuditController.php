<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\OrganizationSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditController extends Controller
{
    /**
     * Display a listing of immutable business audit logs.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', AuditLog::class);

        $organization = OrganizationSetting::current();
        $tz = $organization?->timezone ?? 'Asia/Jakarta';

        $query = AuditLog::with('actorUser');

        if ($request->filled('q')) {
            $q = trim($request->input('q'));
            $query->where(function ($sub) use ($q) {
                $sub->where('target_public_id', 'like', "%{$q}%")
                    ->orWhere('event_type', 'like', "%{$q}%")
                    ->orWhere('target_type', 'like', "%{$q}%");
            });
        }

        if ($request->filled('event_type')) {
            $query->where('event_type', $request->input('event_type'));
        }

        if ($request->filled('actor_user_id')) {
            $query->where('actor_user_id', $request->integer('actor_user_id'));
        }

        if ($request->filled('date_from')) {
            $fromUtc = Carbon::parse($request->input('date_from'), $tz)->startOfDay()->utc();
            $query->where('occurred_at', '>=', $fromUtc);
        }

        if ($request->filled('date_to')) {
            $toUtc = Carbon::parse($request->input('date_to'), $tz)->endOfDay()->utc();
            $query->where('occurred_at', '<=', $toUtc);
        }

        $audits = $query->latest('id')->paginate(20)->withQueryString();
        $users = User::orderBy('name')->get();
        $eventTypes = AuditLog::select('event_type')->distinct()->pluck('event_type');

        return view('admin.audits.index', compact('audits', 'users', 'eventTypes', 'tz'));
    }

    /**
     * Display the specified audit log entry details.
     */
    public function show(AuditLog $audit): View
    {
        $this->authorize('view', $audit);

        $audit->load('actorUser');
        $organization = OrganizationSetting::current();
        $tz = $organization?->timezone ?? 'Asia/Jakarta';

        return view('admin.audits.show', compact('audit', 'tz'));
    }
}
