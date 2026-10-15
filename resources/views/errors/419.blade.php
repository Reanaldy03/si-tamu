@extends('layouts.public')

@php
    try {
        $organization = \App\Models\OrganizationSetting::current();
    } catch (\Throwable) {
        $organization = null;
    }
@endphp

@section('title', 'Sesi berakhir — ' . ($organization?->organization_name ?? 'SI-TAMU'))

@section('content')
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-10 text-center space-y-5">
    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-warning-50 border border-warning-200">
        <svg aria-hidden="true" class="h-7 w-7 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
    </div>

    <div class="space-y-2">
        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">
            Sesi Anda telah berakhir.
        </h1>
        <p class="mx-auto max-w-md text-sm leading-relaxed text-slate-600">
            Silakan masuk kembali untuk melanjutkan.
        </p>
    </div>

    <div class="flex flex-col items-center justify-center gap-3 pt-1 sm:flex-row">
        <x-ui.button href="{{ route('login') }}" variant="primary">
            Masuk
        </x-ui.button>
        <x-ui.button href="{{ route('public.registration.create') }}" variant="secondary">
            Halaman Utama Tamu
        </x-ui.button>
    </div>
</div>
@endsection
