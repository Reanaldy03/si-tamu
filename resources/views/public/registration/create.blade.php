@extends('layouts.public')

@section('title', 'Form Registrasi Tamu — ' . ($organization?->organization_name ?? 'Sekretariat DPRD Kota Binjai'))

@section('content')
<div class="space-y-6">
    <!-- Header Section with Official DPRD Binjai Identity -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-7 text-center space-y-3">
        <div class="hidden sm:inline-flex items-center justify-center p-2 rounded-2xl bg-slate-50 border border-slate-100 shadow-inner">
            <img 
                src="{{ asset('assets/logo.webp') }}" 
                alt="Logo Sekretariat DPRD Kota Binjai" 
                class="h-16 sm:h-20 w-auto object-contain drop-shadow-xs"
            >
        </div>
        
        <div>
            <span class="inline-block px-3 py-1 rounded-full text-[11px] font-semibold bg-primary-50 text-primary-700 border border-primary-100 mb-2">
                Pemerintah Kota Binjai &bull; Sekretariat DPRD
            </span>
            <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">
                Formulir Buku Tamu Digital
            </h2>
            <p class="text-xs sm:text-sm text-slate-600 max-w-lg mx-auto mt-1.5 leading-relaxed">
                {{ $organization?->registration_instruction ?? 'Silakan mengisi formulir kunjungan secara mandiri untuk mendapatkan QR Code tiket kedatangan di lobi Gedung DPRD Kota Binjai.' }}
            </p>
        </div>
    </div>

    <!-- Error Alert Summary -->
    @if ($errors->any())
        <div id="form-errors" role="alert" tabindex="-1" class="p-4 rounded-xl bg-danger-50 border border-danger-200 text-danger-800 text-sm space-y-1.5 focus:outline-hidden">
            <div class="flex items-center gap-2 font-semibold">
                <svg aria-hidden="true" class="w-5 h-5 text-danger-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Terdapat kesalahan pada isian formulir:</span>
            </div>
            <ul class="list-disc list-inside text-xs text-danger-700 pl-7 space-y-0.5">
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
            x-data="{ 
                isSubmitting: false,
                selectedDept: @js(old('department_id', '')),
                descriptions: {
                    @foreach ($departments as $dept)
                        '{{ $dept->id }}': @js($dept->description),
                    @endforeach
                }
            }" 
            @submit="if (!isSubmitting) { isSubmitting = true; } else { $event.preventDefault(); }"
            class="space-y-6"
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

            <!-- Bagian 1: Identitas Tamu -->
            <div class="space-y-4">
                <div class="border-b border-slate-200/80 pb-2 flex items-center justify-between flex-wrap gap-1">
                    <h3 class="text-sm font-semibold text-slate-700 flex items-center gap-1.5">
                        <svg aria-hidden="true" class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span>1. Identitas Pengunjung</span>
                    </h3>
                    <span class="text-xs text-slate-500">
                        <span class="text-danger-600 font-semibold" aria-hidden="true">*</span> Menandakan kolom wajib diisi
                    </span>
                </div>

                <!-- 1. Nama Lengkap -->
                <div>
                    <label for="visitor_name" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Nama Lengkap <span class="text-danger-600" aria-hidden="true">*</span><span class="sr-only">(wajib diisi)</span>
                    </label>
                    <input 
                        type="text" 
                        id="visitor_name"
                        autocomplete="name"
                        name="visitor_name" 
                        value="{{ old('visitor_name') }}" 
                        required 
                        maxlength="100"
                        placeholder="Contoh: Budi Santoso" 
                        @error('visitor_name') aria-invalid="true" aria-describedby="visitor_name-error" @enderror
                        class="w-full px-4 py-3 rounded-xl border text-sm transition placeholder:text-slate-500 focus:outline-hidden focus:ring-2 focus:ring-primary-500 @error('visitor_name') border-danger-300 bg-danger-50/40 text-danger-900 focus:ring-danger-400 @else border-slate-300 text-slate-900 focus:border-primary-500 @enderror"
                    >
                    @error('visitor_name')
                        <p id="visitor_name-error" role="alert" class="text-xs text-danger-600 mt-1 flex items-center gap-1">
                            <svg aria-hidden="true" class="w-3.5 h-3.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>

                <!-- 2. Asal Instansi / Organisasi -->
                <div>
                    <label for="origin_organization" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Asal Instansi / Organisasi / Lembaga <span class="text-danger-600" aria-hidden="true">*</span><span class="sr-only">(wajib diisi)</span>
                    </label>
                    <input 
                        type="text" 
                        id="origin_organization"
                        autocomplete="organization"
                        name="origin_organization" 
                        value="{{ old('origin_organization') }}" 
                        required 
                        maxlength="150"
                        placeholder="Contoh: Dinas Pendidikan / Universitas Sumatera Utara / Pribadi ..." 
                        @error('origin_organization') aria-invalid="true" aria-describedby="origin_organization-error" @enderror
                        class="w-full px-4 py-3 rounded-xl border text-sm transition placeholder:text-slate-500 focus:outline-hidden focus:ring-2 focus:ring-primary-500 @error('origin_organization') border-danger-300 bg-danger-50/40 text-danger-900 focus:ring-danger-400 @else border-slate-300 text-slate-900 focus:border-primary-500 @enderror"
                    >
                    @error('origin_organization')
                        <p id="origin_organization-error" role="alert" class="text-xs text-danger-600 mt-1 flex items-center gap-1">
                            <svg aria-hidden="true" class="w-3.5 h-3.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>
            </div>

            <!-- Bagian 2: Tujuan & Keperluan -->
            <div class="space-y-4 pt-2">
                <div class="border-b border-slate-200/80 pb-2">
                    <h3 class="text-sm font-semibold text-slate-700 flex items-center gap-1.5">
                        <svg aria-hidden="true" class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <span>2. Unit Tujuan & Keperluan</span>
                    </h3>
                </div>

                <!-- 3. Unit / Bidang Tujuan -->
                <div class="space-y-2">
                    <label for="department_id" class="block text-xs font-semibold text-slate-700">
                        Unit / Bidang Tujuan <span class="text-danger-600" aria-hidden="true">*</span><span class="sr-only">(wajib diisi)</span>
                    </label>
                    <p id="department_id-hint" class="text-xs text-slate-600 leading-relaxed">
                        Belum tahu unit yang dituju? Pilih <strong class="font-semibold text-slate-700">Bagian Umum dan Keuangan</strong>, petugas akan mengarahkan Anda.
                    </p>
                    <div class="relative">
                        <select 
                            id="department_id" 
                            name="department_id" 
                            x-model="selectedDept"
                            required 
                            aria-describedby="department_id-hint @error('department_id') department_id-error @enderror"
                            @error('department_id') aria-invalid="true" @enderror
                            class="w-full px-4 py-3 rounded-xl border text-sm transition bg-white appearance-none focus:outline-hidden focus:ring-2 focus:ring-primary-500 pr-10 cursor-pointer @error('department_id') border-danger-300 bg-danger-50/40 text-danger-900 focus:ring-danger-400 @else border-slate-300 text-slate-900 focus:border-primary-500 @enderror"
                        >
                            <option value="" disabled {{ old('department_id') ? '' : 'selected' }}>Pilih Unit / Bidang Tujuan...</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}{{ filled($dept->description) ? ' — ' . \Illuminate\Support\Str::limit($dept->description, 60, '…') : '' }}
                                </option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-400">
                            <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </div>
                    </div>

                    <!-- Dynamic Tupoksi Box (Clean, Elegant & Non-intrusive) -->
                    <template x-if="selectedDept && descriptions[selectedDept]">
                        <div class="p-3 bg-primary-50/80 border border-primary-100 rounded-xl text-xs text-primary-900 flex items-start gap-2.5">
                            <svg aria-hidden="true" class="w-4 h-4 text-primary-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <span class="font-bold block text-primary-950">Lingkup Pelayanan:</span>
                                <span x-text="descriptions[selectedDept]" class="leading-relaxed"></span>
                            </div>
                        </div>
                    </template>

                    @error('department_id')
                        <p id="department_id-error" role="alert" class="text-xs text-danger-600 mt-1 flex items-center gap-1">
                            <svg aria-hidden="true" class="w-3.5 h-3.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>

                <!-- 4. Tanggal Kunjungan & Nomor Surat -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="visit_date" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Tanggal Kunjungan <span class="text-danger-600" aria-hidden="true">*</span><span class="sr-only">(wajib diisi)</span>
                        </label>
                        <input 
                            type="date" 
                            id="visit_date" 
                            name="visit_date" 
                            value="{{ old('visit_date', now($organization?->timezone ?? 'Asia/Jakarta')->toDateString()) }}" 
                            required 
                            @error('visit_date') aria-invalid="true" aria-describedby="visit_date-error" @enderror
                            class="w-full px-4 py-3 rounded-xl border text-sm transition placeholder:text-slate-500 focus:outline-hidden focus:ring-2 focus:ring-primary-500 @error('visit_date') border-danger-300 bg-danger-50/40 text-danger-900 focus:ring-danger-400 @else border-slate-300 text-slate-900 focus:border-primary-500 @enderror"
                        >
                        @error('visit_date')
                            <p id="visit_date-error" role="alert" class="text-xs text-danger-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="letter_number" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Nomor Surat <span class="text-[11px] font-normal text-slate-500 lowercase">(opsional)</span>
                        </label>
                        <input 
                            type="text" 
                            id="letter_number" 
                            name="letter_number" 
                            value="{{ old('letter_number') }}" 
                            maxlength="100"
                            placeholder="Contoh: 005/123/DPRD/2026" 
                            @error('letter_number') aria-invalid="true" aria-describedby="letter_number-error" @enderror
                            class="w-full px-4 py-3 rounded-xl border text-sm transition placeholder:text-slate-500 focus:outline-hidden focus:ring-2 focus:ring-primary-500 @error('letter_number') border-danger-300 bg-danger-50/40 text-danger-900 focus:ring-danger-400 @else border-slate-300 text-slate-900 focus:border-primary-500 @enderror"
                        >
                        @error('letter_number')
                            <p id="letter_number-error" role="alert" class="text-xs text-danger-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- 5. Keperluan Kunjungan -->
                <div>
                    <label for="purpose" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Keperluan Kunjungan <span class="text-danger-600" aria-hidden="true">*</span><span class="sr-only">(wajib diisi)</span>
                    </label>
                    <textarea 
                        id="purpose" 
                        name="purpose" 
                        rows="3" 
                        required 
                        maxlength="500"
                        placeholder="Tuliskan maksud atau keperluan kunjungan kedinasan / konsultasi Anda..." 
                        @error('purpose') aria-invalid="true" aria-describedby="purpose-error" @enderror
                        class="w-full px-4 py-3 rounded-xl border text-sm transition placeholder:text-slate-500 focus:outline-hidden focus:ring-2 focus:ring-primary-500 @error('purpose') border-danger-300 bg-danger-50/40 text-danger-900 focus:ring-danger-400 @else border-slate-300 text-slate-900 focus:border-primary-500 @enderror"
                    >{{ old('purpose') }}</textarea>
                    @error('purpose')
                        <p id="purpose-error" role="alert" class="text-xs text-danger-600 mt-1 flex items-center gap-1">
                            <svg aria-hidden="true" class="w-3.5 h-3.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>
            </div>

            <!-- Bagian 3: Rincian & Kontak Tambahan -->
            <div class="space-y-4 pt-2">
                <div class="border-b border-slate-200/80 pb-2">
                    <h3 class="text-sm font-semibold text-slate-700 flex items-center gap-1.5">
                        <svg aria-hidden="true" class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        <span>3. Kontak & Informasi Tambahan</span>
                    </h3>
                </div>

                <!-- 5. Kontak & Jumlah Rombongan -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="contact_phone" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Nomor Kontak / WhatsApp <span class="text-[11px] font-normal text-slate-500 lowercase">(opsional)</span>
                        </label>
                        <input 
                            type="tel" 
                            id="contact_phone"
                            autocomplete="tel" inputmode="tel"
                            name="contact_phone" 
                            value="{{ old('contact_phone') }}" 
                            maxlength="20"
                            placeholder="Contoh: 081234567890" 
                            @error('contact_phone') aria-invalid="true" aria-describedby="contact_phone-error" @enderror
                            class="w-full px-4 py-3 rounded-xl border text-sm transition placeholder:text-slate-500 focus:outline-hidden focus:ring-2 focus:ring-primary-500 @error('contact_phone') border-danger-300 bg-danger-50/40 text-danger-900 focus:ring-danger-400 @else border-slate-300 text-slate-900 focus:border-primary-500 @enderror"
                        >
                        @error('contact_phone')
                            <p id="contact_phone-error" role="alert" class="text-xs text-danger-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="party_size" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Jumlah Rombongan <span class="text-danger-600" aria-hidden="true">*</span> <span class="text-[11px] font-normal text-slate-500">(jumlah orang)</span><span class="sr-only">(wajib diisi)</span>
                        </label>
                        <input 
                            type="number" 
                            id="party_size"
                            inputmode="numeric"
                            name="party_size" 
                            value="{{ old('party_size', 1) }}" 
                            min="1" 
                            max="100" 
                            required
                            @error('party_size') aria-invalid="true" aria-describedby="party_size-error" @enderror
                            class="w-full px-4 py-3 rounded-xl border text-sm transition placeholder:text-slate-500 focus:outline-hidden focus:ring-2 focus:ring-primary-500 @error('party_size') border-danger-300 bg-danger-50/40 text-danger-900 focus:ring-danger-400 @else border-slate-300 text-slate-900 focus:border-primary-500 @enderror"
                        >
                        @error('party_size')
                            <p id="party_size-error" role="alert" class="text-xs text-danger-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- 6. Email & Catatan Tambahan -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="visitor_email" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Alamat Email <span class="text-[11px] font-normal text-slate-500 lowercase">(opsional)</span>
                        </label>
                        <input 
                            type="email" 
                            id="visitor_email"
                            autocomplete="email"
                            name="visitor_email" 
                            value="{{ old('visitor_email') }}" 
                            maxlength="100"
                            placeholder="nama@instansi.go.id" 
                            @error('visitor_email') aria-invalid="true" aria-describedby="visitor_email-error" @enderror
                            class="w-full px-4 py-3 rounded-xl border text-sm transition placeholder:text-slate-500 focus:outline-hidden focus:ring-2 focus:ring-primary-500 @error('visitor_email') border-danger-300 bg-danger-50/40 text-danger-900 focus:ring-danger-400 @else border-slate-300 text-slate-900 focus:border-primary-500 @enderror"
                        >
                        @error('visitor_email')
                            <p id="visitor_email-error" role="alert" class="text-xs text-danger-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="notes" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Catatan Tambahan <span class="text-[11px] font-normal text-slate-500 lowercase">(opsional)</span>
                        </label>
                        <input 
                            type="text" 
                            id="notes" 
                            name="notes" 
                            value="{{ old('notes') }}" 
                            maxlength="500"
                            placeholder="Contoh: Membawa berkas surat dinas" 
                            class="w-full px-4 py-3 rounded-xl border text-sm transition placeholder:text-slate-500 focus:outline-hidden focus:ring-2 focus:ring-primary-500 border-slate-300 text-slate-900 focus:border-primary-500"
                        >
                    </div>
                </div>
            </div>

            <!-- Privacy Notice -->
            <div class="rounded-xl bg-slate-50 border border-slate-200 p-3.5 text-xs text-slate-600 flex items-start gap-2.5">
                <svg aria-hidden="true" class="w-4 h-4 text-primary-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <p class="leading-relaxed">
                    {{ $organization?->privacy_notice ?? ('Data kunjungan dikelola resmi oleh ' . ($organization?->organization_name ?? 'Sekretariat DPRD Kota Binjai') . ' untuk dokumentasi kedinasan dan keamanan kantor.') }}
                </p>
            </div>

            <!-- Submit Button & Required Note -->
            <div class="pt-2">
                <button 
                    type="submit" 
                    :disabled="isSubmitting"
                    class="w-full py-3.5 px-5 rounded-xl bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white font-bold text-sm shadow-sm hover:shadow-md transition duration-150 flex items-center justify-center gap-2 cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed focus:outline-hidden focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                >
                    <template x-if="!isSubmitting">
                        <span class="flex items-center justify-center gap-2">
                            <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Daftar Kunjungan & Dapatkan QR Tiket</span>
                        </span>
                    </template>
                    <template x-if="isSubmitting">
                        <span class="flex items-center justify-center gap-2">
                            <svg aria-hidden="true" class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Mendaftarkan Kunjungan…</span>
                        </span>
                    </template>
                </button>
            </div>
        </form>
    </div>
</div>

@if ($errors->any())
    @push('scripts')
        <script>
            document.getElementById('form-errors')?.focus();
        </script>
    @endpush
@endif
@endsection
