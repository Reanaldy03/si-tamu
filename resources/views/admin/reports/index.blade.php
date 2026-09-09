@extends('layouts.admin')

@section('title', 'Laporan & Rekapitulasi Kunjungan — SI-TAMU')
@section('header_title', 'Laporan Kunjungan')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Laporan & Rekapitulasi Kunjungan</h1>
            <p class="text-xs sm:text-sm text-slate-500">
                Filter data kunjungan multi-kriteria, ringkasan metrik agregat, dan pembuatan berkas ekspor tabular.
            </p>
        </div>

        @can('export', \App\Models\ReportExport::class)
        <div class="flex items-center gap-2">
            <!-- Form Export CSV -->
            <form method="POST" action="{{ route('admin.reports.export') }}">
                @csrf
                <input type="hidden" name="format" value="CSV">
                <input type="hidden" name="q" value="{{ request('q') }}">
                <input type="hidden" name="department_id" value="{{ request('department_id') }}">
                <input type="hidden" name="current_status" value="{{ request('current_status') }}">
                <input type="hidden" name="date_from" value="{{ request('date_from') }}">
                <input type="hidden" name="date_to" value="{{ request('date_to') }}">
                <button 
                    type="submit" 
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-xs transition cursor-pointer"
                >
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Ekspor CSV</span>
                </button>
            </form>

            <!-- Form Export XLSX -->
            <form method="POST" action="{{ route('admin.reports.export') }}">
                @csrf
                <input type="hidden" name="format" value="XLSX">
                <input type="hidden" name="q" value="{{ request('q') }}">
                <input type="hidden" name="department_id" value="{{ request('department_id') }}">
                <input type="hidden" name="current_status" value="{{ request('current_status') }}">
                <input type="hidden" name="date_from" value="{{ request('date_from') }}">
                <input type="hidden" name="date_to" value="{{ request('date_to') }}">
                <button 
                    type="submit" 
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-xs transition cursor-pointer"
                >
                    <svg class="w-4 h-4 text-indigo-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Ekspor Excel (.xlsx)</span>
                </button>
            </form>
        </div>
        @endcan
    </div>

    <!-- Aggregate Metric Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Kunjungan -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-xs">
            <span class="text-xs font-medium text-slate-500 block">Total Kunjungan</span>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">{{ number_format($aggregates['total_visits']) }}</span>
                <span class="text-xs text-slate-400">data</span>
            </div>
        </div>

        <!-- Selesai -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-xs">
            <span class="text-xs font-medium text-slate-500 block">Kunjungan Selesai</span>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-bold tracking-tight text-emerald-600">{{ number_format($aggregates['completed_count']) }}</span>
                <span class="text-xs text-emerald-600/70 font-medium">selesai</span>
            </div>
        </div>

        <!-- Sedang Aktif / Menunggu -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-xs">
            <span class="text-xs font-medium text-slate-500 block">Sedang Aktif / Berjalan</span>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-bold tracking-tight text-indigo-600">{{ number_format($aggregates['active_count']) }}</span>
                <span class="text-xs text-indigo-600/70 font-medium">proses</span>
            </div>
        </div>

        <!-- Batal / Ditolak -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-xs">
            <span class="text-xs font-medium text-slate-500 block">Batal / Ditolak</span>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-bold tracking-tight text-rose-600">{{ number_format($aggregates['cancelled_rejected_count']) }}</span>
                <span class="text-xs text-rose-600/70 font-medium">tidak selesai</span>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-xs">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <!-- Keyword Search -->
                <div>
                    <label for="q" class="block text-xs font-semibold text-slate-700 mb-1">Pencarian</label>
                    <input 
                        type="text" 
                        name="q" 
                        id="q" 
                        value="{{ request('q') }}" 
                        placeholder="Nama tamu, instansi, ID..." 
                        class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                </div>

                <!-- Department -->
                <div>
                    <label for="department_id" class="block text-xs font-semibold text-slate-700 mb-1">Unit Kerja Tujuan</label>
                    <select 
                        name="department_id" 
                        id="department_id" 
                        class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <option value="">Semua Unit Kerja</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status -->
                <div>
                    <label for="current_status" class="block text-xs font-semibold text-slate-700 mb-1">Status Kunjungan</label>
                    <select 
                        name="current_status" 
                        id="current_status" 
                        class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <option value="">Semua Status</option>
                        @foreach ($statuses as $st)
                            <option value="{{ $st->value }}" {{ request('current_status') === $st->value ? 'selected' : '' }}>
                                {{ $st->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Date From -->
                <div>
                    <label for="date_from" class="block text-xs font-semibold text-slate-700 mb-1">Dari Tanggal</label>
                    <input 
                        type="date" 
                        name="date_from" 
                        id="date_from" 
                        value="{{ request('date_from') }}" 
                        class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                </div>

                <!-- Date To -->
                <div>
                    <label for="date_to" class="block text-xs font-semibold text-slate-700 mb-1">Sampai Tanggal</label>
                    <input 
                        type="date" 
                        name="date_to" 
                        id="date_to" 
                        value="{{ request('date_to') }}" 
                        class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                @if(request()->hasAny(['q', 'department_id', 'current_status', 'date_from', 'date_to']))
                    <a href="{{ route('admin.reports.index') }}" class="px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 text-xs font-medium text-slate-600 transition">
                        Reset Filter
                    </a>
                @endif
                <button type="submit" class="px-4 py-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold transition cursor-pointer">
                    Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Preview Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900">Preview Data Kunjungan</h2>
            <span class="text-xs text-slate-400">Menampilkan {{ $visits->count() }} dari {{ $visits->total() }} data</span>
        </div>

        @if ($visits->isEmpty())
            <div class="p-12 text-center space-y-2">
                <p class="text-sm font-semibold text-slate-700">Tidak ada data kunjungan yang cocok.</p>
                <p class="text-xs text-slate-500">Coba ubah parameter pencarian atau sesuaikan rentang tanggal.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm divide-y divide-slate-200">
                    <thead class="bg-slate-50 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                        <tr>
                            <th scope="col" class="px-5 py-3 font-mono">No. Kunjungan</th>
                            <th scope="col" class="px-5 py-3">Waktu</th>
                            <th scope="col" class="px-5 py-3">Nama Tamu</th>
                            <th scope="col" class="px-5 py-3">Instansi Asal</th>
                            <th scope="col" class="px-5 py-3">Unit Tujuan</th>
                            <th scope="col" class="px-5 py-3">Keperluan</th>
                            <th scope="col" class="px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($visits as $visit)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-5 py-3.5 whitespace-nowrap text-xs font-mono font-semibold text-indigo-600">
                                    <a href="{{ route('admin.visits.show', $visit) }}" class="hover:underline">
                                        {{ $visit->public_id }}
                                    </a>
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap text-xs text-slate-600 font-medium">
                                    {{ $visit->registered_at->timezone($tz)->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <div class="text-xs font-semibold text-slate-900">{{ $visit->visitor_name }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $visit->contact_phone }}</div>
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap text-xs text-slate-700">
                                    {{ $visit->origin_organization }}
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap text-xs text-slate-700">
                                    {{ $visit->department?->name ?? $visit->department_name_snapshot }}
                                </td>
                                <td class="px-5 py-3.5 max-w-xs truncate text-xs text-slate-600" title="{{ $visit->purpose }}">
                                    {{ $visit->purpose }}
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold border {{ $visit->current_status->badgeClasses() }}">
                                        {{ $visit->current_status->label() }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-200">
                {{ $visits->links() }}
            </div>
        @endif
    </div>

    <!-- Recent Exports Table -->
    @if ($recentExports->isNotEmpty())
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Riwayat Berkas Ekspor Anda</h2>
                    <p class="text-xs text-slate-400">Berkas ekspor disimpan secara temporer (24 jam) untuk privasi dan efisiensi penyimpanan.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm divide-y divide-slate-200">
                    <thead class="bg-slate-50 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                        <tr>
                            <th scope="col" class="px-5 py-3">Nama Berkas</th>
                            <th scope="col" class="px-5 py-3">Format</th>
                            <th scope="col" class="px-5 py-3">Jumlah Baris</th>
                            <th scope="col" class="px-5 py-3">Waktu Dibuat</th>
                            <th scope="col" class="px-5 py-3">Kedaluwarsa</th>
                            <th scope="col" class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($recentExports as $exp)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-5 py-3 whitespace-nowrap text-xs font-mono font-medium text-slate-800">
                                    {{ $exp->file_name }}
                                </td>
                                <td class="px-5 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold {{ $exp->format === 'CSV' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-indigo-50 text-indigo-700 border border-indigo-200' }}">
                                        {{ $exp->format }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 whitespace-nowrap text-xs text-slate-600">
                                    {{ $exp->row_count !== null ? number_format($exp->row_count) . ' baris' : '—' }}
                                </td>
                                <td class="px-5 py-3 whitespace-nowrap text-xs text-slate-500">
                                    {{ $exp->created_at->timezone($tz)->format('d M Y, H:i') }}
                                </td>
                                <td class="px-5 py-3 whitespace-nowrap text-xs text-slate-500">
                                    @if ($exp->expires_at && $exp->expires_at->isPast())
                                        <span class="text-rose-600 font-semibold">Telah Kedaluwarsa</span>
                                    @elseif ($exp->expires_at)
                                        {{ $exp->expires_at->timezone($tz)->format('d M Y, H:i') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-5 py-3 whitespace-nowrap text-right text-xs">
                                    @if ($exp->status->value === 'COMPLETED' && (! $exp->expires_at || $exp->expires_at->isFuture()))
                                        <a 
                                            href="{{ route('admin.reports.download', $exp) }}" 
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold transition"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                            </svg>
                                            <span>Unduh ({{ $exp->download_count }})</span>
                                        </a>
                                    @else
                                        <span class="text-slate-400 text-xs italic">Tidak tersedia</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
