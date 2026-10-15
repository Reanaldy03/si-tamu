@extends('layouts.admin')

@section('title', 'Laporan & Rekapitulasi Kunjungan — SI-TAMU')
@section('header_title', 'Laporan Kunjungan')

@section('content')
@php
    // Pagination contract is derived from the paginator, never hardcoded
    // (ReportController::index -> VisitReportQuery::paginate($filter, 20)).
    $previewTotal = (int) $visits->total();
    $previewShown = (int) $visits->count();
    $previewPerPage = (int) $visits->perPage();

    $previewNote = match (true) {
        $previewTotal === 0 => null,
        $previewTotal > $previewShown => 'Menampilkan ' . number_format($previewShown) . ' dari ' . number_format($previewTotal)
            . ' kunjungan (maksimal ' . $previewPerPage . ' baris per halaman). Gunakan navigasi halaman atau ekspor berkas untuk data lengkap.',
        default => 'Menampilkan seluruh ' . number_format($previewTotal) . ' kunjungan yang cocok dengan filter.',
    };

    $hasActiveFilter = request()->hasAny(['q', 'department_id', 'current_status', 'date_from', 'date_to']);
@endphp

<div class="space-y-6">
    <x-layout.page-header
        title="Laporan & Rekapitulasi Kunjungan"
        subtitle="Rekapitulasi kunjungan sesuai filter terpilih, siap dijadikan dokumen resmi."
    >
        <x-slot:actions>
            @can('export', \App\Models\ReportExport::class)
                <form method="POST" action="{{ route('admin.reports.export') }}">
                    @csrf
                    <input type="hidden" name="format" value="PDF">
                    <input type="hidden" name="q" value="{{ request('q') }}">
                    <input type="hidden" name="department_id" value="{{ request('department_id') }}">
                    <input type="hidden" name="current_status" value="{{ request('current_status') }}">
                    <input type="hidden" name="date_from" value="{{ request('date_from') }}">
                    <input type="hidden" name="date_to" value="{{ request('date_to') }}">
                    <x-ui.button type="submit" variant="secondary">
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-4 h-4 text-danger-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </x-slot:icon>
                        Ekspor PDF
                    </x-ui.button>
                </form>

                <form method="POST" action="{{ route('admin.reports.export') }}">
                    @csrf
                    <input type="hidden" name="format" value="XLSX">
                    <input type="hidden" name="q" value="{{ request('q') }}">
                    <input type="hidden" name="department_id" value="{{ request('department_id') }}">
                    <input type="hidden" name="current_status" value="{{ request('current_status') }}">
                    <input type="hidden" name="date_from" value="{{ request('date_from') }}">
                    <input type="hidden" name="date_to" value="{{ request('date_to') }}">
                    <x-ui.button type="submit" variant="secondary">
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </x-slot:icon>
                        Ekspor Excel (.xlsx)
                    </x-ui.button>
                </form>
            @endcan
        </x-slot:actions>
    </x-layout.page-header>

    <!-- Aggregate Metric Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <x-ui.card body-class="p-4 sm:p-5">
            <span class="text-xs font-medium text-slate-500 block">Total kunjungan</span>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">{{ number_format($aggregates['total_visits']) }}</span>
                <span class="text-xs text-slate-500">data</span>
            </div>
        </x-ui.card>

        <x-ui.card body-class="p-4 sm:p-5">
            <span class="text-xs font-medium text-slate-500 block">Kunjungan selesai</span>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-bold tracking-tight text-success-600">{{ number_format($aggregates['completed_count']) }}</span>
                <span class="text-xs text-success-700 font-medium">selesai</span>
            </div>
        </x-ui.card>

        <x-ui.card body-class="p-4 sm:p-5">
            <span class="text-xs font-medium text-slate-500 block">Sedang aktif / berjalan</span>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-bold tracking-tight text-primary-600">{{ number_format($aggregates['active_count']) }}</span>
                <span class="text-xs text-primary-700 font-medium">proses</span>
            </div>
        </x-ui.card>

        <x-ui.card body-class="p-4 sm:p-5">
            <span class="text-xs font-medium text-slate-500 block">Batal / ditolak</span>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-bold tracking-tight text-danger-600">{{ number_format($aggregates['cancelled_rejected_count']) }}</span>
                <span class="text-xs text-danger-700 font-medium">tidak selesai</span>
            </div>
        </x-ui.card>
    </div>

    <!-- Filter Card -->
    <x-ui.card
        title="Filter laporan"
        description="Pilih kriteria untuk mempersempit data kunjungan yang ditampilkan dan diekspor."
        body-class="p-4 sm:p-5"
    >
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
                        class="w-full min-h-11 px-3 py-2.5 rounded-lg border border-slate-300 text-xs sm:text-sm placeholder:text-slate-500 focus:outline-hidden focus:ring-2 focus:ring-primary-500"
                    >
                </div>

                <!-- Department -->
                <div>
                    <label for="department_id" class="block text-xs font-semibold text-slate-700 mb-1">Unit kerja tujuan</label>
                    <select
                        name="department_id"
                        id="department_id"
                        class="w-full min-h-11 px-3 py-2.5 rounded-lg border border-slate-300 text-xs sm:text-sm bg-white focus:outline-hidden focus:ring-2 focus:ring-primary-500"
                    >
                        <option value="">Semua unit kerja</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status -->
                <div>
                    <label for="current_status" class="block text-xs font-semibold text-slate-700 mb-1">Status kunjungan</label>
                    <select
                        name="current_status"
                        id="current_status"
                        class="w-full min-h-11 px-3 py-2.5 rounded-lg border border-slate-300 text-xs sm:text-sm bg-white focus:outline-hidden focus:ring-2 focus:ring-primary-500"
                    >
                        <option value="">Semua status</option>
                        @foreach ($statuses as $st)
                            <option value="{{ $st->value }}" {{ request('current_status') === $st->value ? 'selected' : '' }}>
                                {{ $st->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Date From -->
                <div>
                    <label for="date_from" class="block text-xs font-semibold text-slate-700 mb-1">Dari tanggal kunjungan</label>
                    <input
                        type="date"
                        name="date_from"
                        id="date_from"
                        value="{{ request('date_from') }}"
                        class="w-full min-h-11 px-3 py-2.5 rounded-lg border border-slate-300 text-xs sm:text-sm focus:outline-hidden focus:ring-2 focus:ring-primary-500"
                    >
                </div>

                <!-- Date To -->
                <div>
                    <label for="date_to" class="block text-xs font-semibold text-slate-700 mb-1">Sampai tanggal kunjungan</label>
                    <input
                        type="date"
                        name="date_to"
                        id="date_to"
                        value="{{ request('date_to') }}"
                        class="w-full min-h-11 px-3 py-2.5 rounded-lg border border-slate-300 text-xs sm:text-sm focus:outline-hidden focus:ring-2 focus:ring-primary-500"
                    >
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                @if ($hasActiveFilter)
                    <x-ui.button variant="ghost" :href="route('admin.reports.index')">
                        Reset filter
                    </x-ui.button>
                @endif
                <x-ui.button type="submit" variant="primary">
                    Terapkan filter
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>

    <!-- Preview Table -->
    <x-ui.card title="Preview data kunjungan" :description="$previewNote" flush>
        @if ($visits->isEmpty())
            <div class="p-5">
                <x-ui.empty-state
                    :kind="$hasActiveFilter ? 'filtered' : 'empty'"
                    :title="$hasActiveFilter ? 'Tidak ada kunjungan yang cocok' : 'Belum ada data kunjungan'"
                    :description="$hasActiveFilter
                        ? 'Coba ubah kata kunci, unit kerja, status, atau rentang tanggal kunjungan.'
                        : 'Data kunjungan akan tampil di sini setelah tamu melakukan registrasi.'"
                    :heading-level="3"
                >
                    @if ($hasActiveFilter)
                        <x-slot:action>
                            <x-ui.button variant="secondary" :href="route('admin.reports.index')">
                                Reset filter
                            </x-ui.button>
                        </x-slot:action>
                    @endif
                </x-ui.empty-state>
            </div>
        @else
            <div class="overflow-x-auto" tabindex="0" role="region" aria-label="Tabel pratinjau data kunjungan">
                <table class="w-full xl:table-fixed text-left text-sm divide-y divide-slate-200">
                    <caption class="sr-only">Pratinjau data kunjungan sesuai filter terpilih</caption>
                    <thead class="bg-slate-50 text-xs font-semibold text-slate-600">
                        <tr>
                            <th scope="col" class="hidden md:table-cell md:w-[10%] px-3 py-2.5 font-mono">No. kunjungan</th>
                            <th scope="col" class="hidden md:table-cell md:w-[10%] px-3 py-2.5">Tanggal kunjungan</th>
                            <th scope="col" class="hidden md:table-cell md:w-[10%] px-3 py-2.5">Nomor surat</th>
                            <th scope="col" class="hidden md:table-cell md:w-[10%] px-3 py-2.5">Tanggal registrasi</th>
                            <th scope="col" class="w-[40%] md:w-[14%] px-3 py-2.5">Nama tamu</th>
                            <th scope="col" class="w-[32%] md:w-[12%] px-3 py-2.5">Instansi asal</th>
                            <th scope="col" class="hidden md:table-cell md:w-[11%] px-3 py-2.5">Unit tujuan</th>
                            <th scope="col" class="hidden md:table-cell md:w-[11%] px-3 py-2.5">Keperluan</th>
                            <th scope="col" class="w-[28%] md:w-[13%] px-3 py-2.5">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($visits as $visit)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="hidden md:table-cell px-3 py-2.5 truncate font-mono text-[11px] font-semibold text-primary-600">
                                    <a href="{{ route('admin.visits.show', $visit) }}" class="block truncate rounded py-1.5 -my-1.5 underline-offset-2 hover:underline focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-500" title="{{ $visit->public_id }}">
                                        {{ $visit->public_id }}
                                    </a>
                                </td>
                                <td class="hidden md:table-cell px-3 py-2.5 whitespace-nowrap text-xs font-semibold text-slate-700">
                                    {{ $visit->visit_date?->format('d/m/Y') ?? '—' }}
                                </td>
                                <td class="hidden md:table-cell px-3 py-2.5 text-xs text-slate-700 truncate" title="{{ $visit->letter_number }}">
                                    {{ $visit->letter_number ?: '—' }}
                                </td>
                                <td class="hidden md:table-cell px-3 py-2.5 whitespace-nowrap text-xs text-slate-600 font-medium">
                                    {{ $visit->registered_at->timezone($tz)->format('d/m/Y') }}
                                </td>
                                {{-- DESIGN.md §48.2 — nomor kontak tamu tidak pernah tampil di tabel. --}}
                                <td class="px-3 py-2.5">
                                    <a href="{{ route('admin.visits.show', $visit) }}" class="text-xs font-semibold text-slate-900 hover:text-primary-600 truncate block py-1 -my-1" title="{{ $visit->visitor_name }}">
                                        {{ $visit->visitor_name }}
                                    </a>
                                </td>
                                <td class="px-3 py-2.5 text-xs text-slate-700 truncate" title="{{ $visit->origin_organization }}">
                                    {{ $visit->origin_organization }}
                                </td>
                                <td class="hidden md:table-cell px-3 py-2.5 text-xs text-slate-700 truncate" title="{{ $visit->department?->name ?? $visit->department_name_snapshot }}">
                                    {{ $visit->department?->name ?? $visit->department_name_snapshot }}
                                </td>
                                <td class="hidden md:table-cell px-3 py-2.5 truncate text-xs text-slate-600" title="{{ $visit->purpose }}">
                                    {{ $visit->purpose }}
                                </td>
                                <td class="px-3 py-2.5 whitespace-nowrap">
                                    <x-visits.status-badge :status="$visit->current_status" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $visits->links() }}
            </div>
        @endif
    </x-ui.card>

    <!-- Recent Exports Table -->
    <x-ui.card
        title="Riwayat berkas ekspor Anda"
        description="Berkas ekspor disimpan sementara selama 24 jam, lalu dihapus otomatis demi privasi data tamu. Tautan unduh hanya aktif selama berkas tersimpan — ajukan ekspor baru dengan filter yang diinginkan untuk mendapat berkas terbaru."
        flush
    >
        @if ($recentExports->isEmpty())
            <div class="p-5">
                <x-ui.empty-state
                    kind="empty"
                    title="Belum ada berkas ekspor"
                    description="Gunakan tombol Ekspor PDF atau Ekspor Excel di atas untuk membuat berkas laporan pertama Anda."
                    :heading-level="3"
                />
            </div>
        @else
            <div class="overflow-x-auto" tabindex="0" role="region" aria-label="Tabel riwayat berkas ekspor">
                <table class="w-full text-left text-sm divide-y divide-slate-200">
                    <caption class="sr-only">Riwayat berkas ekspor yang Anda buat</caption>
                    <thead class="bg-slate-50 text-xs font-semibold text-slate-600">
                        <tr>
                            <th scope="col" class="px-3.5 py-2.5">Nama berkas</th>
                            <th scope="col" class="px-3.5 py-2.5">Format</th>
                            <th scope="col" class="hidden sm:table-cell px-3.5 py-2.5">Jumlah baris</th>
                            <th scope="col" class="hidden sm:table-cell px-3.5 py-2.5">Waktu dibuat</th>
                            <th scope="col" class="hidden sm:table-cell px-3.5 py-2.5">Kedaluwarsa</th>
                            <th scope="col" class="px-3.5 py-2.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($recentExports as $exp)
                            @php
                                $statusValue = $exp->status->value;
                                $isCompleted = $statusValue === 'COMPLETED';
                                $isExpired = $exp->expires_at !== null && $exp->expires_at->isPast();
                                $isAvailable = $isCompleted
                                    && ! $isExpired
                                    && auth()->user()->can('download', $exp);
                            @endphp
                            <tr @class(['transition', 'bg-slate-50/60' => ! $isAvailable])>
                                <td class="px-3.5 py-2.5 text-xs font-mono font-medium {{ $isAvailable ? 'text-slate-800' : 'text-slate-600' }} max-w-[140px] sm:max-w-none truncate" title="{{ $exp->file_name }}">
                                    {{ $exp->file_name }}
                                </td>
                                <td class="px-3.5 py-2.5 whitespace-nowrap">
                                    <x-ui.badge tone="neutral">{{ $exp->format }}</x-ui.badge>
                                </td>
                                <td class="hidden sm:table-cell px-3.5 py-2.5 whitespace-nowrap text-xs text-slate-600">
                                    {{ $exp->row_count !== null ? number_format($exp->row_count) . ' baris' : '—' }}
                                </td>
                                <td class="hidden sm:table-cell px-3.5 py-2.5 whitespace-nowrap text-xs text-slate-500">
                                    {{ $exp->created_at->timezone($tz)->format('d M Y, H:i') }}
                                </td>
                                <td class="hidden sm:table-cell px-3.5 py-2.5 whitespace-nowrap text-xs">
                                    @if ($isExpired)
                                        <x-ui.badge tone="warning">Kedaluwarsa</x-ui.badge>
                                    @elseif ($exp->expires_at)
                                        <span class="text-slate-500">{{ $exp->expires_at->timezone($tz)->format('d M Y, H:i') }}</span>
                                    @else
                                        <span class="text-slate-500">—</span>
                                    @endif
                                </td>
                                <td class="px-3.5 py-2.5 whitespace-nowrap text-right text-xs">
                                    @if ($isAvailable)
                                        <x-ui.button
                                            :href="route('admin.reports.download', $exp)"
                                            variant="secondary"
                                            size="md"
                                        >
                                            <x-slot:icon>
                                                <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                </svg>
                                            </x-slot:icon>
                                            Unduh ({{ $exp->download_count }})
                                        </x-ui.button>
                                    @elseif ($isExpired)
                                        <span class="text-slate-500">Ajukan ekspor baru</span>
                                    @elseif ($statusValue === 'FAILED')
                                        <span class="text-slate-500">Gagal diproses</span>
                                    @elseif ($isCompleted)
                                        <span class="text-slate-500">Tidak tersedia</span>
                                    @else
                                        <span class="text-slate-500">Sedang diproses</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-ui.card>
</div>
@endsection
