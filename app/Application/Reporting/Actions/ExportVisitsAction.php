<?php

namespace App\Application\Reporting\Actions;

use App\Application\Audit\Actions\RecordAuditEventAction;
use App\Application\Reporting\DTOs\ReportFilterDTO;
use App\Application\Reporting\Queries\VisitReportQuery;
use App\Application\Reporting\Writers\PdfReportWriter;
use App\Application\Reporting\Writers\XlsxReportWriter;
use App\Domain\Reporting\Enums\ExportStatus;
use App\Domain\Shared\Contracts\PublicIdGenerator;
use App\Models\ReportExport;
use App\Models\User;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ExportVisitsAction
{
    public function __construct(
        private readonly VisitReportQuery $reportQuery,
        private readonly PublicIdGenerator $idGenerator,
        private readonly PdfReportWriter $pdfWriter,
        private readonly XlsxReportWriter $xlsxWriter,
        private readonly RecordAuditEventAction $recordAudit,
    ) {}

    /**
     * Execute report generation and ephemeral export.
     */
    public function execute(
        ReportFilterDTO $filter,
        string $format,
        User $requestedBy,
        ?string $requestId = null,
        ?string $ipAddress = null
    ): ReportExport {
        $format = strtoupper(trim($format));
        if (! in_array($format, ['PDF', 'XLSX'], true)) {
            throw new \InvalidArgumentException("Format ekspor tidak didukung: {$format}");
        }

        $publicId = $this->idGenerator->generate();
        $ext = strtolower($format);
        $fileName = 'laporan-kunjungan-'.now()->format('Ymd-His').'.'.$ext;
        $storageDir = 'exports/'.now()->format('Y/m');
        $storagePath = "{$storageDir}/{$publicId}.{$ext}";

        // Ensure storage directory exists on local disk
        Storage::disk('local')->makeDirectory($storageDir);

        $export = ReportExport::create([
            'public_id' => $publicId,
            'requested_by_user_id' => $requestedBy->id,
            'format' => $format,
            'status' => ExportStatus::PROCESSING,
            'filters' => $filter->toArray(),
            'storage_disk' => 'local',
            'storage_path' => $storagePath,
            'file_name' => $fileName,
        ]);

        $tempFile = tempnam(sys_get_temp_dir(), 'sitamu_export_');

        try {
            $records = $this->reportQuery->cursor($filter);
            $aggregates = $this->reportQuery->getAggregates($filter);

            if ($format === 'PDF') {
                $rowCount = $this->pdfWriter->write($tempFile, $filter, $records, $aggregates);
            } else {
                $rowCount = $this->xlsxWriter->write($tempFile, $filter, $records, $aggregates);
            }

            // Put file into private storage
            Storage::disk('local')->putFileAs($storageDir, new File($tempFile), "{$publicId}.{$ext}");
            @unlink($tempFile);

            $export->update([
                'status' => ExportStatus::COMPLETED,
                'row_count' => $rowCount,
                'completed_at' => now(),
                'expires_at' => now()->addHours(24),
            ]);

            // Audit log
            $this->recordAudit->execute(
                eventType: 'report.export_requested',
                actorType: 'USER',
                actorUserId: $requestedBy->id,
                targetType: 'report_exports',
                targetId: $export->id,
                targetPublicId: $export->public_id,
                requestId: $requestId,
                ipAddress: $ipAddress,
                metadata: [
                    'format' => $format,
                    'row_count' => $rowCount,
                    'filters' => $filter->toArray(),
                ]
            );

            return $export;
        } catch (Throwable $e) {
            @unlink($tempFile);

            $export->update([
                'status' => ExportStatus::FAILED,
                'failed_at' => now(),
                'failure_code' => 'WRITE_ERROR',
            ]);

            throw $e;
        }
    }
}
