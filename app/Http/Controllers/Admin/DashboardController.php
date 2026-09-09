<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Visits\Enums\VisitStatus;
use App\Http\Controllers\Controller;
use App\Models\OrganizationSetting;
use App\Models\Visit;
use App\Models\VisitFeedback;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the operational back-office dashboard with interactive charts and IKM metrics.
     */
    public function index(Request $request): View
    {
        $organization = OrganizationSetting::current();
        $tz = $organization?->timezone ?? 'Asia/Jakarta';

        $todayStartUtc = now($tz)->startOfDay()->utc();
        $todayEndUtc = now($tz)->endOfDay()->utc();

        $todayQuery = Visit::whereBetween('registered_at', [$todayStartUtc, $todayEndUtc]);

        // Operational Counter Metrics
        $totalToday = (clone $todayQuery)->count();
        $waitingCount = (clone $todayQuery)->whereIn('current_status', [VisitStatus::REGISTERED, VisitStatus::ARRIVED])->count();
        $inServiceCount = (clone $todayQuery)->where('current_status', VisitStatus::IN_SERVICE)->count();
        $completedCount = (clone $todayQuery)->where('current_status', VisitStatus::COMPLETED)->count();

        // Guest Satisfaction (IKM) Metrics
        $ikmCount = VisitFeedback::count();
        $ikmAvg = $ikmCount > 0 ? (float) round(VisitFeedback::avg('rating'), 1) : 0.0;

        // Chart 1: Daily Visit Trend (Last 7 Days)
        $sevenDaysAgo = now($tz)->subDays(6)->startOfDay();
        $dailyRaw = Visit::where('registered_at', '>=', $sevenDaysAgo->utc())
            ->selectRaw('DATE(registered_at) as visit_date, count(*) as count')
            ->groupBy('visit_date')
            ->pluck('count', 'visit_date')
            ->all();

        $trendLabels = [];
        $trendValues = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now($tz)->subDays($i);
            $dateKey = $date->format('Y-m-d');
            $trendLabels[] = $date->format('d M');
            $trendValues[] = $dailyRaw[$dateKey] ?? 0;
        }

        // Chart 2: Department / Commission Distribution (Last 30 Days)
        $thirtyDaysAgo = now($tz)->subDays(29)->startOfDay();
        $deptStats = Visit::where('registered_at', '>=', $thirtyDaysAgo->utc())
            ->selectRaw('department_name_snapshot, count(*) as total')
            ->groupBy('department_name_snapshot')
            ->orderByDesc('total')
            ->take(6)
            ->get();

        $deptLabels = $deptStats->pluck('department_name_snapshot')->all();
        $deptValues = $deptStats->pluck('total')->all();

        // Chart 3: Origin Organization Proportion (Last 30 Days)
        $govCount = 0;
        $lsmCount = 0;
        $academicCount = 0;
        $generalCount = 0;

        $orgs = Visit::where('registered_at', '>=', $thirtyDaysAgo->utc())
            ->selectRaw('origin_organization, count(*) as count')
            ->groupBy('origin_organization')
            ->get();

        foreach ($orgs as $org) {
            $name = strtolower($org->origin_organization);
            if (preg_match('/dinas|badan|kementerian|kemen|pemko|pemkab|pemerintah|kelurahan|kecamatan|inspektorat|setda|polres|kodim|bawaslu|kpu/i', $name)) {
                $govCount += $org->count;
            } elseif (preg_match('/lsm|ormas|yayasan|media|pers|wartawan|komunitas|hmi|gmni|pmii|knpi/i', $name)) {
                $lsmCount += $org->count;
            } elseif (preg_match('/universitas|institut|sekolah|kampus|akadem|politeknik|mahasiswa|dosen|guru|siswa/i', $name)) {
                $academicCount += $org->count;
            } else {
                $generalCount += $org->count;
            }
        }

        $orgLabels = ['Instansi Pemerintah', 'LSM / Ormas / Media', 'Akademisi / Mahasiswa', 'Masyarakat / Swasta'];
        $orgValues = [$govCount, $lsmCount, $academicCount, $generalCount];

        // 10 Latest Visits Today
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
            'ikmAvg',
            'ikmCount',
            'trendLabels',
            'trendValues',
            'deptLabels',
            'deptValues',
            'orgLabels',
            'orgValues',
            'latestVisits'
        ));
    }
}
