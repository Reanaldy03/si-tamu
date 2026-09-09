@extends('layouts.public')

@section('title', 'Form Registrasi Tamu — ' . ($organization?->organization_name ?? 'SI-TAMU'))

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="text-center sm:text-left space-y-1.5">
        <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">
            Form Registrasi Tamu
        </h2>
        <p class="text-sm text-slate-600">
            {{ $organization?->registration_instruction ?? 'Silakan lengkapi data kunjungan Anda di bawah ini dengan benar.' }}
        </p>
    </div>

    <!-- Error Alert Summary -->
    @if ($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm space-y-1">
            <div class="flex items-center gap-2 font-semibold">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Mohon periksa kembali formulir Anda.</span>
            </div>
            <ul class="list-disc list-inside text-xs text-rose-700 pl-7 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Registration Card Form -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
        <form 
            action="{{ route('public.registration.store') }}" 
            method="POST" 
            x-data="{ isSubmitting: false }" 
            @submit="if (!isSubmitting) { isSubmitting = true; } else { $event.preventDefault(); }"
            class="space-y-5"
        >
            @csrf

            <!-- Hidden Idempotency & Anti-Spam Tokens -->
            <input type="hidden" name="submission_token" value="{{ $submissionToken }}">
            <input 
                type="text" 
                name="website_hp" 
                aria-hidden="true" 
                tabindex="-1" 
                autocomplete="off" 
                class="hidden" 
                style="display: none !important; opacity: 0; position: absolute; left: -9999px;"
            >

            <!-- 1. Nama Lengkap -->
            <div>
                <label for="visitor_name" class="block text-sm font-semibold text-slate-900 mb-1.5">
                    Nama Lengkap <span class="text-rose-500" title="Wajib diisi">*</span>
                </label>
                <input 
                    type="text" 
                    id="visitor_name" 
                    name="visitor_name" 
                    value="{{ old('visitor_name') }}" 
                    required 
                    maxlength="100"
                    placeholder="Contoh: Budi Santoso" 
                    class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('visitor_name') border-rose-300 bg-rose-50/40 text-rose-900 focus:ring-rose-400 @else border-slate-300 text-slate-900 focus:border-indigo-500 @enderror"
                >
                @error('visitor_name')
                    <p class="text-xs text-rose-600 mt-1 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>{{ $message }}</span>
                    </p>
                @enderror
            </div>

            <!-- 2. Asal Instansi / Organisasi -->
            <div>
                <label for="origin_organization" class="block text-sm font-semibold text-slate-900 mb-1.5">
                    Asal Instansi / Organisasi <span class="text-rose-500" title="Wajib diisi">*</span>
                </label>
                <input 
                    type="text" 
                    id="origin_organization" 
                    name="origin_organization" 
                    value="{{ old('origin_organization') }}" 
                    required 
                    maxlength="150"
                    placeholder="Contoh: PT Harapan Bangsa / Umum / Universitas ..." 
                    class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('origin_organization') border-rose-300 bg-rose-50/40 text-rose-900 focus:ring-rose-400 @else border-slate-300 text-slate-900 focus:border-indigo-500 @enderror"
                >
                @error('origin_organization')
                    <p class="text-xs text-rose-600 mt-1 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>{{ $message }}</span>
                    </p>
                @enderror
            </div>

            <!-- 3. Unit / Bidang Tujuan -->
            <div>
                <label for="department_id" class="block text-sm font-semibold text-slate-900 mb-1.5">
                    Unit / Bidang Tujuan <span class="text-rose-500" title="Wajib diisi">*</span>
                </label>
                <select 
                    id="department_id" 
                    name="department_id" 
                    required 
                    class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('department_id') border-rose-300 bg-rose-50/40 text-rose-900 focus:ring-rose-400 @else border-slate-300 text-slate-900 focus:border-indigo-500 @enderror"
                >
                    <option value="" disabled {{ old('department_id') ? '' : 'selected' }}>Pilih unit tujuan...</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}@if ($dept->description) — {{ $dept->description }}@endif
                        </option>
                    @endforeach
                </select>
                @error('department_id')
                    <p class="text-xs text-rose-600 mt-1 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>{{ $message }}</span>
                    </p>
                @enderror
            </div>

            <!-- 4. Keperluan Kunjungan -->
            <div>
                <label for="purpose" class="block text-sm font-semibold text-slate-900 mb-1.5">
                    Keperluan Kunjungan <span class="text-rose-500" title="Wajib diisi">*</span>
                </label>
                <textarea 
                    id="purpose" 
                    name="purpose" 
                    rows="3" 
                    required 
                    maxlength="500"
                    placeholder="Tuliskan maksud atau keperluan kunjungan Anda..." 
                    class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('purpose') border-rose-300 bg-rose-50/40 text-rose-900 focus:ring-rose-400 @else border-slate-300 text-slate-900 focus:border-indigo-500 @enderror"
                >{{ old('purpose') }}</textarea>
                @error('purpose')
                    <p class="text-xs text-rose-600 mt-1 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>{{ $message }}</span>
                    </p>
                @enderror
            </div>

            <!-- 5. Kontak & Jumlah Rombongan (Two Columns on sm) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="contact_phone" class="block text-sm font-semibold text-slate-900 mb-1.5">
                        Nomor Kontak / WhatsApp <span class="text-xs font-normal text-slate-500">(opsional)</span>
                    </label>
                    <input 
                        type="tel" 
                        id="contact_phone" 
                        name="contact_phone" 
                        value="{{ old('contact_phone') }}" 
                        maxlength="20"
                        placeholder="Contoh: 081234567890" 
                        class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('contact_phone') border-rose-300 bg-rose-50/40 text-rose-900 focus:ring-rose-400 @else border-slate-300 text-slate-900 focus:border-indigo-500 @enderror"
                    >
                    @error('contact_phone')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="party_size" class="block text-sm font-semibold text-slate-900 mb-1.5">
                        Jumlah Rombongan <span class="text-xs font-normal text-slate-500">(orang)</span>
                    </label>
                    <input 
                        type="number" 
                        id="party_size" 
                        name="party_size" 
                        value="{{ old('party_size', 1) }}" 
                        min="1" 
                        max="100" 
                        class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('party_size') border-rose-300 bg-rose-50/40 text-rose-900 focus:ring-rose-400 @else border-slate-300 text-slate-900 focus:border-indigo-500 @enderror"
                    >
                    @error('party_size')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- 6. Email & Catatan Tambahan (Optional) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="visitor_email" class="block text-sm font-semibold text-slate-900 mb-1.5">
                        Alamat Email <span class="text-xs font-normal text-slate-500">(opsional)</span>
                    </label>
                    <input 
                        type="email" 
                        id="visitor_email" 
                        name="visitor_email" 
                        value="{{ old('visitor_email') }}" 
                        maxlength="100"
                        placeholder="nama@email.com" 
                        class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('visitor_email') border-rose-300 bg-rose-50/40 text-rose-900 focus:ring-rose-400 @else border-slate-300 text-slate-900 focus:border-indigo-500 @enderror"
                    >
                    @error('visitor_email')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="notes" class="block text-sm font-semibold text-slate-900 mb-1.5">
                        Catatan Tambahan <span class="text-xs font-normal text-slate-500">(opsional)</span>
                    </label>
                    <input 
                        type="text" 
                        id="notes" 
                        name="notes" 
                        value="{{ old('notes') }}" 
                        maxlength="500"
                        placeholder="Contoh: Membawa laptop / berkas" 
                        class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition focus:outline-none focus:ring-2 focus:ring-indigo-500 border-slate-300 text-slate-900 focus:border-indigo-500"
                    >
                </div>
            </div>

            <!-- Privacy Notice -->
            <div class="rounded-xl bg-slate-50 border border-slate-200 p-3.5 text-xs text-slate-600 flex items-start gap-2.5">
                <svg class="w-4 h-4 text-slate-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p>
                    {{ $organization?->privacy_notice ?? ('Data yang Anda isi digunakan untuk administrasi kunjungan dan dikelola oleh ' . ($organization?->organization_name ?? 'organisasi') . '.') }}
                </p>
            </div>

            <!-- Submit Button & Required Note -->
            <div class="pt-2 space-y-3">
                <button 
                    type="submit" 
                    :disabled="isSubmitting"
                    class="w-full py-3 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-semibold text-sm shadow-sm transition flex items-center justify-center gap-2 cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    <template x-if="!isSubmitting">
                        <span class="flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Daftar Kunjungan</span>
                        </span>
                    </template>
                    <template x-if="isSubmitting">
                        <span class="flex items-center justify-center gap-2">
                            <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Mendaftarkan…</span>
                        </span>
                    </template>
                </button>

                <p class="text-center text-xs text-slate-500">
                    <span class="text-rose-500 font-semibold">*</span> Menandakan field wajib diisi
                </p>
            </div>
        </form>
    </div>
</div>
@endsection
