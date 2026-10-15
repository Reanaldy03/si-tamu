<?php

namespace App\Http\Controllers\Admin;

use App\Application\Reporting\Actions\ExportVisitsAction;
use App\Application\Reporting\DTOs\ReportFilterDTO;
use App\Application\Reporting\Queries\VisitReportQuery;
use App\Domain\Reporting\Enums\ExportStatus;
use App\Domain\Visits\Enums\VisitStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ExportReportRequest;
use App\Http\Requests\Admin\ReportFilterRequest;
use App\Models\Department;
use App\Models\OrganizationSetting;
use App\Models\ReportExport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Display report filters, aggregated KPIs, and paginated visits preview.
     */
    public function index(ReportFilterRequest $request, VisitReportQuery $reportQuery): View
    {
        $organization = OrganizationSetting::current();
        $tz = $organization?->timezone ?? 'Asia/Jakarta';

        $filter = ReportFilterDTO::fromArray($request->validated(), $tz);
        $aggregates = $reportQuery->getAggregates($filter);
        $visits = $reportQuery->paginate($filter, 20);
        $departments = Department::orderBy('name')->get();
        $statuses = VisitStatus::cases();

        $recentExports = ReportExport::where('requested_by_user_id', $request->user()->id)
            ->latest('id')
            ->take(5)
            ->get();

        return view('admin.reports.index', compact(
            'filter',
            'aggregates',
            'visits',
            'departments',
            'statuses',
            'recentExports',
            'tz'
        ));
    }

    /**
     * Request an ephemeral report export in PDF or XLSX format.
     */
    public function export(ExportReportRequest $request, ExportVisitsAction $exportAction): RedirectResponse
    {
        $organization = OrganizationSetting::current();
        $tz = $organization?->timezone ?? 'Asia/Jakarta';

        $filter = ReportFilterDTO::fromArray($request->validated(), $tz);

        $export = $exportAction->execute(
            filter: $filter,
            format: $request->string('format')->toString(),
            requestedBy: $request->user(),
            requestId: $request->attributes->get('request_id'),
            ipAddress: $request->ip()
        );

        return redirect()
            ->route('admin.reports.index', $request->except(['format', '_token']))
            ->with('success', "Berkas ekspor {$export->format} berhasil dibuat ({$export->row_count} baris). Anda dapat mengunduhnya pada tabel riwayat ekspor.");
    }

    /**
     * Download the ephemeral exported file if authorized and not expired.
     */
    public function download(ReportExport $export, Request $request): StreamedResponse
    {
        $this->authorize('download', $export);

        if ($export->status !== ExportStatus::COMPLETED || ($export->expires_at && $export->expires_at->isPast())) {
            abort(410, 'Berkas ekspor ini telah kedaluwarsa atau tidak lagi tersedia.');
        }

        if (! Storage::disk($export->storage_disk)->exists($export->storage_path)) {
            abort(404, 'Berkas fisik tidak ditemukan di sistem penyimpanan server.');
        }

        $export->increment('download_count');
        $export->update(['last_downloaded_at' => now()]);

        return Storage::disk($export->storage_disk)->download($export->storage_path, $export->file_name);
    }
}
