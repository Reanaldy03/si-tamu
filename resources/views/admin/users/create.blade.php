@extends('layouts.admin')

@section('title', 'Tambah Pengguna — SI-TAMU')
@section('header_title', 'Tambah Pengguna')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Tambah Pengguna Baru</h1>
            <p class="text-xs sm:text-sm text-slate-500">Daftarkan akun staf atau administrator back-office.</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900 transition">
            &larr; Batal & Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
            <span class="font-bold">Periksa input Anda:</span>
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-xs">
        <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Nama Pengguna -->
            <div>
                <label for="name" class="block text-xs font-semibold text-slate-900 mb-1">
                    Nama Lengkap <span class="text-rose-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    value="{{ old('name') }}" 
                    required 
                    maxlength="100"
                    placeholder="Contoh: Siti Rahmawati" 
                    class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-xs font-semibold text-slate-900 mb-1">
                    Alamat Email (Login) <span class="text-rose-500">*</span>
                </label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="{{ old('email') }}" 
                    required 
                    maxlength="100"
                    placeholder="nama@instansi.go.id" 
                    class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-xs font-semibold text-slate-900 mb-1">
                    Password Akun <span class="text-rose-500">*</span>
                </label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required 
                    minlength="8"
                    placeholder="Minimal 8 karakter..." 
                    class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                <p class="text-[11px] text-slate-400 mt-1">Gunakan kombinasi huruf, angka, dan simbol untuk keamanan maksimal.</p>
            </div>

            <!-- Peran (Role) -->
            <div>
                <label for="role" class="block text-xs font-semibold text-slate-900 mb-1">
                    Hak Akses / Peran <span class="text-rose-500">*</span>
                </label>
                <select 
                    id="role" 
                    name="role" 
                    required 
                    class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                    <option value="" disabled {{ old('role') ? '' : 'selected' }}>Pilih peran...</option>
                    @foreach ($roles as $r)
                        <option value="{{ $r->slug }}" {{ old('role') === $r->slug ? 'selected' : '' }}>
                            {{ $r->name }} — {{ $r->description }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status Aktif -->
            <div class="pt-2">
                <label class="relative flex items-center gap-3 cursor-pointer">
                    <input 
                        type="checkbox" 
                        name="is_active" 
                        value="1" 
                        {{ old('is_active', '1') == '1' ? 'checked' : '' }} 
                        class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                    >
                    <span class="text-xs font-semibold text-slate-900">Akun Aktif (Dapat langsung digunakan untuk login)</span>
                </label>
            </div>

            <!-- Submit -->
            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                <a href="{{ route('admin.users.index') }}" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-medium text-slate-600 hover:bg-slate-50 transition">
                    Batal
                </a>
                <button 
                    type="submit" 
                    class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-xs transition cursor-pointer"
                >
                    Simpan Pengguna Baru
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
