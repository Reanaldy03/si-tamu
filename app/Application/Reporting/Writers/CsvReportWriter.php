<?php

namespace App\Application\Reporting\Writers;

use App\Application\Reporting\Services\FormulaSanitizer;
use App\Models\Visit;
use Illuminate\Support\LazyCollection;

class CsvReportWriter
{
    /**
     * Standard export header columns.
     *
     * @var list<string>
     */
    public const HEADERS = [
        'No. Kunjungan',
        'Waktu Registrasi',
        'Nama Tamu',
        'Instansi Asal',
        'No. Telepon',
        'Email',
        'Unit Kerja Tujuan',
        'Keperluan',
        'Status',
        'Sumber Registrasi',
    ];

    /**
     * Write visit records to a CSV file stream.
     *
     * @param  resource  $stream
     * @param  LazyCollection<int, Visit>  $records
     */
    public function write($stream, LazyCollection $records, string $timezone = 'Asia/Jakarta'): int
    {
        // 1. Write UTF-8 BOM for Microsoft Excel compatibility
        fwrite($stream, "\xEF\xBB\xBF");

        // 2. Write headers
        fputcsv($stream, self::HEADERS);

        $rowCount = 0;

        // 3. Stream data rows efficiently
        foreach ($records as $visit) {
            $row = [
                FormulaSanitizer::sanitize($visit->public_id),
                FormulaSanitizer::sanitize($visit->registered_at->timezone($timezone)->format('d/m/Y H:i:s')),
                FormulaSanitizer::sanitize($visit->visitor_name),
                FormulaSanitizer::sanitize($visit->origin_organization),
                FormulaSanitizer::sanitize($visit->contact_phone),
                FormulaSanitizer::sanitize($visit->visitor_email),
                FormulaSanitizer::sanitize($visit->department?->name ?? $visit->department_name_snapshot),
                FormulaSanitizer::sanitize($visit->purpose),
                FormulaSanitizer::sanitize($visit->current_status->label()),
                FormulaSanitizer::sanitize($visit->registration_source?->value ?? 'PUBLIC'),
            ];

            fputcsv($stream, $row);
            $rowCount++;
        }

        return $rowCount;
    }
}
