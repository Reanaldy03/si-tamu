<?php

namespace App\Application\Reporting\Writers;

use App\Application\Reporting\DTOs\ReportFilterDTO;
use App\Application\Reporting\Services\FormulaSanitizer;
use App\Domain\Visits\Enums\VisitStatus;
use App\Models\Department;
use App\Models\OrganizationSetting;
use App\Models\Visit;
use Illuminate\Support\Carbon;
use Illuminate\Support\LazyCollection;
use XMLWriter;
use ZipArchive;

class XlsxReportWriter
{
    /**
     * Table header, in sheet column order A..L.
     *
     * @var list<string>
     */
    public const HEADERS = [
        'No.',
        'Nama Tamu',
        'Instansi Asal',
        'Tanggal Kunjungan',
        'Nomor Surat',
        'No. Telepon',
        'Email',
        'Unit Kerja Tujuan',
        'Keperluan',
        'Status',
        'Sumber Registrasi',
        'Tanggal Registrasi',
    ];

    /** Header row, per the printed "rincian" form. */
    private const HEADER_ROW = 5;

    private const FIRST_DATA_ROW = 6;

    /**
     * Column letters from A to L.
     *
     * @var list<string>
     */
    private const COLUMNS = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];

    /**
     * Sheet column widths, matching the proportions of the printed form.
     *
     * @var list<float>
     */
    private const WIDTHS = [6.57, 26, 24, 15, 18, 15, 26, 24, 32, 16, 22, 16];

    /**
     * cellXfs indices, resolved in getStylesXml().
     */
    private const S_HEAD_FIRST = 2;

    private const S_HEAD = 1;

    private const S_HEAD_LAST = 3;

    private const S_DATA_FIRST = 5;

    private const S_DATA = 4;

    private const S_DATA_LAST = 6;

    private const S_TOTAL_LABEL = 7;

    private const S_TOTAL_VALUE = 8;

    private const S_PLAIN = 9;

    private const S_DESCRIPTION = 10;

    /**
     * Write visit records to an XLSX file laid out like the printed
     * "rincian" form: description block, bordered table, summary row,
     * and signature block.
     *
     * @param  LazyCollection<int, Visit>  $records
     * @param  array<string, int>  $aggregates
     */
    public function write(string $destinationPath, ReportFilterDTO $filter, LazyCollection $records, array $aggregates): int
    {
        $organization = OrganizationSetting::current();
        $generatedAt = Carbon::now($filter->timezone)->locale('id');
        $description = $this->description($filter, $organization, $generatedAt);
        $summary = sprintf(
            '%s kunjungan: %s selesai, %s berjalan, %s dibatalkan/ditolak.',
            number_format($aggregates['total_visits'], 0, ',', '.'),
            number_format($aggregates['completed_count'], 0, ',', '.'),
            number_format($aggregates['active_count'], 0, ',', '.'),
            number_format($aggregates['cancelled_rejected_count'], 0, ',', '.'),
        );

        $tempSheetXml = tempnam(sys_get_temp_dir(), 'sitamu_sheet_');
        $xml = new XMLWriter;
        $xml->openURI($tempSheetXml);
        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElement('worksheet');
        $xml->writeAttribute('xmlns', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $xml->startElement('sheetPr');
        $xml->startElement('pageSetUpPr');
        $xml->writeAttribute('fitToPage', '1');
        $xml->endElement();
        $xml->endElement();
        $xml->startElement('sheetViews');
        $xml->startElement('sheetView');
        $xml->writeAttribute('workbookViewId', '0');
        $xml->startElement('pane');
        $xml->writeAttribute('ySplit', (string) self::HEADER_ROW);
        $xml->writeAttribute('topLeftCell', 'A'.(self::HEADER_ROW + 1));
        $xml->writeAttribute('activePane', 'bottomLeft');
        $xml->writeAttribute('state', 'frozen');
        $xml->endElement();
        $xml->endElement();
        $xml->endElement();
        $xml->startElement('cols');
        foreach (self::WIDTHS as $index => $width) {
            $column = (string) ($index + 1);
            $xml->startElement('col');
            $xml->writeAttribute('min', $column);
            $xml->writeAttribute('max', $column);
            $xml->writeAttribute('width', (string) $width);
            $xml->writeAttribute('customWidth', '1');
            $xml->endElement();
        }
        $xml->endElement();
        $xml->startElement('sheetData');

        // "Daftar :" description block, as on the printed form.
        $xml->startElement('row');
        $xml->writeAttribute('r', '1');
        $this->cell($xml, 'A1', 'Daftar :', self::S_PLAIN);
        $this->cell($xml, 'B1', $description, self::S_DESCRIPTION);
        $xml->endElement();
        $xml->startElement('row');
        $xml->writeAttribute('r', '4');
        $xml->endElement();

        $xml->startElement('row');
        $xml->writeAttribute('r', (string) self::HEADER_ROW);
        foreach (self::HEADERS as $colIndex => $headerText) {
            $style = match ($colIndex) {
                0 => self::S_HEAD_FIRST,
                count(self::HEADERS) - 1 => self::S_HEAD_LAST,
                default => self::S_HEAD,
            };
            $this->cell($xml, self::COLUMNS[$colIndex].self::HEADER_ROW, $headerText, $style);
        }
        $xml->endElement();

        $rowIndex = self::FIRST_DATA_ROW;
        $rowCount = 0;

        foreach ($records as $visit) {
            $values = [
                (string) ($rowCount + 1),
                FormulaSanitizer::sanitize($visit->visitor_name),
                FormulaSanitizer::sanitize($visit->origin_organization),
                FormulaSanitizer::sanitize($visit->visit_date?->format('d/m/Y') ?? ''),
                FormulaSanitizer::sanitize($visit->letter_number),
                FormulaSanitizer::sanitize($visit->contact_phone),
                FormulaSanitizer::sanitize($visit->visitor_email),
                FormulaSanitizer::sanitize($visit->department?->name ?? $visit->department_name_snapshot),
                FormulaSanitizer::sanitize($visit->purpose),
                FormulaSanitizer::sanitize($visit->current_status->label()),
                FormulaSanitizer::sanitize($visit->registration_source?->label() ?? 'Mandiri (Publik)'),
                FormulaSanitizer::sanitize($visit->registered_at->timezone($filter->timezone)->format('d/m/Y')),
            ];

            $xml->startElement('row');
            $xml->writeAttribute('r', (string) $rowIndex);

            foreach ($values as $colIndex => $val) {
                $style = match ($colIndex) {
                    0 => self::S_DATA_FIRST,
                    count($values) - 1 => self::S_DATA_LAST,
                    default => self::S_DATA,
                };
                $this->cell($xml, self::COLUMNS[$colIndex].$rowIndex, $val, $style);
            }

            $xml->endElement();
            $rowIndex++;
            $rowCount++;
        }

        // Summary row: "JUMLAH" + the recap counts.
        $totalRow = $rowIndex;
        $xml->startElement('row');
        $xml->writeAttribute('r', (string) $totalRow);
        $this->cell($xml, 'A'.$totalRow, 'JUMLAH', self::S_TOTAL_LABEL);
        $this->cell($xml, 'C'.$totalRow, $summary, self::S_TOTAL_VALUE);
        $xml->endElement();

        // Signature block, anchored on column G as on the printed form.
        foreach ([
            $totalRow + 2 => 'Binjai, '.$generatedAt->translatedFormat('j F Y'),
            $totalRow + 3 => 'SEKRETARIS DPRD KOTA BINJAI',
            $totalRow + 5 => '...............................................',
            $totalRow + 6 => 'NIP. ...............................................',
        ] as $signRow => $text) {
            $xml->startElement('row');
            $xml->writeAttribute('r', (string) $signRow);
            $this->cell($xml, 'G'.$signRow, $text, self::S_PLAIN);
            $xml->endElement();
        }

        $xml->endElement(); // sheetData
        $xml->startElement('autoFilter');
        $xml->writeAttribute('ref', 'A'.self::HEADER_ROW.':L'.self::HEADER_ROW);
        $xml->endElement();
        $xml->startElement('mergeCells');
        $xml->writeAttribute('count', '3');
        foreach (['B1:L3', 'A'.$totalRow.':B'.$totalRow, 'C'.$totalRow.':L'.$totalRow] as $merge) {
            $xml->startElement('mergeCell');
            $xml->writeAttribute('ref', $merge);
            $xml->endElement();
        }
        $xml->endElement();
        $xml->startElement('pageMargins');
        foreach (['left' => '0.23622047244094491', 'right' => '0', 'top' => '0.31496062992125984', 'bottom' => '0', 'header' => '0.19685039370078741', 'footer' => '0.31496062992125984'] as $name => $value) {
            $xml->writeAttribute($name, $value);
        }
        $xml->endElement();
        $xml->startElement('pageSetup');
        $xml->writeAttribute('paperSize', '9');
        $xml->writeAttribute('orientation', 'landscape');
        $xml->writeAttribute('fitToWidth', '1');
        $xml->writeAttribute('fitToHeight', '0');
        $xml->endElement();
        $xml->endElement(); // worksheet
        $xml->endDocument();
        $xml->flush();
        unset($xml);

        // Package into ZIP / XLSX
        $zip = new ZipArchive;
        if ($zip->open($destinationPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            @unlink($tempSheetXml);
            throw new \RuntimeException("Gagal membuat file XLSX pada path: {$destinationPath}");
        }

        $zip->addFromString('[Content_Types].xml', $this->getContentTypesXml());
        $zip->addFromString('_rels/.rels', $this->getRelsXml());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->getWorkbookRelsXml());
        $zip->addFromString('xl/workbook.xml', $this->getWorkbookXml());
        $zip->addFromString('xl/styles.xml', $this->getStylesXml());
        $zip->addFile($tempSheetXml, 'xl/worksheets/sheet1.xml');
        $zip->close();

        @unlink($tempSheetXml);

        return $rowCount;
    }

    /**
     * Describe the report scope, mirroring the "Daftar :" line of the form.
     */
    private function description(ReportFilterDTO $filter, ?OrganizationSetting $organization, Carbon $generatedAt): string
    {
        $department = $filter->departmentId ? Department::find($filter->departmentId) : null;
        $parts = [
            'Daftar kunjungan tamu '.($organization?->organization_name ?? 'Sekretariat DPRD Kota Binjai'),
            'periode '.$filter->periodLabel(),
            'unit kerja '.($department?->name ?? 'Semua Unit Kerja'),
            'status '.($filter->status ? VisitStatus::from($filter->status)->label() : 'Semua Status'),
        ];

        if ($filter->query) {
            $parts[] = 'kata kunci "'.$filter->query.'"';
        }

        return implode(', ', $parts).'. Dicetak pada '.$generatedAt->translatedFormat('j F Y, H:i').' '.$generatedAt->format('T').'.';
    }

    /**
     * Write one inline-string cell; empty values are left as blank cells.
     */
    private function cell(XMLWriter $xml, string $ref, ?string $value, int $style): void
    {
        $xml->startElement('c');
        $xml->writeAttribute('r', $ref);
        $xml->writeAttribute('s', (string) $style);

        if ($value !== null && $value !== '') {
            $xml->writeAttribute('t', 'inlineStr');
            $xml->startElement('is');
            $xml->startElement('t');

            if ($value !== trim($value)) {
                $xml->writeAttribute('xml:space', 'preserve');
            }

            $xml->text($value);
            $xml->endElement();
            $xml->endElement();
        }

        $xml->endElement();
    }

    private function getContentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            .'</Types>';
    }

    private function getRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>';
    }

    private function getWorkbookRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            .'</Relationships>';
    }

    private function getWorkbookXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets>'
            .'<sheet name="Laporan Kunjungan" sheetId="1" r:id="rId1"/>'
            .'</sheets>'
            .'</workbook>';
    }

    /**
     * Styles of the printed form: Arial 12, medium outer border, thin inner
     * rules, bold total row. Indices match the S_* constants above.
     */
    private function getStylesXml(): string
    {
        $thin = 'thin';
        $medium = 'medium';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<fonts count="3">'
            .'<font><sz val="12"/><name val="Arial"/></font>'
            .'<font><b/><sz val="12"/><name val="Arial"/></font>'
            .'<font><sz val="11"/><name val="Calibri"/></font>'
            .'</fonts>'
            .'<fills count="2"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill></fills>'
            .'<borders count="9">'
            .'<border><left/><right/><top/><bottom/><diagonal/></border>'
            .'<border><left style="'.$thin.'"/><right style="'.$thin.'"/><top/><bottom/><diagonal/></border>'
            .'<border><left style="'.$medium.'"/><right style="'.$thin.'"/><top/><bottom/><diagonal/></border>'
            .'<border><left style="'.$thin.'"/><right style="'.$medium.'"/><top/><bottom/><diagonal/></border>'
            .'<border><left style="'.$thin.'"/><right style="'.$thin.'"/><top style="'.$medium.'"/><bottom/><diagonal/></border>'
            .'<border><left style="'.$medium.'"/><right style="'.$thin.'"/><top style="'.$medium.'"/><bottom/><diagonal/></border>'
            .'<border><left style="'.$thin.'"/><right style="'.$medium.'"/><top style="'.$medium.'"/><bottom/><diagonal/></border>'
            .'<border><left style="'.$medium.'"/><right style="'.$thin.'"/><top style="'.$thin.'"/><bottom style="'.$medium.'"/><diagonal/></border>'
            .'<border><left style="'.$thin.'"/><right style="'.$medium.'"/><top style="'.$thin.'"/><bottom style="'.$medium.'"/><diagonal/></border>'
            .'</borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs count="11">'
            .'<xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0"/>'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="4" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="5" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="6" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf>'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="2" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="top"/></xf>'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="3" xfId="0" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf>'
            .'<xf numFmtId="0" fontId="1" fillId="0" borderId="7" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            .'<xf numFmtId="0" fontId="1" fillId="0" borderId="8" xfId="0" applyAlignment="1"><alignment vertical="center" wrapText="1"/></xf>'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0" applyAlignment="1"><alignment horizontal="left" vertical="top" wrapText="1"/></xf>'
            .'</cellXfs>'
            .'</styleSheet>';
    }
}
