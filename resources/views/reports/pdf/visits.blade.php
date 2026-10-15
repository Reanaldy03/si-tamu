<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Rekapitulasi Kunjungan Tamu</title>
    <style>
        /* F4 (215 x 330 mm) with the margins of the institution's printed forms. */
        @page { size: 609.6pt 935.5pt; margin: 17.75pt 27.9pt 36pt 49pt; }
        @if ($fonts['kop'])
            @font-face { font-family: 'KopFont'; src: url("{{ $fonts['kop'] }}"); }
        @endif
        @if ($fonts['serif'])
            @font-face { font-family: 'LaporanSerif'; src: url("{{ $fonts['serif'] }}"); }
        @endif
        @if ($fonts['serifBold'])
            @font-face { font-family: 'LaporanSerif'; font-weight: bold; src: url("{{ $fonts['serifBold'] }}"); }
        @endif

        body { color: #000; font-family: 'LaporanSerif', 'Times New Roman', Times, serif; font-size: 9pt; }
        table { border-collapse: collapse; }

        /* Kop surat: dicetak pada koordinat tetap mengikuti kop resmi instansi
           (F4), sama seperti formulir dinas, jadi tidak ikut alur teks. */
        .kop-line, .kop-logo { position: absolute; }
        .kop-line { left: 0; width: 100%; padding-left: 7.55pt; text-align: center; text-transform: uppercase; font-family: 'KopFont', Helvetica, Arial, sans-serif; }
        .kop-logo { left: -1.5pt; top: 0; width: 60.25pt; height: 79pt; }
        .kop-1 { top: 0; font-size: 24pt; }
        .kop-2 { top: 25.25pt; font-size: 26pt; }
        .kop-3 { top: 54.5pt; font-size: 17pt; }
        .kop-4 { top: 72.75pt; font-size: 17pt; }
        .kop-space { height: 123pt; }

        h1 { font-size: 12pt; text-align: center; text-transform: uppercase; text-decoration: underline; margin: 14pt 0 10pt; }

        .form { width: 100%; font-size: 9pt; border: 1pt solid #000; margin-bottom: 9pt; }
        .form td { border: 0.5pt solid #000; padding: 2pt 5pt; vertical-align: top; }
        .form .no { width: 4%; text-align: center; }
        .form .lbl { width: 28%; }
        .form .sub { display: block; }

        .data { width: 100%; table-layout: fixed; font-size: 8pt; }
        .data th, .data td { border: 0.5pt solid #000; padding: 2pt 3pt; vertical-align: top; overflow-wrap: break-word; }
        .data th { text-align: center; font-weight: bold; }
        .data tr { page-break-inside: avoid; }
        .center { text-align: center; }

        /* Blok tanda tangan mengikuti contoh surat dinas: satu kolom di kanan. */
        .sign { margin: 24pt 0 0 264pt; width: 268.7pt; font-size: 12pt; page-break-inside: avoid; }
        .sign td { vertical-align: top; line-height: 15.5pt; }
        .sign-lbl { width: 77pt; }
        .sign-sep { width: 14pt; }
        .sign-date { text-align: right; padding-right: 46pt; }
        .sign-ruang { height: 40pt; }
        .sign-nama { margin: 0; }
    </style>
</head>
<body>
    <div class="kop">
        @if ($logoDataUri)
            <img class="kop-logo" src="{{ $logoDataUri }}" alt="Logo instansi">
        @endif
        <div class="kop-line kop-1">Pemerintah Kota Binjai</div>
        <div class="kop-line kop-2">{{ $organization?->organization_name ?? 'Sekretariat Dewan Perwakilan Rakyat Daerah' }}</div>
        <div class="kop-line kop-3">{{ $kopStreet }} Telepon / Fax {{ $organization?->contact_phone }}</div>
        <div class="kop-line kop-4">{{ $kopCity }}</div>
    </div>
    <div class="kop-space"></div>

    <h1>Laporan Rekapitulasi Kunjungan Tamu</h1>

    @php
        $period = $filter->periodLabel();
        $format = fn ($number) => number_format($number, 0, ',', '.');
    @endphp

    <table class="form">
        <tr>
            <td class="no">1.</td>
            <td class="lbl">Periode Laporan</td>
            <td>{{ $period }}</td>
        </tr>
        <tr>
            <td class="no">2.</td>
            <td class="lbl">Unit Kerja</td>
            <td>{{ $department?->name ?? 'Semua Unit Kerja' }}</td>
        </tr>
        <tr>
            <td class="no">3.</td>
            <td class="lbl">Status Kunjungan</td>
            <td>{{ $filter->status ? \App\Domain\Visits\Enums\VisitStatus::from($filter->status)->label() : 'Semua Status' }}</td>
        </tr>
        <tr>
            <td class="no">4.</td>
            <td class="lbl">Kata Kunci Pencarian</td>
            <td>{{ $filter->query ?: '-' }}</td>
        </tr>
        <tr>
            <td class="no">5.</td>
            <td class="lbl">Rekapitulasi Kunjungan</td>
            <td>
                <span class="sub">a. Total: {{ $format($aggregates['total_visits']) }} kunjungan</span>
                <span class="sub">b. Selesai: {{ $format($aggregates['completed_count']) }} kunjungan</span>
                <span class="sub">c. Berjalan: {{ $format($aggregates['active_count']) }} kunjungan</span>
                <span class="sub">d. Dibatalkan / ditolak: {{ $format($aggregates['cancelled_rejected_count']) }} kunjungan</span>
            </td>
        </tr>
        <tr>
            <td class="no">6.</td>
            <td class="lbl">Keterangan</td>
            <td>Dicetak pada {{ $generatedAt->translatedFormat('j F Y, H:i') }} {{ $generatedAt->format('T') }} | Nomor Laporan: SITAMU/{{ $generatedAt->format('Ymd-His') }}</td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th style="width: 5%;">No.</th>
                <th style="width: 10.5%;">Tanggal</th>
                <th style="width: 13.5%;">Nama Tamu</th>
                <th style="width: 12%;">Instansi Asal</th>
                <th style="width: 10.5%;">Telepon</th>
                <th style="width: 13.5%;">Unit Kerja</th>
                <th style="width: 17%;">Keperluan</th>
                <th style="width: 10%;">Status</th>
                <th style="width: 8%;">Sumber</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($records as $index => $visit)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td class="center">{{ $visit->visit_date?->format('d/m/Y') ?? '-' }}</td>
                    <td>{{ $visit->visitor_name }}</td>
                    <td>{{ $visit->origin_organization }}</td>
                    <td>{{ $visit->contact_phone ?: '-' }}</td>
                    <td>{{ $visit->department?->name ?? $visit->department_name_snapshot }}</td>
                    <td>{{ $visit->purpose }}</td>
                    <td>{{ $visit->current_status->label() }}</td>
                    <td>@php
                        $source = $visit->registration_source;
                        echo $source === \App\Domain\Visits\Enums\RegistrationSource::STAFF ? 'Petugas' : 'Publik';
                    @endphp</td>
                </tr>
            @empty
                <tr><td colspan="9" class="center">Tidak ada data kunjungan untuk kriteria laporan ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    <table class="sign">
        <tr>
            <td class="sign-lbl">Dikeluarkan di</td>
            <td class="sign-sep">:</td>
            <td>B I N J A I</td>
        </tr>
        <tr>
            <td class="sign-lbl">Pada Tanggal</td>
            <td class="sign-sep">:</td>
            <td class="sign-date">{{ $generatedAt->translatedFormat('j F Y') }}</td>
        </tr>
        <tr>
            <td colspan="3">SEKRETARIS DPRD KOTA BINJAI</td>
        </tr>
    </table>
    <div class="sign" style="margin-top: 0;">
        <div class="sign-ruang">&nbsp;</div>
        <p class="sign-nama">...............................................</p>
        <p class="sign-nama">NIP. ...............................................</p>
    </div>
</body>
</html>
