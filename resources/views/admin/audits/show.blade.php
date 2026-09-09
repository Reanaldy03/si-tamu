@extends('layouts.admin')

@section('title', 'Detail Audit Log #' . $audit->id . ' — SI-TAMU')
@section('header_title', 'Detail Audit Log')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('admin.audits.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition">
                &larr; Kembali ke Daftar Audit
            </a>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 mt-1">Detail Audit #{{ $audit->id }}</h1>
        </div>
        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-mono font-bold bg-slate-900 text-white">
            {{ $audit->event_type }}
        </span>
    </div>

    <!-- Metadata Card -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400">Informasi Eksekusi</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div>
                <span class="text-xs text-slate-400 block">Waktu Kejadian</span>
                <span class="text-slate-900 font-semibold">
                    {{ $audit->occurred_at ? $audit->occurred_at->timezone($tz)->format('d M Y, H:i:s') : '—' }} ({{ $tz }})
                </span>
            </div>

            <div>
                <span class="text-xs text-slate-400 block">Aktor Pengguna</span>
                <span class="text-slate-900 font-semibold">
                    {{ $audit->actorUser?->name ?? ($audit->actor_type === 'PUBLIC' ? 'Tamu Publik' : 'Sistem') }}
                    <span class="text-xs text-slate-400 font-normal">({{ $audit->actor_type }})</span>
                </span>
            </div>

            <div>
                <span class="text-xs text-slate-400 block">Target Entitas</span>
                <span class="text-slate-900 font-medium capitalize">
                    {{ $audit->target_type ?: '—' }} (ID: {{ $audit->target_id ?: '—' }})
                </span>
                @if ($audit->target_public_id)
                    <div class="text-xs font-mono text-indigo-600">{{ $audit->target_public_id }}</div>
                @endif
            </div>

            <div>
                <span class="text-xs text-slate-400 block">Request Correlation ID</span>
                <span class="text-xs font-mono text-slate-700 select-all">
                    {{ $audit->request_id ?: '—' }}
                </span>
            </div>

            <div class="sm:col-span-2">
                <span class="text-xs text-slate-400 block">IP Address Hash (SHA-256)</span>
                <span class="text-xs font-mono text-slate-600 select-all">
                    {{ $audit->ip_hash ?: '—' }}
                </span>
            </div>
        </div>
    </div>

    <!-- State Diff Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <!-- Before State -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-2">
            <h3 class="text-xs font-bold uppercase tracking-wider text-amber-600">Sebelum Mutasi (Before)</h3>
            <pre class="bg-slate-900 text-slate-200 p-3.5 rounded-xl text-xs font-mono overflow-x-auto max-h-64 leading-relaxed">{{ json_encode($audit->before_state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
        </div>

        <!-- After State -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-2">
            <h3 class="text-xs font-bold uppercase tracking-wider text-emerald-600">Sesudah Mutasi (After)</h3>
            <pre class="bg-slate-900 text-slate-200 p-3.5 rounded-xl text-xs font-mono overflow-x-auto max-h-64 leading-relaxed">{{ json_encode($audit->after_state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
        </div>
    </div>

    <!-- Metadata Additional -->
    @if ($audit->metadata)
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-2">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Metadata Tambahan</h3>
            <pre class="bg-slate-900 text-slate-200 p-3.5 rounded-xl text-xs font-mono overflow-x-auto max-h-48 leading-relaxed">{{ json_encode($audit->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
        </div>
    @endif
</div>
@endsection
