@extends('layouts.admin')

@section('title', 'Edit Departemen — ' . $department->name . ' — SI-TAMU')
@section('header_title', 'Edit Departemen')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Edit Unit / Departemen</h1>
            <p class="text-xs sm:text-sm text-slate-500">Perbarui data unit {{ $department->name }}.</p>
        </div>
        <a href="{{ route('admin.departments.index') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900 transition">
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
        <form action="{{ route('admin.departments.update', $department) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <!-- Kode Unit -->
            <div>
                <label for="code" class="block text-xs font-semibold text-slate-900 mb-1">
                    Kode Unit <span class="text-rose-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="code" 
                    name="code" 
                    value="{{ old('code', $department->code) }}" 
                    required 
                    maxlength="32"
                    class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm font-mono uppercase focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
            </div>

            <!-- Nama Unit -->
            <div>
                <label for="name" class="block text-xs font-semibold text-slate-900 mb-1">
                    Nama Unit / Departemen <span class="text-rose-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    value="{{ old('name', $department->name) }}" 
                    required 
                    maxlength="100"
                    class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
            </div>

            <!-- Deskripsi -->
            <div>
                <label for="description" class="block text-xs font-semibold text-slate-900 mb-1">
                    Deskripsi / Keterangan Singkat
                </label>
                <textarea 
                    id="description" 
                    name="description" 
                    rows="2" 
                    maxlength="500"
                    class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >{{ old('description', $department->description) }}</textarea>
            </div>

            <!-- Urutan & Status -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <div>
                    <label for="sort_order" class="block text-xs font-semibold text-slate-900 mb-1">
                        Urutan Tampilan
                    </label>
                    <input 
                        type="number" 
                        id="sort_order" 
                        name="sort_order" 
                        value="{{ old('sort_order', $department->sort_order) }}" 
                        min="0" 
                        max="9999" 
                        class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                </div>

                <div class="flex items-center pt-5">
                    <label class="relative flex items-center gap-3 cursor-pointer">
                        <input 
                            type="checkbox" 
                            name="is_active" 
                            value="1" 
                            {{ old('is_active', $department->is_active ? '1' : '0') == '1' ? 'checked' : '' }} 
                            class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                        >
                        <span class="text-xs font-semibold text-slate-900">Unit Aktif untuk Registrasi</span>
                    </label>
                </div>
            </div>

            <!-- Submit -->
            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                <a href="{{ route('admin.departments.index') }}" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-medium text-slate-600 hover:bg-slate-50 transition">
                    Batal
                </a>
                <button 
                    type="submit" 
                    class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-xs transition cursor-pointer"
                >
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
