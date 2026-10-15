@extends('layouts.admin')

@section('title', 'Tambah Pengguna — SI-TAMU')
@section('header_title', 'Tambah Pengguna')

@section('content')
@php
    $roleOptions = $roles->pluck('name', 'slug')->all();
@endphp

<div class="max-w-2xl mx-auto space-y-6">
    <x-layout.page-header
        title="Tambah pengguna baru"
        subtitle="Daftarkan akun staf atau administrator back-office."
    >
        <x-slot:actions>
            <x-ui.button :href="route('admin.users.index')" variant="ghost" size="sm">
                &larr; Batal &amp; kembali
            </x-ui.button>
        </x-slot:actions>
    </x-layout.page-header>

    @if ($errors->any())
        <x-ui.alert tone="danger" title="Periksa input Anda:" focusable>
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </x-ui.alert>
    @endif

    <x-ui.card body-class="p-6 sm:p-8">
        <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-5">
            @csrf

            <x-form.input
                name="name"
                label="Nama lengkap"
                placeholder="Contoh: Siti Rahmawati"
                :required="true"
                :maxlength="100"
                autocomplete="name"
            />

            <x-form.input
                name="email"
                type="email"
                label="Alamat email (login)"
                placeholder="nama@instansi.go.id"
                :required="true"
                :maxlength="100"
                autocomplete="email"
            />

            {{-- Password dengan tombol tampil/sembunyikan --}}
            <div x-data="{ showPassword: false }">
                <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">
                    Password akun <span class="text-danger-600" aria-hidden="true">*</span><span class="sr-only"> (wajib diisi)</span>
                </label>
                <div class="relative">
                    <input
                        :type="showPassword ? 'text' : 'password'"
                        id="password"
                        name="password"
                        required
                        aria-required="true"
                        minlength="8"
                        placeholder="Minimal 8 karakter…"
                        @if ($errors->has('password')) aria-invalid="true" aria-describedby="password-hint password-error" @else aria-describedby="password-hint" @endif
                        class="w-full min-h-11 px-3.5 py-2.5 pr-12 rounded-lg border bg-white text-sm text-slate-900 placeholder:text-slate-500 focus:outline-hidden focus:ring-2 focus:border-primary-500 focus:ring-primary-500 @if ($errors->has('password')) border-danger-400 focus:border-danger-500 focus:ring-danger-500 @else border-slate-300 @endif"
                    >
                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        :aria-pressed="showPassword"
                        aria-controls="password"
                        :aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'"
                        :title="showPassword ? 'Sembunyikan password' : 'Tampilkan password'"
                        class="absolute inset-y-0 right-0 w-11 flex items-center justify-center rounded-r-lg text-slate-500 hover:text-primary-600 transition cursor-pointer focus:outline-hidden focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-primary-500"
                    >
                        <svg x-show="!showPassword" aria-hidden="true" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg x-show="showPassword" x-cloak aria-hidden="true" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                    </button>
                </div>
                <p id="password-hint" class="text-xs text-slate-500 mt-1">Gunakan kombinasi huruf, angka, dan simbol untuk keamanan maksimal.</p>
                @error('password')
                    <p id="password-error" role="alert" class="mt-1 text-xs text-danger-600 flex items-center gap-1">
                        <svg aria-hidden="true" class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div>
                <x-form.select
                    name="role"
                    label="Hak akses / peran"
                    :required="true"
                    empty="Pilih peran…"
                    :options="$roleOptions"
                />
                <div class="mt-1.5 space-y-0.5 text-[11px] text-slate-500">
                    @foreach ($roles as $r)
                        <p><span class="font-medium text-slate-600">{{ $r->name }}:</span> {{ $r->description }}</p>
                    @endforeach
                </div>
            </div>

            <div class="pt-2">
                <label class="relative flex items-center gap-3 min-h-11 cursor-pointer">
                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        {{ old('is_active', '1') == '1' ? 'checked' : '' }}
                        class="w-5 h-5 rounded border-slate-300 text-primary-600 focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-500"
                    >
                    <span class="text-sm font-medium text-slate-700">Akun aktif (dapat langsung digunakan untuk login)</span>
                </label>
            </div>

            <div class="pt-4 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3 border-t border-slate-100">
                <x-ui.button :href="route('admin.users.index')" variant="secondary" size="md" class="w-full sm:w-auto">Batal</x-ui.button>
                <x-ui.button type="submit" variant="primary" size="md" class="w-full sm:w-auto">Simpan pengguna baru</x-ui.button>
            </div>
        </form>
    </x-ui.card>
</div>
@endsection
