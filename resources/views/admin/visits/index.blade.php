@extends('layouts.admin')

@section('title', 'Data Kunjungan — SI-TAMU')
@section('header_title', 'Data Kunjungan')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Data Kunjungan</h1>
            <p class="text-xs sm:text-sm text-slate-500">
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
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            name="q" 
                            id="q" 
                            value="{{ request('q') }}" 
                            placeholder="Cari nama tamu, instansi, atau ID..." 
                            class="w-full pl-9 pr-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        >
                    </div>
                </div>

                <!-- 2. Unit Tujuan -->
                <div>
                    <label for="department_id" class="block text-xs font-semibold text-slate-700 mb-1">Unit / Bidang</label>
                    <select 
                        name="department_id" 
                        id="department_id" 
                        class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
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
                        class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
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
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-500">Tanggal:</span>
                    <input 
                        type="date" 
                        name="date_from" 
                        value="{{ request('date_from') }}" 
                        class="px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                    <span class="text-xs text-slate-400">s/d</span>
                    <input 
                        type="date" 
                        name="date_to" 
                        value="{{ request('date_to') }}" 
                        class="px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                </div>

                <div class="flex items-center gap-2">
                    @if (request()->hasAny(['q', 'department_id', 'current_status', 'date_from', 'date_to']))
                        <a 
                            href="{{ route('admin.visits.index') }}" 
                            class="px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 text-xs font-medium text-slate-600 transition"
                        >
                            Reset Filter
                        </a>
                    @endif
                    <button 
                        type="submit" 
                        class="px-4 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-xs transition"
                    >
                        Terapkan Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Visits Data Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        @if ($visits->isEmpty())
            <!-- No Result State Per Design.md 13.7 -->
            <div class="p-12 text-center space-y-3">
                <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="space-y-1">
                    <h4 class="text-sm font-semibold text-slate-900">Tidak ada kunjungan yang cocok.</h4>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto">
                        Ubah kata pencarian atau reset beberapa kriteria filter untuk menemukan data kunjungan.
                    </p>
                </div>
                @if (request()->hasAny(['q', 'department_id', 'current_status', 'date_from', 'date_to']))
                    <div class="pt-2">
                        <a 
                            href="{{ route('admin.visits.index') }}" 
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition"
                        >
                            <span>Reset Filter</span>
                        </a>
                    </div>
                @endif
            </div>
        @else
            <!-- Desktop Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm divide-y divide-slate-200">
                    <thead class="bg-slate-50 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                        <tr>
                            <th scope="col" class="px-5 py-3">Waktu & ID</th>
                            <th scope="col" class="px-5 py-3">Tamu & Instansi</th>
                            <th scope="col" class="px-5 py-3">Unit Tujuan</th>
                            <th scope="col" class="px-5 py-3">Keperluan</th>
                            <th scope="col" class="px-5 py-3">Status</th>
                            <th scope="col" class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($visits as $visit)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <div class="text-xs font-semibold text-slate-900">
                                        {{ $visit->registered_at->timezone($tz)->format('d M Y, H:i') }}
                                    </div>
                                    <div class="text-[11px] text-slate-400 font-mono">
                                        {{ $visit->public_id }}
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <div class="font-semibold text-slate-900 text-sm">{{ $visit->visitor_name }}</div>
                                    <div class="text-xs text-slate-500">{{ $visit->origin_organization }}</div>
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap text-xs text-slate-700 font-medium">
                                    {{ $visit->department_name_snapshot }}
                                </td>
                                <td class="px-5 py-3.5 text-xs text-slate-600 max-w-xs">
                                    <p class="truncate" title="{{ $visit->purpose }}">{{ $visit->purpose }}</p>
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold border {{ $visit->current_status->badgeClasses() }}">
                                        {{ $visit->current_status->label() }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap text-right text-xs">
                                    <a 
                                        href="{{ route('admin.visits.show', $visit) }}" 
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-semibold transition"
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

            <!-- Table Pagination Bar -->
            <div class="p-4 border-t border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-slate-600">
                <div>
                    Menampilkan <span class="font-semibold">{{ $visits->firstItem() ?? 0 }}</span> - <span class="font-semibold">{{ $visits->lastItem() ?? 0 }}</span> dari <span class="font-semibold">{{ $visits->total() }}</span> kunjungan
                </div>
                <div>
                    {{ $visits->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
