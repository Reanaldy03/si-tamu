@extends('layouts.public')

@section('title', 'Registrasi Berhasil — ' . ($organization?->organization_name ?? 'SI-TAMU'))

@section('content')
<div class="space-y-6">
    <!-- Success Banner -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-7 text-center space-y-3">
        <div class="hidden sm:inline-flex items-center justify-center p-2 rounded-2xl bg-white border border-slate-200 shadow-xs">
            <img 
                src="{{ asset('assets/logo.webp') }}" 
                alt="Logo Sekretariat DPRD Kota Binjai" 
                class="h-16 sm:h-20 w-auto object-contain drop-shadow-xs"
            >
        </div>

        <div class="space-y-1">
            <span class="inline-block px-3 py-1 rounded-full text-[11px] font-semibold bg-success-50 text-success-800 border border-success-200">
                Registrasi Selesai &bull; Tiket Diterbitkan
            </span>
            <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">
                Registrasi Berhasil!
            </h2>
            <p class="text-sm text-slate-600 max-w-md mx-auto leading-relaxed">
                Data kunjungan Anda telah tercatat. Silakan tunjukkan QR Code tiket ini kepada petugas resepsionis di lobi Gedung DPRD Kota Binjai.
            </p>
        </div>
    </div>

    <!-- Confirmation Ticket Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <!-- Ticket Header: Public Reference Number -->
        <div class="bg-slate-900 text-white p-4 sm:p-7 text-center space-y-2">
            <span class="text-xs font-semibold text-slate-400">
                Nomor Kunjungan
            </span>
            <div class="font-mono text-base sm:text-2xl font-bold tracking-normal sm:tracking-wider text-primary-300 select-all break-all">
                {{ $visit->public_id }}
            </div>

            <!-- Alpine.js Copy to Clipboard Button -->
            <div x-data="{ copied: false }" class="pt-1">
                <button
                    type="button"
                    @click="navigator.clipboard.writeText(@js($visit->public_id)); copied = true; setTimeout(() => copied = false, 2500)"
                    aria-label="Salin nomor kunjungan"
                    class="inline-flex items-center gap-1.5 min-h-11 px-3.5 py-2.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-medium transition cursor-pointer focus:outline-hidden focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-slate-900"
                >
                    <template x-if="!copied">
                        <span class="flex items-center gap-1.5">
                            <svg aria-hidden="true" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            <span>Salin Nomor</span>
                        </span>
                    </template>
                    <template x-if="copied">
                        <span role="status" class="flex items-center gap-1.5 text-success-300 font-semibold">
                            <svg aria-hidden="true" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                <p class="text-xs font-semibold text-slate-700">
                    QR Code Check-In Tamu
                </p>
                <p class="text-xs text-slate-500 max-w-xs">
                    Tunjukkan QR Code ini ke kamera pemindai di meja resepsionis lobi Gedung DPRD Kota Binjai.
                </p>
            </div>
        </div>
        @endif

        <!-- Completed Feedback Banner -->
        @if($visit->current_status === \App\Domain\Visits\Enums\VisitStatus::COMPLETED && !$visit->feedback)
        <div class="p-4 bg-warning-50 border-b border-warning-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5 text-xs text-warning-900 font-medium">
                <svg aria-hidden="true" class="w-5 h-5 text-warning-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
                <span>Pelayanan Anda telah selesai! Silakan berikan ulasan Anda di sini.</span>
            </div>
            <a 
                href="{{ route('public.feedback.create', ['publicId' => $visit->public_id]) }}"
                class="shrink-0 min-h-11 px-4 py-2.5 rounded-xl bg-warning-700 hover:bg-warning-800 active:bg-warning-900 text-white text-xs font-bold shadow-xs transition flex items-center justify-center gap-1.5 focus:outline-hidden focus-visible:ring-2 focus-visible:ring-warning-700 focus-visible:ring-offset-2"
            >
                <svg aria-hidden="true" class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
                <span>Beri Penilaian Bintang</span>
            </a>
        </div>
        @endif

        <!-- Ticket Body: Visit Details -->
        <div class="p-6 sm:p-7 space-y-4 text-sm divide-y divide-slate-100">
            <div class="flex justify-between items-center pb-3">
                <span class="text-slate-500 font-medium">Status</span>
                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-primary-50 text-primary-700 border border-primary-200">
                    <span aria-hidden="true" class="w-1.5 h-1.5 rounded-full bg-primary-600 mr-1.5"></span>
                    {{ $visit->current_status->label() }}
                </span>
            </div>

            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start pt-3 gap-0.5 sm:gap-4">
                <span class="text-slate-500 font-medium">Nama Tamu</span>
                <span class="text-slate-900 font-semibold text-left sm:text-right">{{ $visit->visitor_name }}</span>
            </div>

            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start pt-3 gap-0.5 sm:gap-4">
                <span class="text-slate-500 font-medium">Asal Instansi</span>
                <span class="text-slate-900 font-medium text-left sm:text-right">{{ $visit->origin_organization }}</span>
            </div>

            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start pt-3 gap-0.5 sm:gap-4">
                <span class="text-slate-500 font-medium">Unit / Bidang Tujuan</span>
                <span class="text-slate-900 font-medium text-left sm:text-right">{{ $visit->department_name_snapshot }}</span>
            </div>

            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start pt-3 gap-0.5 sm:gap-4">
                <span class="text-slate-500 font-medium">Keperluan</span>
                <span class="text-slate-900 text-left sm:text-right max-w-xs">{{ $visit->purpose }}</span>
            </div>

            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start pt-3 gap-0.5 sm:gap-4">
                <span class="text-slate-500 font-medium">Jumlah Rombongan</span>
                <span class="text-slate-900 font-medium text-left sm:text-right">{{ $visit->party_size }} Orang</span>
            </div>

            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start pt-3 gap-0.5 sm:gap-4">
                <span class="text-slate-500 font-medium">Tanggal Kunjungan</span>
                <span class="text-slate-900 font-medium text-left sm:text-right">
                    {{ $visit->visit_date?->format('d M Y') ?? '—' }}
                </span>
            </div>

            @if ($visit->letter_number)
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start pt-3 gap-0.5 sm:gap-4">
                    <span class="text-slate-500 font-medium">Nomor Surat</span>
                    <span class="text-slate-900 font-medium text-left sm:text-right">{{ $visit->letter_number }}</span>
                </div>
            @endif

            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start pt-3 gap-0.5 sm:gap-4">
                <span class="text-slate-500 font-medium">Tanggal Registrasi</span>
                <span class="text-slate-900 font-medium text-left sm:text-right">
                    @php
                        $tz = $organization?->timezone ?? 'Asia/Jakarta';
                    @endphp
                    {{ $visit->registered_at->timezone($tz)->format('d M Y') }}
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
                    rel="noopener noreferrer"
                    class="w-full sm:w-auto min-h-11 inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-white border border-slate-300 hover:bg-slate-100 text-slate-700 text-xs font-semibold shadow-xs transition text-center focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2"
                >
                    Cetak Tiket
                </a>
                <a
                    href="{{ route('public.registration.create') }}"
                    class="w-full sm:w-auto min-h-11 inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white text-xs font-semibold shadow-xs transition text-center focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2"
                >
                    Form Baru
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
