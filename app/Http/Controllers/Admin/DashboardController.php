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

        // Operational counter metrics. Every figure below is scoped to "hari
        // ini" and the view labels it as such, so a quiet day never looks like
        // a broken chart next to a populated 30-day donut.
        $totalToday = (clone $todayQuery)->count();
        $inServiceCount = (clone $todayQuery)->where('current_status', VisitStatus::IN_SERVICE)->count();
        $completedCount = (clone $todayQuery)->where('current_status', VisitStatus::COMPLETED)->count();

        // Worklist: guests who are physically at the desk and need an operator
        // decision right now. Deliberately NOT day-scoped — someone still
        // standing in the lobby at 00:05 must not vanish from the queue. The
        // counter is the true total; the list is capped so the page stays light.
        $awaitingStatuses = array_map(
            fn (VisitStatus $status): string => $status->value,
            VisitStatus::awaitingOperator(),
        );

        $waitingCount = Visit::query()
            ->whereIn('current_status', $awaitingStatuses)
            ->count();

        $waitingVisits = Visit::query()
            ->whereIn('current_status', $awaitingStatuses)
            ->with(['department'])
            ->orderBy('checked_in_at')
            ->orderBy('registered_at')
            ->limit(20)
            ->get();

        // Guest Satisfaction (IKM) Metrics
        $ikmCount = VisitFeedback::count();
        $ikmAvg = $ikmCount > 0 ? (float) round(VisitFeedback::avg('rating'), 1) : 0.0;

        // Chart 1: Visit Trend (Flexible: 7 Hari / 30 Hari / 12 Bulan / Tahun Ini)
        // 1A. Weekly (Last 7 Days)
        $sevenDaysAgo = now($tz)->subDays(6)->startOfDay();
        $dailyRaw7 = Visit::where('registered_at', '>=', $sevenDaysAgo->utc())
            ->selectRaw('DATE(registered_at) as day, count(*) as count')
            ->groupBy('day')
            ->pluck('count', 'day')
            ->all();

        $trendWeeklyLabels = [];
        $trendWeeklyValues = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now($tz)->subDays($i);
            $dateKey = $date->format('Y-m-d');
            $trendWeeklyLabels[] = $date->format('d M');
            $trendWeeklyValues[] = $dailyRaw7[$dateKey] ?? 0;
        }

        // 1B. Monthly (Last 30 Days)
        $thirtyDaysAgo = now($tz)->subDays(29)->startOfDay();
        $dailyRaw30 = Visit::where('registered_at', '>=', $thirtyDaysAgo->utc())
            ->selectRaw('DATE(registered_at) as day, count(*) as count')
            ->groupBy('day')
            ->pluck('count', 'day')
            ->all();

        $trendMonthlyLabels = [];
        $trendMonthlyValues = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now($tz)->subDays($i);
            $dateKey = $date->format('Y-m-d');
            $trendMonthlyLabels[] = $date->format('d M');
            $trendMonthlyValues[] = $dailyRaw30[$dateKey] ?? 0;
        }

        // 1C. Yearly (Last 12 Months)
        $twelveMonthsAgo = now($tz)->subMonths(11)->startOfMonth();
        $monthlyRaw12 = Visit::where('registered_at', '>=', $twelveMonthsAgo->utc())
            ->selectRaw('substr(registered_at, 1, 7) as ym, count(*) as count')
            ->groupBy('ym')
            ->pluck('count', 'ym')
            ->all();

        $trendYearlyLabels = [];
        $trendYearlyValues = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now($tz)->subMonths($i);
            $ymKey = $date->format('Y-m');
            $trendYearlyLabels[] = $date->format('M Y');
            $trendYearlyValues[] = $monthlyRaw12[$ymKey] ?? 0;
        }

        // 1D. This Year (Jan - Dec)
        $currentYear = now($tz)->year;
        $startOfYear = now($tz)->startOfYear();
        $endOfYear = now($tz)->endOfYear();
        $thisYearRaw = Visit::whereBetween('registered_at', [$startOfYear->utc(), $endOfYear->utc()])
            ->selectRaw('substr(registered_at, 1, 7) as ym, count(*) as count')
            ->groupBy('ym')
            ->pluck('count', 'ym')
            ->all();

        $trendThisYearLabels = [];
        $trendThisYearValues = [];
        for ($m = 1; $m <= 12; $m++) {
            $date = now($tz)->setYear($currentYear)->setMonth($m)->startOfMonth();
            $ymKey = $date->format('Y-m');
            $trendThisYearLabels[] = $date->format('M');
            $trendThisYearValues[] = $thisYearRaw[$ymKey] ?? 0;
        }

        $trendPeriods = [
            '7d' => [
                'key' => '7d',
                'name' => 'Mingguan',
                'badge' => '7 Hari Terakhir',
                'title' => 'Tren Kunjungan Mingguan',
                'subtitle' => 'Jumlah pengunjung harian dalam 7 hari terakhir',
                'labels' => $trendWeeklyLabels,
                'values' => $trendWeeklyValues,
                'total' => array_sum($trendWeeklyValues),
            ],
            '30d' => [
                'key' => '30d',
                'name' => 'Bulanan',
                'badge' => '30 Hari Terakhir',
                'title' => 'Tren Kunjungan Bulanan',
                'subtitle' => 'Jumlah pengunjung harian dalam 30 hari terakhir',
                'labels' => $trendMonthlyLabels,
                'values' => $trendMonthlyValues,
                'total' => array_sum($trendMonthlyValues),
            ],
            '12m' => [
                'key' => '12m',
                'name' => 'Tahunan',
                'badge' => '12 Bulan Terakhir',
                'title' => 'Tren Kunjungan Tahunan',
                'subtitle' => 'Jumlah pengunjung per bulan dalam 12 bulan terakhir',
                'labels' => $trendYearlyLabels,
                'values' => $trendYearlyValues,
                'total' => array_sum($trendYearlyValues),
            ],
            'this_year' => [
                'key' => 'this_year',
                'name' => 'Tahun '.$currentYear,
                'badge' => 'Tahun '.$currentYear,
                'title' => 'Tren Kunjungan Tahun '.$currentYear,
                'subtitle' => 'Jumlah pengunjung per bulan di tahun '.$currentYear,
                'labels' => $trendThisYearLabels,
                'values' => $trendThisYearValues,
                'total' => array_sum($trendThisYearValues),
            ],
        ];

        $defaultPeriod = (string) $request->query('period', '7d');
        if (! isset($trendPeriods[$defaultPeriod])) {
            $defaultPeriod = '7d';
        }

        $trendLabels = $trendPeriods[$defaultPeriod]['labels'];
        $trendValues = $trendPeriods[$defaultPeriod]['values'];

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

        // 10 Latest Visits across all days — the panel must never look empty
        // while data exists just because nothing happened today.
        $latestVisits = Visit::query()
            ->with('department')
            ->latest('registered_at')
            ->take(10)
            ->get();

        return view('admin.dashboard', compact(
            'organization',
            'tz',
            'totalToday',
            'waitingCount',
            'waitingVisits',
            'inServiceCount',
            'completedCount',
            'ikmAvg',
            'ikmCount',
            'trendLabels',
            'trendValues',
            'trendPeriods',
            'defaultPeriod',
            'deptLabels',
            'deptValues',
            'orgLabels',
            'orgValues',
            'latestVisits'
        ));
    }
}
