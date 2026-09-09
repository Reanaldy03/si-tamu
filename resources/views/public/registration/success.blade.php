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
                Tunjukkan nomor kunjungan ini kepada petugas jika diminta.
            </p>
            <a 
                href="{{ route('public.registration.create') }}" 
                class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-xs font-semibold shadow-xs transition text-center focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
                Selesai / Form Baru
            </a>
        </div>
    </div>
</div>
@endsection
