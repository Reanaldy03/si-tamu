@extends('layouts.admin')

@section('title', 'Dashboard Operasional — SI-TAMU')
@section('header_title', 'Dashboard Operasional')

@section('content')
<div class="space-y-6">
    <!-- Top Greeting & Context -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Ringkasan Hari Ini</h1>
            <p class="text-xs sm:text-sm text-slate-500">
                Data aktivitas buku tamu digital periode {{ now($tz)->format('d F Y') }} ({{ $tz }}).
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a 
                href="{{ route('admin.dashboard') }}" 
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-medium shadow-xs transition"
                title="Muat ulang data"
            >
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span>Muat Ulang</span>
            </a>
            <a 
                href="{{ route('admin.visits.index') }}" 
                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-xs transition"
            >
                <span>Kelola Semua Kunjungan</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        </div>
    </div>

    <!-- 4 Operational Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Total Hari Ini -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Total Hari Ini</p>
                <h3 class="text-3xl font-bold text-slate-900 mt-1">{{ number_format($totalToday) }}</h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Seluruh kunjungan masuk</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
        </div>

        <!-- 2. Terdaftar / Menunggu -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-amber-600 uppercase tracking-wider">Menunggu / Tiba</p>
                <h3 class="text-3xl font-bold text-amber-600 mt-1">{{ number_format($waitingCount) }}</h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Perlu tindak lanjut petugas</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <!-- 3. Sedang Dilayani -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-sky-600 uppercase tracking-wider">Sedang Dilayani</p>
                <h3 class="text-3xl font-bold text-sky-600 mt-1">{{ number_format($inServiceCount) }}</h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Sedang dalam proses unit</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
        </div>

        <!-- 4. Selesai -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-emerald-600 uppercase tracking-wider">Selesai Hari Ini</p>
                <h3 class="text-3xl font-bold text-emerald-600 mt-1">{{ number_format($completedCount) }}</h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Kunjungan tuntas</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Latest Visits Today Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900">Kunjungan Terkini Hari Ini</h2>
                <p class="text-xs text-slate-500">Maksimal 10 kunjungan terbaru yang tercatat hari ini.</p>
            </div>
            @if ($latestVisits->isNotEmpty())
                <a href="{{ route('admin.visits.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition">
                    Lihat Riwayat Lengkap &rarr;
                </a>
            @endif
        </div>

        @if ($latestVisits->isEmpty())
            <!-- Empty State Per Design.md 12.5 -->
            <div class="p-12 text-center space-y-3">
                <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                </div>
                <div class="space-y-1">
                    <h4 class="text-sm font-semibold text-slate-900">Belum ada kunjungan hari ini.</h4>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto">
                        Kunjungan baru akan muncul di sini setelah tamu selesai mendaftar melalui formulir tamu digital.
                    </p>
                </div>
            </div>
        @else
            <!-- Desktop/Tablet Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm divide-y divide-slate-200">
                    <thead class="bg-slate-50 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                        <tr>
                            <th scope="col" class="px-5 py-3">Waktu</th>
                            <th scope="col" class="px-5 py-3">Nama Tamu</th>
                            <th scope="col" class="px-5 py-3">Instansi</th>
                            <th scope="col" class="px-5 py-3">Unit Tujuan</th>
                            <th scope="col" class="px-5 py-3">Status</th>
                            <th scope="col" class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($latestVisits as $visit)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-5 py-3.5 whitespace-nowrap text-xs text-slate-600 font-medium">
                                    {{ $visit->registered_at->timezone($tz)->format('H:i') }} WIB
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <div class="font-semibold text-slate-900 text-sm">{{ $visit->visitor_name }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono">{{ $visit->public_id }}</div>
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap text-xs text-slate-600">
                                    {{ $visit->origin_organization }}
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap text-xs text-slate-600">
                                    {{ $visit->department_name_snapshot }}
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold border {{ $visit->current_status->badgeClasses() }}">
                                        {{ $visit->current_status->label() }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap text-right text-xs">
                                    <a 
                                        href="{{ route('admin.visits.show', $visit) }}" 
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-slate-100 hover:bg-indigo-50 text-slate-700 hover:text-indigo-600 font-medium transition"
                                    >
                                        <span>Detail</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
