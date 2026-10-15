<?php

namespace App\Application\Reporting\Writers;

use App\Application\Reporting\DTOs\ReportFilterDTO;
use App\Infrastructure\Printing\Letterhead;
use App\Models\Department;
use App\Models\OrganizationSetting;
use App\Models\Visit;
use Barryvdh\DomPDF\Facade\Pdf;
use Dompdf\Canvas;
use Dompdf\FontMetrics;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\LazyCollection;

class PdfReportWriter
{
    /** F4 (215 x 330 mm) in points, the paper size of the institution's printed forms. */
    private const PAGE_WIDTH = 609.6;

    private const PAGE_HEIGHT = 935.5;

    public function __construct(private readonly Letterhead $letterhead) {}

    /**
     * Render the formal F4 portrait report. Dompdf buffers the document; queue
     * large reports if document sizes grow beyond the current operational scale.
     *
     * @param  LazyCollection<int, Visit>  $records
     * @param  array<string, int>  $aggregates
     */
    public function write(string $destinationPath, ReportFilterDTO $filter, LazyCollection $records, array $aggregates): int
    {
        $organization = OrganizationSetting::current();
        $department = $filter->departmentId ? Department::find($filter->departmentId) : null;
        $logoDataUri = $this->resolveLogo($organization?->logo_path);
        [$kopStreet, $kopCity] = $this->letterheadLines($organization?->address);

        $pdf = Pdf::loadView('reports.pdf.visits', [
            'organization' => $organization,
            'department' => $department,
            'filter' => $filter,
            'aggregates' => $aggregates,
            'records' => $records,
            'generatedAt' => Carbon::now($filter->timezone)->locale('id'),
            'logoDataUri' => $logoDataUri,
            'kopStreet' => $kopStreet,
            'kopCity' => $kopCity,
            'fonts' => $this->fontFiles(),
        ])->setPaper([0, 0, self::PAGE_WIDTH, self::PAGE_HEIGHT])
            ->setOption('isRemoteEnabled', false)
            ->setOption('defaultFont', 'serif');

        $pdf->render();
        $this->drawPageChrome($pdf->getDomPDF()->getCanvas());

        if (file_put_contents($destinationPath, $pdf->output()) === false) {
            throw new \RuntimeException("Gagal menulis berkas PDF pada path: {$destinationPath}");
        }

        return $aggregates['total_visits'];
    }

    /**
     * Embed the organization logo as a PNG data URI. GD normalizes whatever
     * raster format was uploaded (png, jpg, gif, webp) into a format Dompdf
     * always understands; the bundled public logo is the fallback.
     */
    private function resolveLogo(?string $logoPath): ?string
    {
        $candidates = [];

        if ($logoPath && Storage::disk('public')->exists($logoPath)) {
            $candidates[] = Storage::disk('public')->get($logoPath);
        }

        foreach ([public_path('logo.webp'), public_path('images/logo.webp')] as $fallback) {
            if (is_file($fallback)) {
                $candidates[] = (string) file_get_contents($fallback);
            }
        }

        foreach ($candidates as $bytes) {
            // ponytail: GD covers raster logos only; an SVG upload falls back to text-only kop.
            $image = @imagecreatefromstring($bytes);

            if ($image === false) {
                continue;
            }

            ob_start();
            imagepng($image);
            $png = (string) ob_get_clean();
            imagedestroy($image);

            return 'data:image/png;base64,'.base64_encode($png);
        }

        return null;
    }

    /**
     * Locate the two letterhead faces used by the institution's printed forms
     * (Haettenschweiler for the kop, Times New Roman for the body), and split
     * the stored address into the two letterhead lines. Delegated so the
     * visitor pass renders the identical kop.
     *
     * @return array<string, string|null>
     */
    private function fontFiles(): array
    {
        return $this->letterhead->faces();
    }

    /**
     * @return array{string, string}
     */
    private function letterheadLines(?string $address): array
    {
        return $this->letterhead->addressLines($address);
    }

    /**
     * Paint the page chrome Dompdf's CSS cannot express: the two full-bleed
     * kop rules on page one, then the running footer. The footer is painted
     * after layout because Dompdf resolves `counter(pages)` to 0.
     */
    private function drawPageChrome(Canvas $canvas): void
    {
        $canvas->page_script(function (int $pageNumber, int $pageCount, Canvas $canvas, FontMetrics $fontMetrics): void {
            $width = $canvas->get_width();

            if ($pageNumber === 1) {
                // Thin rule above a heavy rule, as on the institution's letterhead.
                $canvas->line(0.0, 119.1, $width, 119.1, [0, 0, 0], 0.75);
                $canvas->line(0.0, 121.75, $width, 121.75, [0, 0, 0], 2.5);
            }

            $font = $fontMetrics->getFont('times', 'normal');
            $size = 7.0;
            $left = 34.0;
            $right = $width - 34.0;
            $baseline = $canvas->get_height() - 24.0;
            $label = "Halaman {$pageNumber} dari {$pageCount}";

            $canvas->line($left, $baseline - 10.0, $right, $baseline - 10.0, [0.6, 0.6, 0.6], 0.5);
            $canvas->text($left, $baseline, 'SI-TAMU | Dokumen laporan kedinasan', $font, $size);
            $canvas->text($right - $fontMetrics->getTextWidth($label, $font, $size), $baseline, $label, $font, $size);
        });
    }
}
