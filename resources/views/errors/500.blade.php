@extends('layouts.public')

@php
    try {
        $organization = \App\Models\OrganizationSetting::current();
    } catch (\Throwable) {
        $organization = null;
    }

    // Kode referensi: pakai kode exception bila bermakna, selain itu turunkan
    // kode pendek yang stabil dari ID request agar petugas dapat melacaknya.
    $exceptionCode = isset($exception) ? $exception->getCode() : null;
    $requestId = request()->attributes->get('request_id');

    $referenceCode = is_int($exceptionCode) && $exceptionCode > 0
        ? 'REQ-' . $exceptionCode
        : 'REQ-' . strtoupper(substr(hash('sha256', (string) ($requestId ?? request()->fullUrl())), 0, 8));
@endphp

@section('title', 'Kendala sistem — ' . ($organization?->organization_name ?? 'SI-TAMU'))

@section('content')
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-10 text-center space-y-5">
    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-danger-50 border border-danger-200">
        <svg aria-hidden="true" class="h-7 w-7 text-danger-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
    </div>

    <div class="space-y-2">
        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">
            Terjadi kendala pada sistem.
        </h1>
        <p class="mx-auto max-w-md text-sm leading-relaxed text-slate-600">
            Coba lagi beberapa saat. Jika masalah berlanjut, sampaikan kode referensi berikut kepada petugas.
        </p>
    </div>

    <div class="mx-auto inline-flex flex-col items-center gap-1 rounded-xl border border-slate-200 bg-slate-50 px-5 py-3">
        <span class="text-[11px] font-semibold text-slate-500">Kode referensi</span>
        <code class="select-all font-mono text-base font-bold tracking-wider text-slate-900">{{ $referenceCode }}</code>
    </div>

    <div class="flex flex-col items-center justify-center gap-3 pt-1 sm:flex-row">
        <x-ui.button href="{{ route('public.registration.create') }}" variant="primary">
            Kembali ke Halaman Utama
        </x-ui.button>
        <x-ui.button href="{{ route('admin.dashboard') }}" variant="secondary">
            Masuk ke Dashboard
        </x-ui.button>
    </div>
</div>
@endsection
