<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Kunjungan Tamu — {{ $visit->public_id }}</title>
    <meta name="robots" content="noindex, nofollow">
    @vite(['resources/css/app.css'])
    <style>
        /*
         * Kop surat. The institution's printed forms set the letterhead in
         * Haettenschweiler; the report PDF uses the same face, so the pass and
         * the report carry an identical letterhead. The font ships with
         * Windows and is embedded here as a data URI — no public asset, no
         * CDN, no extra route.
         */
        @if ($kopFont)
            @font-face {
                font-family: 'KopFont';
                src: url("{{ $kopFont }}") format('truetype');
                font-weight: normal;
                font-style: normal;
                font-display: swap;
            }
        @endif

        .kop-line {
            font-family: 'KopFont', 'Arial Narrow', Impact, sans-serif;
            text-transform: uppercase;
            text-align: center;
            line-height: 1.04;
            color: #000;
            margin: 0;
            /* Narrow previews wrap the institution name; balance the break
               instead of orphaning the last word. */
            text-wrap: balance;
        }

        /* Screen preview: the pass is a card, so the kop is scaled to the card's
           width. Print restores the institution's true letterhead dimensions,
           identical to the report PDF (kop-1 24pt / kop-2 26pt / kop-3-4 17pt). */
        .kop-1 { font-size: 12pt; }
        .kop-2 { font-size: 14pt; }
        .kop-3,
        .kop-4 { font-size: 8pt; }

        @media (min-width: 640px) {
            .kop-1 { font-size: 17pt; }
            .kop-2 { font-size: 24pt; }
            .kop-3,
            .kop-4 { font-size: 12pt; }
        }

        .kop-rule {
            border-top: 0.75pt solid #000;
            border-bottom: 2.5pt solid #000;
            height: 2.65pt;
        }

        @media print {
            body {
                background: white !important;
                color: black !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .print-container {
                border: 2px solid #000 !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                max-width: 100% !important;
                page-break-inside: avoid;
            }

            /* The letterhead's real sizes, matching reports/pdf/visits.blade.php. */
            .kop-1 { font-size: 24pt; }
            .kop-2 { font-size: 26pt; }
            .kop-3,
            .kop-4 { font-size: 17pt; }

            .kop-logo { height: 79pt; width: auto; }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen py-8 px-4 antialiased text-slate-800">
    <a
        href="#main-content"
        class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 focus:px-4 focus:py-2.5 focus:rounded-lg focus:bg-slate-900 focus:text-white focus:text-xs focus:font-semibold focus:shadow-lg"
    >
        Lewati ke konten utama
    </a>

    <!-- Non-Print Controls Toolbar -->
    <nav aria-label="Kontrol halaman bukti kunjungan" class="max-w-2xl mx-auto mb-4 flex items-center justify-between no-print">
        <a
            href="{{ route('public.registration.success', ['publicId' => $visit->public_id]) }}"
            class="inline-flex items-center gap-1.5 min-h-11 px-2 py-2.5 rounded-lg text-xs font-semibold text-slate-700 hover:text-slate-900 transition focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2"
        >
            <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali</span>
        </a>
        <button
            type="button"
            onclick="window.print()"
            class="inline-flex items-center gap-2 min-h-11 px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold shadow-xs transition cursor-pointer focus:outline-hidden focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:ring-offset-2"
        >
            <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            <span>Cetak / Simpan PDF</span>
        </button>
    </nav>

    <!-- Official Pass Ticket Container -->
    <main id="main-content" class="print-container max-w-2xl mx-auto bg-white rounded-2xl border border-slate-300 shadow-md p-6 sm:p-8 space-y-6 print:rounded-none print:shadow-none">
        <!--
            Kop surat. Four lines in Haettenschweiler over the institution's
            double rule (thin above heavy), logo at the left — the same
            letterhead the report PDF prints, fed by the same service so the
            two documents can never disagree about the address.
        -->
        <div class="pb-3">
            <div class="flex items-center gap-3 sm:gap-4">
                <img
                    src="{{ asset('assets/logo.webp') }}"
                    alt=""
                    class="kop-logo h-16 sm:h-20 w-auto object-contain shrink-0"
                >
                <div class="flex-1 min-w-0">
                    <p class="kop-line kop-1">{{ $kopGovernment }}</p>
                    <p class="kop-line kop-2">{{ $organization?->organization_name ?? 'Sekretariat Dewan Perwakilan Rakyat Daerah' }}</p>
                    <p class="kop-line kop-3">
                        {{ $kopStreet }}@if ($organization?->contact_phone) Telepon / Fax {{ $organization->contact_phone }}@endif
                    </p>
                    <p class="kop-line kop-4">{{ $kopCity }}</p>
                </div>
            </div>

            <div class="kop-rule mt-2" aria-hidden="true"></div>
        </div>

        <h1 class="text-center text-sm sm:text-base font-bold uppercase underline text-slate-950">
            Kartu Bukti Kunjungan Tamu
        </h1>

        <!-- Main Body: Data + QR Code Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 items-start">
            <!-- Details (2 Cols) -->
            <div class="sm:col-span-2 space-y-3 text-xs leading-relaxed">
                <div>
                    <span class="text-slate-500 font-medium block">Nomor Kunjungan (ID):</span>
                    <span class="font-mono text-sm font-bold text-slate-900 select-all">{{ $visit->public_id }}</span>
                </div>
                <div>
                    <span class="text-slate-500 font-medium block">Nama Lengkap Tamu:</span>
                    <span class="font-bold text-sm text-slate-950">{{ $visit->visitor_name }}</span>
                </div>
                <div>
                    <span class="text-slate-500 font-medium block">Asal Instansi / Lembaga:</span>
                    <span class="font-medium text-slate-800">{{ $visit->origin_organization }}</span>
                </div>
                @if ($visit->letter_number)
                    <div>
                        <span class="text-slate-500 font-medium block">Nomor Surat:</span>
                        <span class="font-semibold text-slate-800">{{ $visit->letter_number }}</span>
                    </div>
                @endif
                <div>
                    <span class="text-slate-500 font-medium block">Bagian / Komisi Tujuan:</span>
                    <span class="font-bold text-slate-900">{{ $visit->department_name_snapshot }}</span>
                </div>
                <div>
                    <span class="text-slate-500 font-medium block">Keperluan Kunjungan:</span>
                    <span class="text-slate-800">{{ $visit->purpose }}</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-6 pt-1 items-start">
                    <div>
                        <span class="text-slate-500 font-medium block">Jumlah Tamu:</span>
                        <span class="font-semibold text-slate-800">{{ $visit->party_size }} Orang</span>
                    </div>
                    <div>
                        <span class="text-slate-500 font-medium block">Tanggal Kunjungan:</span>
                        <span class="font-semibold text-slate-800">
                            {{ $visit->visit_date?->format('d/m/Y') ?? '—' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-slate-500 font-medium block">Tanggal Registrasi:</span>
                        <span class="font-semibold text-slate-800">
                            {{ $visit->registered_at->timezone($organization?->timezone ?? 'Asia/Jakarta')->format('d/m/Y') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- QR Code (1 Col) -->
            <div class="flex flex-col items-center justify-center p-3 bg-slate-50 border border-slate-200 rounded-xl text-center space-y-2">
                <div class="w-36 h-36 [&>svg]:w-full [&>svg]:h-full bg-white p-1 rounded-lg border border-slate-200">
                    {!! $qrSvg !!}
                </div>
                <span class="text-[10px] font-bold text-slate-600 uppercase tracking-tight">
                    PINDAI SAAT TIBA
                </span>
            </div>
        </div>

        <!-- Official Footer Notice -->
        <div class="border-t border-dashed border-slate-300 pt-4 text-[11px] text-slate-500 space-y-1">
            <p>
                <strong>Catatan Keamanan & Tata Tertib:</strong>
            </p>
            <ul class="list-disc list-inside space-y-0.5 text-slate-600">
                <li>Harap menunjukkan kartu pass ini atau QR Code kepada petugas resepsionis di lobi Gedung DPRD.</li>
                <li>Tamu wajib mengenakan pakaian rapi, sopan, serta mematuhi tata tertib di lingkungan Sekretariat DPRD Kota Binjai.</li>
            </ul>
        </div>
    </main>
</body>
</html>
