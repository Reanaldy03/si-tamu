@extends('layouts.admin')

@section('title', 'Data Kunjungan — SI-TAMU')
@section('header_title', 'Data Kunjungan')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Data Kunjungan</h1>
            <p class="text-xs sm:text-sm text-slate-600">
                Lihat, cari, dan kelola riwayat buku tamu digital organisasi.
            </p>
        </div>
    </div>

    <!-- Filter & Search Card -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-xs">
        <form method="GET" action="{{ route('admin.visits.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <!-- 1. Search Query -->
                <div class="lg:col-span-2">
                    <label for="q" class="block text-xs font-semibold text-slate-700 mb-1">Pencarian</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input
                            type="text"
                            name="q"
                            id="q"
                            value="{{ request('q') }}"
                            placeholder="Cari nama tamu, instansi, atau ID..."
                            class="w-full min-h-11 pl-9 pr-3 py-2.5 rounded-lg border border-slate-300 text-xs sm:text-sm placeholder:text-slate-500 focus:outline-hidden focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                        >
                    </div>
                </div>

                <!-- 2. Unit Tujuan -->
                <div>
                    <label for="department_id" class="block text-xs font-semibold text-slate-700 mb-1">Unit / Bidang</label>
                    <select 
                        name="department_id" 
                        id="department_id" 
                        class="w-full min-h-11 px-3 py-2.5 rounded-lg border border-slate-300 text-xs sm:text-sm bg-white focus:outline-hidden focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                    >
                        <option value="">Semua Unit</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- 3. Status Kunjungan -->
                <div>
                    <label for="current_status" class="block text-xs font-semibold text-slate-700 mb-1">Status</label>
                    <select 
                        name="current_status" 
                        id="current_status" 
                        class="w-full min-h-11 px-3 py-2.5 rounded-lg border border-slate-300 text-xs sm:text-sm bg-white focus:outline-hidden focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                    >
                        <option value="">Semua Status</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" {{ request('current_status') === $status->value ? 'selected' : '' }}>
                                {{ $status->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Date Range & Action Buttons -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2 border-t border-slate-100">
                <div class="flex flex-wrap items-center gap-2 min-w-0">
                    <span class="text-xs text-slate-500">Tanggal Kunjungan:</span>
                    <input 
                        type="date" 
                        name="date_from" 
                        aria-label="Dari tanggal kunjungan"
                        value="{{ request('date_from') }}" 
                        class="min-h-11 min-w-0 flex-1 sm:flex-none px-2.5 py-2.5 rounded-lg border border-slate-300 text-xs text-slate-700 focus:outline-hidden focus:ring-2 focus:ring-primary-500"
                    >
                    <span class="text-xs text-slate-600">s/d</span>
                    <input 
                        type="date" 
                        name="date_to" 
                        aria-label="Sampai tanggal kunjungan"
                        value="{{ request('date_to') }}" 
                        class="min-h-11 min-w-0 flex-1 sm:flex-none px-2.5 py-2.5 rounded-lg border border-slate-300 text-xs text-slate-700 focus:outline-hidden focus:ring-2 focus:ring-primary-500"
                    >
                </div>

                <div class="flex items-center gap-2">
                    @if (request()->hasAny(['q', 'department_id', 'current_status', 'date_from', 'date_to']))
                        <a 
                            href="{{ route('admin.visits.index') }}" 
                            class="inline-flex items-center min-h-11 px-3.5 py-2.5 rounded-lg border border-slate-200 hover:bg-slate-50 text-xs font-medium text-slate-600 transition focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-500"
                        >
                            Reset Filter
                        </a>
                    @endif
                    <x-ui.button variant="primary" type="submit">
                        Terapkan Filter
                    </x-ui.button>
                </div>
            </div>
        </form>
    </div>

    <!-- Visits Data Table -->
    @if ($visits->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <!-- No Result State Per Design.md 13.7 -->
            <div class="p-12 text-center space-y-3">
                <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
                    <svg aria-hidden="true" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="space-y-1">
                    <h3 class="text-sm font-semibold text-slate-900">Tidak ada kunjungan yang cocok.</h3>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto">
                        Ubah kata pencarian atau reset beberapa kriteria filter untuk menemukan data kunjungan.
                    </p>
                </div>
                @if (request()->hasAny(['q', 'department_id', 'current_status', 'date_from', 'date_to']))
                    <div class="pt-2">
                        <a 
                            href="{{ route('admin.visits.index') }}" 
                            class="inline-flex items-center gap-1.5 min-h-11 px-3.5 py-2.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-500"
                        >
                            <span>Reset Filter</span>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    @else
        {{-- Mobile: card list (DESIGN.md §34.4). The table below is never rendered on this breakpoint. --}}
        <div class="md:hidden space-y-4">
            <div class="space-y-3">
                @foreach ($visits as $visit)
                    <article class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 space-y-2.5">
                        <div class="flex items-start justify-between gap-3">
                            <h2 class="min-w-0 text-sm font-semibold text-slate-900 break-words">{{ $visit->visitor_name }}</h2>
                            <x-visits.status-badge :status="$visit->current_status" class="shrink-0" />
                        </div>

                        <p class="text-xs text-slate-500 break-words">{{ $visit->origin_organization }}</p>

                        <p class="text-xs text-slate-600 break-words">
                            Tujuan: <span class="font-medium text-slate-900">{{ $visit->department_name_snapshot }}</span>
                        </p>

                        <p class="text-xs text-slate-600">
                            {{ $visit->registered_at->timezone($tz)->format('d M Y') }}
                            <span aria-hidden="true">&bull;</span>
                            <span class="sr-only">pukul</span>
                            {{ $visit->registered_at->timezone($tz)->format('H:i') }}
                        </p>

                        <x-ui.button variant="secondary" block :href="route('admin.visits.show', $visit)">
                            Lihat detail
                        </x-ui.button>
                    </article>
                @endforeach
            </div>

            <!-- Mobile Pagination Bar -->
            @if ($visits->hasPages())
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4">
                    {{ $visits->links() }}
                </div>
            @endif
        </div>

        <!-- Desktop Table (keyboard-scrollable region) -->
        <div class="hidden md:block bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="overflow-x-auto" tabindex="0" role="region" aria-label="Tabel data kunjungan">
                <table class="w-full xl:table-fixed text-left text-sm divide-y divide-slate-200">
                    <caption class="sr-only">Daftar kunjungan tamu beserta tanggal, nomor surat, unit tujuan, status, dan tautan detail</caption>
                    <thead class="bg-slate-50 text-[11px] font-semibold text-slate-500">
                        <tr>
                            <th scope="col" class="hidden md:table-cell md:w-[14%] px-3.5 py-2.5">Tanggal &amp; ID</th>
                            <th scope="col" class="hidden md:table-cell md:w-[11%] px-3.5 py-2.5">Tanggal Kunjungan</th>
                            <th scope="col" class="hidden md:table-cell md:w-[11%] px-3.5 py-2.5">Nomor Surat</th>
                            <th scope="col" class="w-[38%] md:w-[16%] px-3.5 py-2.5">Tamu &amp; Instansi</th>
                            <th scope="col" class="hidden md:table-cell md:w-[13%] px-3.5 py-2.5">Unit Tujuan</th>
                            <th scope="col" class="hidden md:table-cell md:w-[12%] px-3.5 py-2.5">Keperluan</th>
                            <th scope="col" class="w-[30%] md:w-[13%] px-3.5 py-2.5">Status</th>
                            <th scope="col" class="w-[32%] md:w-[11%] px-3.5 py-2.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($visits as $visit)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="hidden md:table-cell px-3.5 py-2.5 whitespace-nowrap">
                                    <div class="text-xs font-semibold text-slate-900">
                                        {{ $visit->registered_at->timezone($tz)->format('d M Y') }}
                                    </div>
                                    <div class="text-[11px] text-slate-500 font-mono truncate" title="{{ $visit->public_id }}">
                                        {{ $visit->public_id }}
                                    </div>
                                </td>
                                <td class="hidden md:table-cell px-3.5 py-2.5 whitespace-nowrap text-xs font-semibold text-slate-900">
                                    {{ $visit->visit_date?->format('d M Y') ?? '—' }}
                                </td>
                                <td class="hidden md:table-cell px-3.5 py-2.5 text-xs text-slate-700 truncate" title="{{ $visit->letter_number }}">
                                    {{ $visit->letter_number ?: '—' }}
                                </td>
                                <td class="px-3.5 py-2.5">
                                    <div class="font-semibold text-slate-900 text-xs sm:text-sm truncate" title="{{ $visit->visitor_name }}">{{ $visit->visitor_name }}</div>
                                    <div class="text-[11px] sm:text-xs text-slate-500 truncate" title="{{ $visit->origin_organization }}">{{ $visit->origin_organization }}</div>
                                </td>
                                <td class="hidden md:table-cell px-3.5 py-2.5 text-xs text-slate-700 font-medium truncate" title="{{ $visit->department_name_snapshot }}">
                                    {{ $visit->department_name_snapshot }}
                                </td>
                                <td class="hidden md:table-cell px-3.5 py-2.5 text-xs text-slate-600">
                                    <p class="truncate" title="{{ $visit->purpose }}">{{ $visit->purpose }}</p>
                                </td>
                                <td class="px-3.5 py-2.5 whitespace-nowrap">
                                    <x-visits.status-badge :status="$visit->current_status" />
                                </td>
                                <td class="px-3.5 py-2.5 whitespace-nowrap text-right text-xs">
                                    <a 
                                        href="{{ route('admin.visits.show', $visit) }}" 
                                        class="inline-flex items-center gap-1 min-h-9 px-2.5 py-1.5 rounded-lg bg-primary-50 hover:bg-primary-100 text-primary-700 font-semibold transition focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-500"
                                    >
                                        <span>Detail</span>
                                        <svg aria-hidden="true" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Table Pagination Bar -->
            <div class="p-4 border-t border-slate-200">
                {{ $visits->links() }}
            </div>
        </div>
    @endif
</div>
@endsection
