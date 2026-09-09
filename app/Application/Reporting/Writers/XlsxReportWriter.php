<?php

namespace App\Application\Reporting\Writers;

use App\Application\Reporting\Services\FormulaSanitizer;
use App\Models\Visit;
use Illuminate\Support\LazyCollection;
use XMLWriter;
use ZipArchive;

class XlsxReportWriter
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
     * Column letters from A to J.
     *
     * @var list<string>
     */
    private const COLUMNS = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];

    /**
     * Write visit records to an XLSX file at destination path.
     *
     * @param  LazyCollection<int, Visit>  $records
     */
    public function write(string $destinationPath, LazyCollection $records, string $timezone = 'Asia/Jakarta'): int
    {
        $tempSheetXml = tempnam(sys_get_temp_dir(), 'sitamu_sheet_');
        $xml = new XMLWriter;
        $xml->openURI($tempSheetXml);
        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElement('worksheet');
        $xml->writeAttribute('xmlns', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $xml->startElement('sheetData');

        // Write header row at r="1"
        $xml->startElement('row');
        $xml->writeAttribute('r', '1');
        foreach (self::HEADERS as $colIndex => $headerText) {
            $colLetter = self::COLUMNS[$colIndex];
            $xml->startElement('c');
            $xml->writeAttribute('r', "{$colLetter}1");
            $xml->writeAttribute('t', 'inlineStr');
            $xml->startElement('is');
            $xml->writeElement('t', $headerText);
            $xml->endElement(); // is
            $xml->endElement(); // c
        }
        $xml->endElement(); // row

        $rowIndex = 2;
        $rowCount = 0;

        foreach ($records as $visit) {
            $values = [
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

            $xml->startElement('row');
            $xml->writeAttribute('r', (string) $rowIndex);

            foreach ($values as $colIndex => $val) {
                $colLetter = self::COLUMNS[$colIndex];
                $cellRef = "{$colLetter}{$rowIndex}";

                $xml->startElement('c');
                $xml->writeAttribute('r', $cellRef);
                $xml->writeAttribute('t', 'inlineStr');
                $xml->startElement('is');
                $xml->writeElement('t', $val);
                $xml->endElement(); // is
                $xml->endElement(); // c
            }

            $xml->endElement(); // row
            $rowIndex++;
            $rowCount++;
        }

        $xml->endElement(); // sheetData
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

    private function getStylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<fonts count="1"><font><sz val="11"/><name val="Calibri"/></font></fonts>'
            .'<fills count="2"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill></fills>'
            .'<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/></cellXfs>'
            .'</styleSheet>';
    }
}
