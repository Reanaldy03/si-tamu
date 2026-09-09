@extends('layouts.admin')

@section('title', 'Audit Log Sistem — SI-TAMU')
@section('header_title', 'Audit Log')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Audit Log Aktivitas</h1>
            <p class="text-xs sm:text-sm text-slate-500">
                Catatan jejak audit bisnis yang bersifat append-only dan permanen untuk akuntabilitas operasional.
            </p>
        </div>
        <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-200/70 text-slate-700 text-xs font-semibold">
            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            <span>Immutable & Append-Only</span>
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
                        class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                </div>

                <!-- Event Type -->
                <div>
                    <label for="event_type" class="block text-xs font-semibold text-slate-700 mb-1">Jenis Event</label>
                    <select 
                        name="event_type" 
                        id="event_type" 
                        class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <option value="">Semua Event</option>
                        @foreach ($eventTypes as $event)
                            <option value="{{ $event }}" {{ request('event_type') === $event ? 'selected' : '' }}>
                                {{ $event }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Actor User -->
                <div>
                    <label for="actor_user_id" class="block text-xs font-semibold text-slate-700 mb-1">Aktor (Pengguna)</label>
                    <select 
                        name="actor_user_id" 
                        id="actor_user_id" 
                        class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
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
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Rentang Tanggal</label>
                    <div class="flex items-center gap-1.5">
                        <input 
                            type="date" 
                            name="date_from" 
                            value="{{ request('date_from') }}" 
                            class="w-1/2 px-2 py-1.5 rounded-lg border border-slate-300 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                        <span class="text-xs text-slate-400">-</span>
                        <input 
                            type="date" 
                            name="date_to" 
                            value="{{ request('date_to') }}" 
                            class="w-1/2 px-2 py-1.5 rounded-lg border border-slate-300 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                @if(request()->hasAny(['q', 'event_type', 'actor_user_id', 'date_from', 'date_to']))
                    <a href="{{ route('admin.audits.index') }}" class="px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 text-xs font-medium text-slate-600 transition">
                        Reset Filter
                    </a>
                @endif
                <button type="submit" class="px-4 py-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold transition">
                    Filter Audit
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
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm divide-y divide-slate-200">
                    <thead class="bg-slate-50 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                        <tr>
                            <th scope="col" class="px-5 py-3">Waktu</th>
                            <th scope="col" class="px-5 py-3">Jenis Event</th>
                            <th scope="col" class="px-5 py-3">Aktor</th>
                            <th scope="col" class="px-5 py-3">Target Entitas</th>
                            <th scope="col" class="px-5 py-3 font-mono">IP Hash</th>
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
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-semibold bg-slate-100 text-slate-800 border border-slate-200">
                                        {{ $audit->event_type }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <div class="text-xs font-semibold text-slate-900">
                                        {{ $audit->actorUser?->name ?? ($audit->actor_type === 'PUBLIC' ? 'Tamu Publik' : 'Sistem') }}
                                    </div>
                                    <div class="text-[10px] text-slate-400 uppercase tracking-wider">
                                        {{ $audit->actor_type }}
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <div class="text-xs font-medium text-slate-800 capitalize">
                                        {{ $audit->target_type ?: '—' }}
                                    </div>
                                    @if ($audit->target_public_id)
                                        <div class="text-[11px] text-indigo-600 font-mono">
                                             {{ $audit->target_public_id }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap text-[11px] font-mono text-slate-400">
                                    {{ $audit->ip_hash ? substr($audit->ip_hash, 0, 12) . '…' : '—' }}
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap text-right text-xs">
                                    <a 
                                        href="{{ route('admin.audits.show', $audit) }}" 
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-slate-100 hover:bg-indigo-50 text-slate-700 hover:text-indigo-600 font-medium transition"
                                    >
                                        <span>Lihat</span>
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

            <div class="p-4 border-t border-slate-200">
                {{ $audits->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
