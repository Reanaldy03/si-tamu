<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Kunjungan Tamu — {{ $visit->public_id }}</title>
    @vite(['resources/css/app.css'])
    <style>
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
                max-width: 100% !important;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen py-8 px-4 antialiased text-slate-800">
    <!-- Non-Print Controls Toolbar -->
    <div class="max-w-2xl mx-auto mb-4 flex items-center justify-between no-print">
        <a 
            href="{{ route('public.registration.success', ['publicId' => $visit->public_id]) }}" 
            class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 transition"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali</span>
        </a>
        <button 
            type="button" 
            onclick="window.print()" 
            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold shadow-xs transition cursor-pointer"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            <span>Cetak / Simpan PDF</span>
        </button>
    </div>

    <!-- Official Pass Ticket Container -->
    <div class="print-container max-w-2xl mx-auto bg-white rounded-2xl border border-slate-300 shadow-md p-6 sm:p-8 space-y-6">
        <!-- Official Kop Surat Header -->
        <div class="border-b-2 border-slate-900 pb-4 text-center space-y-1">
            <h1 class="text-base sm:text-lg font-extrabold uppercase tracking-wide text-slate-950">
                SEKRETARIAT DEWAN PERWAKILAN RAKYAT DAERAH
            </h1>
            <h2 class="text-sm sm:text-base font-bold uppercase tracking-wider text-slate-900">
                KOTA BINJAI
            </h2>
            <p class="text-xs text-slate-600">
                Jl. Veteran No. 1, Kel. Tangsi, Kec. Binjai Kota, Kota Binjai, Sumatera Utara 20714
            </p>
            <div class="pt-2">
                <span class="inline-block px-3 py-1 bg-slate-100 rounded-md text-xs font-bold uppercase tracking-wider text-slate-800 border border-slate-300">
                    KARTU BUKTI KUNJUNGAN TAMU (VISITOR PASS)
                </span>
            </div>
        </div>

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
                <div>
                    <span class="text-slate-500 font-medium block">Bagian / Komisi Tujuan:</span>
                    <span class="font-bold text-slate-900">{{ $visit->department_name_snapshot }}</span>
                </div>
                <div>
                    <span class="text-slate-500 font-medium block">Keperluan Kunjungan:</span>
                    <span class="text-slate-800">{{ $visit->purpose }}</span>
                </div>
                <div class="flex items-center gap-6 pt-1">
                    <div>
                        <span class="text-slate-500 font-medium block">Jumlah Tamu:</span>
                        <span class="font-semibold text-slate-800">{{ $visit->party_size }} Orang</span>
                    </div>
                    <div>
                        <span class="text-slate-500 font-medium block">Waktu Registrasi:</span>
                        <span class="font-semibold text-slate-800">
                            {{ $visit->registered_at->timezone($organization?->timezone ?? 'Asia/Jakarta')->format('d/m/Y, H:i') }} WIB
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
    </div>
</body>
</html>
