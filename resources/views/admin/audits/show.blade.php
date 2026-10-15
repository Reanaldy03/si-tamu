@extends('layouts.admin')

@section('title', 'Detail Audit Log #' . $audit->id . ' — SI-TAMU')
@section('header_title', 'Detail Audit Log')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <a href="{{ route('admin.audits.index') }}" class="inline-flex items-center min-h-9 px-2 -ml-2 rounded-lg text-xs font-semibold text-primary-600 hover:text-primary-800 transition focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-500">
                &larr; Kembali ke daftar audit
            </a>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 mt-1">Detail audit #{{ $audit->id }}</h1>
        </div>
        <span class="self-start sm:self-auto inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-900 text-white" title="{{ $audit->event_type }}">
            {{ $eventLabel($audit->event_type) }}
        </span>
    </div>

    <!-- Metadata Card -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
        <h2 class="text-xs font-bold text-slate-600">Informasi eksekusi</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div>
                <span class="text-xs text-slate-500 block">Waktu kejadian</span>
                <span class="text-slate-900 font-semibold">
                    {{ $audit->occurred_at ? $audit->occurred_at->timezone($tz)->format('d M Y, H:i:s') : '—' }} ({{ $tz }})
                </span>
            </div>

            <div>
                <span class="text-xs text-slate-500 block">Aktor pengguna</span>
                <span class="text-slate-900 font-semibold">
                    {{ $audit->actorUser?->name ?? ($audit->actor_type === 'PUBLIC' ? 'Tamu Publik' : 'Sistem') }}
                    <span class="text-xs text-slate-500 font-normal">({{ $audit->actor_type }})</span>
                </span>
            </div>

            <div>
                <span class="text-xs text-slate-500 block">Target entitas</span>
                @php
                    $targetUrl = match ($audit->target_type) {
                        'visit' => $audit->target_public_id ? route('admin.visits.show', $audit->target_public_id) : null,
                        'user' => $audit->target_public_id ? route('admin.users.edit', $audit->target_public_id) : null,
                        'department' => $audit->target_public_id ? route('admin.departments.edit', $audit->target_public_id) : null,
                        default => null,
                    };
                @endphp
                @if ($targetUrl)
                    <a
                        href="{{ $targetUrl }}"
                        class="text-slate-900 font-semibold rounded hover:text-primary-700 underline-offset-2 hover:underline focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-500"
                    >
                        {{ $targetLabel($audit->target_type) }}
                    </a>
                @else
                    <span class="text-slate-900 font-semibold">{{ $targetLabel($audit->target_type) }}</span>
                @endif
                @if ($audit->target_public_id)
                    <div class="text-xs font-mono text-slate-500 break-all">{{ $audit->target_public_id }}</div>
                @endif
            </div>

            <div>
                <span class="text-xs text-slate-500 block">ID korelasi permintaan</span>
                <span class="text-xs font-mono text-slate-700 select-all break-all">
                    {{ $audit->request_id ?: '—' }}
                </span>
            </div>

            <div class="sm:col-span-2">
                <span class="text-xs text-slate-500 block">Hash alamat IP (SHA-256)</span>
                <span class="text-xs font-mono text-slate-600 select-all break-all">
                    {{ $audit->ip_hash ?: '—' }}
                </span>
            </div>
        </div>
    </div>

    <!-- State Diff Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <!-- Before State -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-2 flex flex-col">
            <h3 class="text-xs font-bold text-warning-700">Sebelum mutasi</h3>
            @if ($audit->before_state)
                <pre tabindex="0" role="region" class="bg-slate-900 text-slate-200 p-3.5 rounded-xl text-xs font-mono overflow-x-auto whitespace-pre-wrap break-all focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-400 max-h-64 leading-relaxed flex-1">{{ json_encode($audit->before_state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            @else
                <div class="flex items-center justify-center p-6 rounded-xl bg-slate-50 border border-dashed border-slate-200 text-xs text-slate-500 italic flex-1 text-center">
                    Tidak ada data sebelum mutasi (entitas baru dibuat).
                </div>
            @endif
        </div>

        <!-- After State -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-2 flex flex-col">
            <h3 class="text-xs font-bold text-success-700">Sesudah mutasi</h3>
            @if ($audit->after_state)
                <pre tabindex="0" role="region" class="bg-slate-900 text-slate-200 p-3.5 rounded-xl text-xs font-mono overflow-x-auto whitespace-pre-wrap break-all focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-400 max-h-64 leading-relaxed flex-1">{{ json_encode($audit->after_state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            @else
                <div class="flex items-center justify-center p-6 rounded-xl bg-slate-50 border border-dashed border-slate-200 text-xs text-slate-500 italic flex-1 text-center">
                    Tidak ada data sesudah mutasi (entitas dihapus).
                </div>
            @endif
        </div>
    </div>

    <!-- Metadata Additional -->
    @if ($audit->metadata)
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-2">
            <h3 class="text-xs font-bold text-slate-600">Metadata tambahan</h3>
            <pre tabindex="0" role="region" class="bg-slate-900 text-slate-200 p-3.5 rounded-xl text-xs font-mono overflow-x-auto whitespace-pre-wrap break-all focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-400 max-h-48 leading-relaxed">{{ json_encode($audit->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
        </div>
    @endif
</div>
@endsection
