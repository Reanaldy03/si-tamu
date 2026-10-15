@extends('layouts.public')

@php
    try {
        $organization = \App\Models\OrganizationSetting::current();
    } catch (\Throwable) {
        $organization = null;
    }
@endphp

@section('title', 'Halaman tidak ditemukan — ' . ($organization?->organization_name ?? 'SI-TAMU'))

@section('content')
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-10 text-center space-y-5">
    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-info-50 border border-info-200">
        <svg aria-hidden="true" class="h-7 w-7 text-info-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z" />
        </svg>
    </div>

    <div class="space-y-2">
        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">
            Halaman tidak ditemukan.
        </h1>
        <p class="mx-auto max-w-md text-sm leading-relaxed text-slate-600">
            Tautan mungkin sudah berubah atau data tidak tersedia.
        </p>
    </div>

    <div class="flex flex-col items-center justify-center gap-3 pt-1 sm:flex-row">
        <x-ui.button href="{{ route('public.registration.create') }}" variant="primary">
            Kembali ke Formulir Registrasi
        </x-ui.button>
        <x-ui.button href="{{ route('admin.dashboard') }}" variant="secondary">
            Masuk ke Dashboard
        </x-ui.button>
    </div>
</div>
@endsection
