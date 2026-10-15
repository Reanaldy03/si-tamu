@extends('layouts.admin')

@section('title', 'Audit Log Sistem — SI-TAMU')
@section('header_title', 'Audit Log')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Audit log aktivitas</h1>
            <p class="text-xs sm:text-sm text-slate-600">
                Catatan jejak audit bisnis yang bersifat append-only dan permanen untuk akuntabilitas operasional.
            </p>
        </div>
        <div class="w-fit self-start sm:self-auto flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-200/70 text-slate-700 text-xs font-semibold">
            <svg aria-hidden="true" class="w-4 h-4 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            <span>Immutable &amp; append-only</span>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-xs">
        <form method="GET" action="{{ route('admin.audits.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <!-- Search Query -->
                <div>
                    <label for="q" class="block text-xs font-semibold text-slate-700 mb-1">Pencarian</label>
                    <input 
                        type="text" 
                        name="q" 
                        id="q" 
                        value="{{ request('q') }}" 
                        placeholder="Cari ID target, tipe..." 
                        class="w-full min-h-11 px-3 py-2.5 rounded-lg border border-slate-300 text-xs sm:text-sm placeholder:text-slate-500 focus:outline-hidden focus:ring-2 focus:ring-primary-500"
                    >
                </div>

                <!-- Event Type -->
                <div>
                    <label for="event_type" class="block text-xs font-semibold text-slate-700 mb-1">Jenis event</label>
                    <select 
                        name="event_type" 
                        id="event_type" 
                        class="w-full min-h-11 px-3 py-2.5 rounded-lg border border-slate-300 text-xs sm:text-sm bg-white focus:outline-hidden focus:ring-2 focus:ring-primary-500"
                    >
                        <option value="">Semua event</option>
                        @foreach ($eventTypes as $event)
                            <option value="{{ $event }}" title="{{ $event }}" {{ request('event_type') === $event ? 'selected' : '' }}>
                                {{ $eventLabel($event) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Actor User -->
                <div>
                    <label for="actor_user_id" class="block text-xs font-semibold text-slate-700 mb-1">Aktor (pengguna)</label>
                    <select 
                        name="actor_user_id" 
                        id="actor_user_id" 
                        class="w-full min-h-11 px-3 py-2.5 rounded-lg border border-slate-300 text-xs sm:text-sm bg-white focus:outline-hidden focus:ring-2 focus:ring-primary-500"
                    >
                        <option value="">Semua Aktor</option>
                        @foreach ($users as $u)
                            <option value="{{ $u->id }}" {{ request('actor_user_id') == $u->id ? 'selected' : '' }}>
                                {{ $u->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Date Range -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Rentang tanggal</label>
                    <div class="flex items-center gap-1.5">
                        <input 
                            type="date"
                            name="date_from"
                            aria-label="Tanggal mulai"
                            value="{{ request('date_from') }}"
                            class="w-full min-w-0 flex-1 min-h-11 px-2 py-2.5 rounded-lg border border-slate-300 text-xs focus:outline-hidden focus:ring-2 focus:ring-primary-500"
                        >
                        <span class="text-xs text-slate-500" aria-hidden="true">-</span>
                        <input 
                            type="date"
                            name="date_to"
                            aria-label="Tanggal akhir"
                            value="{{ request('date_to') }}"
                            class="w-full min-w-0 flex-1 min-h-11 px-2 py-2.5 rounded-lg border border-slate-300 text-xs focus:outline-hidden focus:ring-2 focus:ring-primary-500"
                        >
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                @if(request()->hasAny(['q', 'event_type', 'actor_user_id', 'date_from', 'date_to']))
                    <a href="{{ route('admin.audits.index') }}" class="inline-flex items-center min-h-11 px-3.5 py-2.5 rounded-lg border border-slate-200 hover:bg-slate-50 text-xs font-medium text-slate-600 transition focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-500">
                        Reset filter
                    </a>
                @endif
                <button type="submit" class="inline-flex items-center min-h-11 px-4 py-2.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold transition cursor-pointer focus:outline-hidden focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:ring-offset-2">
                    Filter audit
                </button>
            </div>
        </form>
    </div>

    <!-- Audits Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        @if ($audits->isEmpty())
            <div class="p-12 text-center space-y-2">
                <p class="text-sm font-semibold text-slate-700">Belum ada riwayat audit yang cocok.</p>
                <p class="text-xs text-slate-500">Log audit dicatat secara otomatis pada setiap mutasi data.</p>
            </div>
        @else
            <div class="overflow-x-auto" tabindex="0" role="region" aria-label="Tabel audit log">
                <table class="w-full text-left text-sm divide-y divide-slate-200">
                    <caption class="sr-only">Audit log aktivitas sistem, terbaru lebih dahulu</caption>
                    <thead class="bg-slate-50 text-xs font-semibold text-slate-600">
                        <tr>
                            <th scope="col" class="px-5 py-3">Waktu</th>
                            <th scope="col" class="px-5 py-3">Jenis event</th>
                            <th scope="col" class="px-5 py-3">Aktor</th>
                            <th scope="col" class="hidden md:table-cell px-5 py-3">Target entitas</th>
                            <th scope="col" class="hidden md:table-cell px-5 py-3">IP hash</th>
                            <th scope="col" class="px-5 py-3 text-right">Detail</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($audits as $audit)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-5 py-3.5 whitespace-nowrap text-xs text-slate-600 font-medium">
                                    {{ $audit->occurred_at ? $audit->occurred_at->timezone($tz)->format('d M Y, H:i:s') : '—' }}
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <div class="text-xs font-semibold text-slate-900" title="{{ $audit->event_type }}">
                                        {{ $eventLabel($audit->event_type) }}
                                    </div>
                                    <div class="text-[11px] font-mono text-slate-500">{{ $audit->event_type }}</div>
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <div class="text-xs font-semibold text-slate-900">
                                        {{ $audit->actorUser?->name ?? ($audit->actor_type === 'PUBLIC' ? 'Tamu Publik' : 'Sistem') }}
                                    </div>
                                    <div class="text-[11px] text-slate-500">
                                        {{ $audit->actor_type }}
                                    </div>
                                </td>
                                <td class="hidden md:table-cell px-5 py-3.5 whitespace-nowrap">
                                    <div class="text-xs font-medium text-slate-800">
                                        {{ $targetLabel($audit->target_type) }}
                                    </div>
                                    @if ($audit->target_public_id)
                                        <div class="text-[11px] text-slate-500 font-mono">{{ $audit->target_public_id }}</div>
                                    @endif
                                </td>
                                <td class="hidden md:table-cell px-5 py-3.5 whitespace-nowrap text-[11px] font-mono text-slate-500">
                                    {{ $audit->ip_hash ? substr($audit->ip_hash, 0, 12) . '…' : '—' }}
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap text-right text-xs">
                                    <a
                                        href="{{ route('admin.audits.show', $audit) }}"
                                        class="inline-flex items-center gap-1 min-h-9 px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-primary-50 text-slate-700 hover:text-primary-600 font-medium transition focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-500"
                                    >
                                        <span>Lihat</span>
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

            <div class="p-4 border-t border-slate-200">
                {{ $audits->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
