@extends('layouts.public')

@section('title', 'Registrasi Berhasil — ' . ($organization?->organization_name ?? 'SI-TAMU'))

@section('content')
<div class="space-y-6">
    <!-- Success Banner -->
    <div class="text-center space-y-3">
        <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto shadow-xs">
            <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
            </svg>
        </div>

        <div class="space-y-1">
            <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">
                Registrasi Berhasil
            </h2>
            <p class="text-sm text-slate-600 max-w-md mx-auto">
                Data kunjungan Anda telah tercatat. Silakan melanjutkan ke meja resepsionis atau petugas front-office.
            </p>
        </div>
    </div>

    <!-- Confirmation Ticket Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <!-- Ticket Header: Public Reference Number -->
        <div class="bg-slate-900 text-white p-6 sm:p-7 text-center space-y-2">
            <span class="text-xs font-semibold tracking-wider text-slate-400 uppercase">
                Nomor Kunjungan
            </span>
            <div class="font-mono text-xl sm:text-2xl font-bold tracking-wider text-indigo-300 select-all">
                {{ $visit->public_id }}
            </div>

            <!-- Alpine.js Copy to Clipboard Button -->
            <div x-data="{ copied: false }" class="pt-1">
                <button 
                    type="button" 
                    @click="navigator.clipboard.writeText('{{ $visit->public_id }}'); copied = true; setTimeout(() => copied = false, 2500)"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-medium transition cursor-pointer"
                >
                    <template x-if="!copied">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            <span>Salin Nomor</span>
                        </span>
                    </template>
                    <template x-if="copied">
                        <span class="flex items-center gap-1.5 text-emerald-400 font-semibold">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Nomor Tersalin!</span>
                        </span>
                    </template>
                </button>
            </div>
        </div>

        <!-- QR Code Section -->
        @if(isset($qrSvg))
        <div class="p-6 bg-slate-50 border-b border-slate-200 text-center flex flex-col items-center justify-center space-y-3">
            <div class="p-3 bg-white rounded-2xl shadow-sm border border-slate-200 inline-block">
                <div class="w-48 h-48 flex items-center justify-center [&>svg]:w-full [&>svg]:h-full">
                    {!! $qrSvg !!}
                </div>
            </div>
            <div class="space-y-1">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-700">
                    QR Code Check-In Tamu
                </p>
                <p class="text-xs text-slate-500 max-w-xs">
                    Tunjukkan QR Code ini ke kamera pemindai di meja resepsionis lobi Gedung DPRD Kota Binjai.
                </p>
            </div>
            <div class="pt-1">
                <a 
                    href="{{ route('public.registration.ticket', ['publicId' => $visit->public_id]) }}" 
                    target="_blank"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-xs transition"
                >
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Cetak Bukti Kunjungan / Unduh Tiket</span>
                </a>
            </div>
        </div>
        @endif

        <!-- Completed Feedback Banner -->
        @if($visit->current_status === \App\Domain\Visits\Enums\VisitStatus::COMPLETED)
        <div class="p-4 bg-amber-50 border-b border-amber-200 flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5 text-xs text-amber-800">
                <svg class="w-5 h-5 text-amber-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
                <span>Kunjungan Anda telah selesai. Berikan penilaian layanan kami untuk evaluasi Sekretariat DPRD.</span>
            </div>
            <a 
                href="{{ route('public.feedback.create', ['publicId' => $visit->public_id]) }}" 
                class="shrink-0 px-3 py-1.5 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold shadow-xs transition"
            >
                Isi Survei Kepuasan
            </a>
        </div>
        @endif

        <!-- Ticket Body: Visit Details -->
        <div class="p-6 sm:p-7 space-y-4 text-sm divide-y divide-slate-100">
            <div class="flex justify-between items-center pb-3">
                <span class="text-slate-500 font-medium">Status</span>
                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-600 mr-1.5 animate-pulse"></span>
                    {{ $visit->current_status->label() }}
                </span>
            </div>

            <div class="flex justify-between items-start pt-3">
                <span class="text-slate-500 font-medium">Nama Tamu</span>
                <span class="text-slate-900 font-semibold text-right">{{ $visit->visitor_name }}</span>
            </div>

            <div class="flex justify-between items-start pt-3">
                <span class="text-slate-500 font-medium">Asal Instansi</span>
                <span class="text-slate-900 font-medium text-right">{{ $visit->origin_organization }}</span>
            </div>

            <div class="flex justify-between items-start pt-3">
                <span class="text-slate-500 font-medium">Unit / Bidang Tujuan</span>
                <span class="text-slate-900 font-medium text-right">{{ $visit->department_name_snapshot }}</span>
            </div>

            <div class="flex justify-between items-start pt-3">
                <span class="text-slate-500 font-medium">Keperluan</span>
                <span class="text-slate-900 text-right max-w-xs">{{ $visit->purpose }}</span>
            </div>

            <div class="flex justify-between items-center pt-3">
                <span class="text-slate-500 font-medium">Jumlah Rombongan</span>
                <span class="text-slate-900 font-medium">{{ $visit->party_size }} Orang</span>
            </div>

            <div class="flex justify-between items-center pt-3">
                <span class="text-slate-500 font-medium">Waktu Registrasi</span>
                <span class="text-slate-900 font-medium text-right">
                    @php
                        $tz = $organization?->timezone ?? 'Asia/Jakarta';
                    @endphp
                    {{ $visit->registered_at->timezone($tz)->format('d M Y, H:i') }}
                    <span class="text-xs text-slate-500">({{ $tz }})</span>
                </span>
            </div>
        </div>

        <!-- Ticket Footer / Action Buttons -->
        <div class="p-6 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row gap-3 items-center justify-between">
            <p class="text-xs text-slate-500 text-center sm:text-left">
                Tunjukkan QR code atau nomor kunjungan ini kepada petugas resepsionis di lobi.
            </p>
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <a 
                    href="{{ route('public.registration.ticket', ['publicId' => $visit->public_id]) }}" 
                    target="_blank"
                    class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-white border border-slate-300 hover:bg-slate-100 text-slate-700 text-xs font-semibold shadow-xs transition text-center"
                >
                    Cetak Tiket
                </a>
                <a 
                    href="{{ route('public.registration.create') }}" 
                    class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-xs font-semibold shadow-xs transition text-center focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    Form Baru
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
