@extends('layouts.admin')

@section('title', 'Tambah Departemen — SI-TAMU')
@section('header_title', 'Tambah Departemen')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Tambah unit / departemen</h1>
            <p class="text-xs sm:text-sm text-slate-600">Daftarkan unit baru sebagai tujuan kunjungan tamu.</p>
        </div>
        <a href="{{ route('admin.departments.index') }}" class="inline-flex items-center self-start sm:self-auto min-h-9 px-2 rounded-lg text-xs font-semibold text-slate-600 hover:text-slate-900 transition focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-500">
            &larr; Batal &amp; kembali
        </a>
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
        <form action="{{ route('admin.departments.store') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Kode Unit -->
            <div>
                <label for="code" class="block text-sm font-medium text-slate-700 mb-1.5">
                    Kode unit <span class="text-danger-600" aria-hidden="true">*</span><span class="sr-only"> (wajib diisi)</span>
                </label>
                <input
                    type="text"
                    id="code"
                    name="code"
                    value="{{ old('code') }}"
                    required
                    maxlength="32"
                    placeholder="Contoh: SEKRETARIAT / KEUANGAN / UMUM"
                    aria-describedby="code-hint"
                    @error('code') aria-invalid="true" @enderror
                    class="w-full min-h-11 px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm font-mono uppercase placeholder:text-slate-500 focus:outline-hidden focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                >
                <p id="code-hint" class="text-[11px] text-slate-500 mt-1">Kode unik singkatan unit (huruf kapital).</p>
                @error('code')
                    <p role="alert" class="mt-1 text-xs text-danger-600">{{ $message }}</p>
                @enderror
            </div>

            <x-form.input
                name="name"
                label="Nama unit / departemen"
                required
                maxlength="100"
                placeholder="Contoh: Bagian Umum dan Kepegawaian"
            />

            <x-form.textarea
                name="description"
                label="Deskripsi / keterangan singkat"
                rows="2"
                maxlength="500"
                placeholder="Contoh: Melayani pengurusan berkas administrasi dan persuratan"
            ></x-form.textarea>

            <!-- Urutan & Status -->
            <div class="space-y-4 pt-1">
                <x-form.input
                    name="sort_order"
                    type="number"
                    label="Urutan tampilan"
                    :value="0"
                    min="0"
                    max="9999"
                    hint="Urutan pada form dropdown tamu (angka terkecil tampil teratas)."
                />

                <div class="pt-2">
                    <label class="relative inline-flex items-center gap-3 min-h-11 cursor-pointer">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            {{ old('is_active', '1') == '1' ? 'checked' : '' }}
                            class="w-5 h-5 rounded border-slate-300 text-primary-600 focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-500"
                        >
                        <span class="text-sm font-medium text-slate-700">Unit aktif untuk registrasi</span>
                    </label>
                </div>
            </div>

            <!-- Submit -->
            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                <a href="{{ route('admin.departments.index') }}" class="inline-flex items-center min-h-11 px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-medium text-slate-600 hover:bg-slate-50 transition focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-500">
                    Batal
                </a>
                <button
                    type="submit"
                    class="min-h-11 px-5 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-xs font-semibold shadow-xs transition cursor-pointer focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2"
                >
                    Simpan unit baru
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
