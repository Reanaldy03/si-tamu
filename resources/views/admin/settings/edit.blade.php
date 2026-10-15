@extends('layouts.admin')

@section('title', 'Pengaturan Sistem — SI-TAMU')
@section('header_title', 'Pengaturan Sistem')

@section('content')
@php
    $logoPath = $setting?->logo_path;
    $logoUrl = $logoPath && file_exists(public_path('storage/' . $logoPath))
        ? asset('storage/' . $logoPath)
        : null;
@endphp
<div class="max-w-3xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Pengaturan &amp; identitas organisasi</h1>
        <p class="text-xs sm:text-sm text-slate-600">
            Sesuaikan identitas instansi, petunjuk registrasi tamu, dan kebijakan privasi sistem.
        </p>
    </div>

    @if ($errors->any())
        <x-ui.alert tone="danger" focusable title="Periksa input Anda">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </x-ui.alert>
    @endif

    <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-xs">
        <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Section 1: Identitas Organisasi -->
            <div class="space-y-4">
                <h2 class="text-xs font-bold text-slate-600">Identitas instansi</h2>

                <x-form.input
                    name="organization_name"
                    label="Nama organisasi / instansi"
                    :value="$setting?->organization_name"
                    autocomplete="organization"
                    maxlength="150"
                    required
                />

                <!-- Logo Upload -->
                <div>
                    <x-form.label for="logo">Logo organisasi</x-form.label>
                    @if ($logoUrl)
                        <div class="mb-3 flex items-center gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200 w-fit">
                            <img src="{{ $logoUrl }}" alt="Logo instansi saat ini" class="h-12 w-auto object-contain">
                            <span class="text-xs text-slate-500">Logo saat ini terpasang</span>
                        </div>
                    @else
                        <div class="mb-3 flex items-start gap-3 p-3.5 rounded-xl bg-slate-50 border border-dashed border-slate-300">
                            <svg aria-hidden="true" class="w-5 h-5 shrink-0 text-slate-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <div class="text-xs space-y-0.5">
                                @if ($logoPath)
                                    <p class="font-semibold text-slate-700">Logo tidak dapat ditampilkan</p>
                                    <p class="text-slate-500">Berkas logo tersimpan, tetapi belum tersedia untuk diakses. Unggah ulang logo untuk memperbaruinya.</p>
                                @else
                                    <p class="font-semibold text-slate-700">Logo belum tersedia</p>
                                    <p class="text-slate-500">Unggah berkas logo agar tampil pada halaman tamu dan kop laporan.</p>
                                @endif
                            </div>
                        </div>
                    @endif
                    <input
                        type="file"
                        id="logo"
                        name="logo"
                        accept="image/png,image/jpeg,image/webp"
                        class="block w-full text-xs text-slate-600 rounded-xl cursor-pointer file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 transition focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2"
                    >
                    <p class="text-[11px] text-slate-500 mt-1">Format: PNG, JPG, atau WebP (maksimal 2MB).</p>
                </div>

                <x-form.textarea
                    name="address"
                    label="Alamat kantor / fisik"
                    rows="3"
                    maxlength="500"
                >{{ $setting?->address }}</x-form.textarea>

                <!-- Kontak Telepon & Email -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-form.input
                        name="contact_phone"
                        label="Nomor telepon kantor"
                        :value="$setting?->contact_phone"
                        inputmode="tel"
                        autocomplete="tel"
                        maxlength="20"
                    />

                    <x-form.input
                        name="contact_email"
                        type="email"
                        label="Alamat email kantor"
                        :value="$setting?->contact_email"
                        autocomplete="email"
                        maxlength="100"
                    />
                </div>
            </div>

            <!-- Section 2: Konfigurasi Sistem -->
            <div class="space-y-4 pt-4 border-t border-slate-100">
                <h2 class="text-xs font-bold text-slate-600">Konfigurasi operasional</h2>

                <x-form.select
                    name="timezone"
                    label="Zona waktu operasional"
                    :selected="$setting?->timezone"
                    :options="['Asia/Jakarta' => 'Asia/Jakarta (WIB — UTC+7)', 'Asia/Makassar' => 'Asia/Makassar (WITA — UTC+8)', 'Asia/Jayapura' => 'Asia/Jayapura (WIT — UTC+9)', 'UTC' => 'UTC (Universal Time Coordinated)']"
                    required
                />

                <x-form.textarea
                    name="registration_instruction"
                    label="Instruksi pengisian pada form tamu"
                    rows="4"
                    maxlength="500"
                    required
                >{{ $setting?->registration_instruction }}</x-form.textarea>

                <x-form.textarea
                    name="privacy_notice"
                    label="Pernyataan privasi data tamu"
                    rows="4"
                    maxlength="500"
                    required
                >{{ $setting?->privacy_notice }}</x-form.textarea>
            </div>

            <!-- Submit -->
            <div class="pt-4 flex items-center justify-stretch sm:justify-end border-t border-slate-100">
                <button
                    type="submit"
                    class="w-full sm:w-auto justify-center inline-flex items-center min-h-11 px-6 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-xs font-semibold shadow-xs transition cursor-pointer focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2"
                >
                    Simpan perubahan pengaturan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
