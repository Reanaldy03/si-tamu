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
                Aktivitas buku tamu digital Sekretariat DPRD Kota Binjai periode {{ now($tz)->format('d F Y') }} ({{ $tz }}).
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a 
                href="{{ route('admin.checkin.scan') }}" 
                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs transition"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                </svg>
                <span>Pindai QR Check-In</span>
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

    <!-- 5 Operational Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- 1. Total Hari Ini -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Total Hari Ini</p>
                <h3 class="text-3xl font-bold text-slate-900 mt-1">{{ number_format($totalToday) }}</h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Seluruh kunjungan masuk</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <!-- 3. Sedang Dilayani -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-sky-600 uppercase tracking-wider">Sedang Dilayani</p>
                <h3 class="text-3xl font-bold text-sky-600 mt-1">{{ number_format($inServiceCount) }}</h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Dalam proses unit/bagian</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <!-- 5. Skor IKM Kepuasan -->
        <a href="{{ route('admin.feedback.index') }}" class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs flex items-center justify-between hover:border-amber-300 transition group">
            <div>
                <p class="text-xs font-medium text-amber-600 uppercase tracking-wider">Indeks IKM</p>
                <h3 class="text-3xl font-bold text-slate-900 mt-1 flex items-baseline gap-1">
                    {{ $ikmAvg > 0 ? number_format($ikmAvg, 1) : '-' }}
                    <span class="text-xs font-normal text-slate-400">/ 5.0</span>
                </h3>
                <p class="text-[11px] text-slate-400 mt-0.5">{{ number_format($ikmCount) }} responden</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-500 group-hover:scale-105 transition flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
            </div>
        </a>
    </div>

    <!-- Interactive Charts Section -->
    <div 
        x-data="{
            initCharts() {
                if (!window.Chart) return;

                // 1. Trend Line Chart
                const ctxTrend = document.getElementById('trendChart')?.getContext('2d');
                if (ctxTrend) {
                    new Chart(ctxTrend, {
                        type: 'line',
                        data: {
                            labels: @json($trendLabels),
                            datasets: [{
                                label: 'Jumlah Kunjungan',
                                data: @json($trendValues),
                                borderColor: '#4f46e5',
                                backgroundColor: 'rgba(79, 70, 229, 0.08)',
                                fill: true,
                                tension: 0.35,
                                pointBackgroundColor: '#4f46e5',
                                pointRadius: 4,
                                pointHoverRadius: 6,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: (context) => ` ${context.parsed.y} Tamu`
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: { precision: 0, stepSize: 1 }
                                }
                            }
                        }
                    });
                }

                // 2. Organization Category Donut Chart
                const ctxOrg = document.getElementById('orgChart')?.getContext('2d');
                if (ctxOrg) {
                    new Chart(ctxOrg, {
                        type: 'doughnut',
                        data: {
                            labels: @json($orgLabels),
                            datasets: [{
                                data: @json($orgValues),
                                backgroundColor: [
                                    '#3b82f6', // Pemerintah
                                    '#f59e0b', // LSM/Ormas
                                    '#10b981', // Akademisi
                                    '#8b5cf6', // Umum/Swasta
                                ],
                                borderWidth: 2,
                                borderColor: '#ffffff',
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: { boxWidth: 12, font: { size: 11 } }
                                }
                            },
                            cutout: '65%',
                        }
                    });
                }

                // 3. Department Distribution Horizontal Bar Chart
                const ctxDept = document.getElementById('deptChart')?.getContext('2d');
                if (ctxDept) {
                    new Chart(ctxDept, {
                        type: 'bar',
                        data: {
                            labels: @json($deptLabels),
                            datasets: [{
                                label: 'Tamu 30 Hari Terakhir',
                                data: @json($deptValues),
                                backgroundColor: '#6366f1',
                                borderRadius: 6,
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                            },
                            scales: {
                                x: {
                                    beginAtZero: true,
                                    ticks: { precision: 0 }
                                }
                            }
                        }
                    });
                }
            }
        }"
        x-init="$nextTick(() => initCharts())"
        class="space-y-6"
    >
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Chart 1: Trend 7 Hari (7 cols) -->
            <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200 shadow-xs p-5 sm:p-6 space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Tren Kunjungan Harian</h3>
                        <p class="text-xs text-slate-500">Jumlah pengunjung dalam 7 hari terakhir</p>
                    </div>
                    <span class="px-2.5 py-1 bg-indigo-50 text-indigo-700 text-xs font-semibold rounded-lg">
                        7 Hari Terakhir
                    </span>
                </div>
                <div class="h-64 relative">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>

            <!-- Chart 2: Proporsi Kategori Instansi (5 cols) -->
            <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200 shadow-xs p-5 sm:p-6 space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Proporsi Asal Tamu</h3>
                        <p class="text-xs text-slate-500">Segmentasi instansi dalam 30 hari</p>
                    </div>
                    <span class="px-2.5 py-1 bg-slate-100 text-slate-700 text-xs font-semibold rounded-lg">
                        30 Hari
                    </span>
                </div>
                <div class="h-64 relative flex items-center justify-center">
                    <canvas id="orgChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Chart 3: Distribusi Kunjungan per Bagian / Komisi DPRD -->
        @if(count($deptLabels) > 0)
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 sm:p-6 space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Distribusi Kunjungan per Bagian / Komisi DPRD</h3>
                    <p class="text-xs text-slate-500">Tingkat frekuensi audiensi dan penerimaan tamu per unit kerja (30 Hari Terakhir)</p>
                </div>
                <span class="px-2.5 py-1 bg-slate-100 text-slate-700 text-xs font-semibold rounded-lg">
                    Top Unit Kerja
                </span>
            </div>
            <div class="h-56 relative">
                <canvas id="deptChart"></canvas>
            </div>
        </div>
        @endif
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
            <div class="p-12 text-center space-y-3">
                <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                </div>
                <div class="space-y-1">
                    <h4 class="text-sm font-semibold text-slate-900">Belum ada kunjungan hari ini.</h4>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto">
                        Kunjungan baru akan muncul di sini setelah tamu mendaftar di meja lobi Gedung DPRD Kota Binjai.
                    </p>
                </div>
            </div>
        @else
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
