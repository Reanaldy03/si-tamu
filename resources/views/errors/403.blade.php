@extends('layouts.public')

@php
    try {
        $organization = \App\Models\OrganizationSetting::current();
    } catch (\Throwable) {
        $organization = null;
    }
@endphp

@section('title', 'Akses ditolak — ' . ($organization?->organization_name ?? 'SI-TAMU'))

@section('content')
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-10 text-center space-y-5">
    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-danger-50 border border-danger-200">
        <svg aria-hidden="true" class="h-7 w-7 text-danger-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
        </svg>
    </div>

    <div class="space-y-2">
        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">
            Anda tidak memiliki akses ke halaman ini.
        </h1>
        <p class="mx-auto max-w-md text-sm leading-relaxed text-slate-600">
            Jika Anda merasa seharusnya memiliki akses, hubungi administrator.
        </p>
    </div>

    <div class="flex flex-col items-center justify-center gap-3 pt-1 sm:flex-row">
        <x-ui.button href="{{ route('admin.dashboard') }}" variant="primary">
            Kembali ke Dashboard
        </x-ui.button>
        <x-ui.button href="{{ route('public.registration.create') }}" variant="secondary">
            Halaman Utama Tamu
        </x-ui.button>
    </div>
</div>
@endsection
