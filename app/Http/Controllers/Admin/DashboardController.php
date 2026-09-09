<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Visits\Enums\VisitStatus;
use App\Http\Controllers\Controller;
use App\Models\OrganizationSetting;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the operational back-office dashboard.
     */
    public function index(Request $request): View
    {
        $organization = OrganizationSetting::current();
        $tz = $organization?->timezone ?? 'Asia/Jakarta';

        $todayStartUtc = now($tz)->startOfDay()->utc();
        $todayEndUtc = now($tz)->endOfDay()->utc();

        $todayQuery = Visit::whereBetween('registered_at', [$todayStartUtc, $todayEndUtc]);

        $totalToday = (clone $todayQuery)->count();
        $waitingCount = (clone $todayQuery)->whereIn('current_status', [VisitStatus::REGISTERED, VisitStatus::ARRIVED])->count();
        $inServiceCount = (clone $todayQuery)->where('current_status', VisitStatus::IN_SERVICE)->count();
        $completedCount = (clone $todayQuery)->where('current_status', VisitStatus::COMPLETED)->count();

        $latestVisits = (clone $todayQuery)
            ->with('department')
            ->latest('registered_at')
            ->take(10)
            ->get();

        return view('admin.dashboard', compact(
            'organization',
            'tz',
            'totalToday',
            'waitingCount',
            'inServiceCount',
            'completedCount',
            'latestVisits'
        ));
    }
}
