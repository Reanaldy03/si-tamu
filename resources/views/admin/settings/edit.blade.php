@extends('layouts.admin')

@section('title', 'Pengaturan Sistem — SI-TAMU')
@section('header_title', 'Pengaturan Sistem')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Pengaturan & Identitas Organisasi</h1>
        <p class="text-xs sm:text-sm text-slate-500">
            Sesuaikan identitas instansi, petunjuk registrasi tamu, dan kebijakan privasi sistem.
        </p>
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
        <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Section 1: Identitas Organisasi -->
            <div class="space-y-4">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400">Identitas Instansi</h2>

                <!-- Nama Organisasi -->
                <div>
                    <label for="organization_name" class="block text-xs font-semibold text-slate-900 mb-1">
                        Nama Organisasi / Instansi <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="organization_name" 
                        name="organization_name" 
                        value="{{ old('organization_name', $setting?->organization_name) }}" 
                        required 
                        maxlength="150"
                        class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                </div>

                <!-- Logo Upload -->
                <div>
                    <label class="block text-xs font-semibold text-slate-900 mb-1">Logo Organisasi</label>
                    @if ($setting?->logo_path)
                        <div class="mb-3 flex items-center gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200 w-fit">
                            <img src="{{ asset('storage/' . $setting->logo_path) }}" alt="Logo Instansi" class="h-12 w-auto object-contain">
                            <span class="text-xs text-slate-500">Logo saat ini terpasang</span>
                        </div>
                    @endif
                    <input 
                        type="file" 
                        name="logo" 
                        accept="image/png,image/jpeg,image/svg+xml"
                        class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition"
                    >
                    <p class="text-[11px] text-slate-400 mt-1">Format: PNG, JPG, atau SVG (Maksimal 2MB).</p>
                </div>

                <!-- Alamat -->
                <div>
                    <label for="address" class="block text-xs font-semibold text-slate-900 mb-1">
                        Alamat Kantor / Fisik
                    </label>
                    <textarea 
                        id="address" 
                        name="address" 
                        rows="2" 
                        maxlength="500"
                        class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >{{ old('address', $setting?->address) }}</textarea>
                </div>

                <!-- Kontak Telepon & Email -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="contact_phone" class="block text-xs font-semibold text-slate-900 mb-1">
                            Nomor Telepon Kantor
                        </label>
                        <input 
                            type="text" 
                            id="contact_phone" 
                            name="contact_phone" 
                            value="{{ old('contact_phone', $setting?->contact_phone) }}" 
                            maxlength="20"
                            class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                    </div>

                    <div>
                        <label for="contact_email" class="block text-xs font-semibold text-slate-900 mb-1">
                            Alamat Email Kantor
                        </label>
                        <input 
                            type="email" 
                            id="contact_email" 
                            name="contact_email" 
                            value="{{ old('contact_email', $setting?->contact_email) }}" 
                            maxlength="100"
                            class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                    </div>
                </div>
            </div>

            <!-- Section 2: Konfigurasi Sistem -->
            <div class="space-y-4 pt-4 border-t border-slate-100">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400">Konfigurasi Operasional</h2>

                <!-- Timezone -->
                <div>
                    <label for="timezone" class="block text-xs font-semibold text-slate-900 mb-1">
                        Zona Waktu Operasional <span class="text-rose-500">*</span>
                    </label>
                    <select 
                        id="timezone" 
                        name="timezone" 
                        required 
                        class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <option value="Asia/Jakarta" {{ old('timezone', $setting?->timezone) === 'Asia/Jakarta' ? 'selected' : '' }}>
                            Asia/Jakarta (WIB — UTC+7)
                        </option>
                        <option value="Asia/Makassar" {{ old('timezone', $setting?->timezone) === 'Asia/Makassar' ? 'selected' : '' }}>
                            Asia/Makassar (WITA — UTC+8)
                        </option>
                        <option value="Asia/Jayapura" {{ old('timezone', $setting?->timezone) === 'Asia/Jayapura' ? 'selected' : '' }}>
                            Asia/Jayapura (WIT — UTC+9)
                        </option>
                        <option value="UTC" {{ old('timezone', $setting?->timezone) === 'UTC' ? 'selected' : '' }}>
                            UTC (Universal Time Coordinated)
                        </option>
                    </select>
                </div>

                <!-- Instruksi Registrasi -->
                <div>
                    <label for="registration_instruction" class="block text-xs font-semibold text-slate-900 mb-1">
                        Instruksi Pengisian pada Form Tamu <span class="text-rose-500">*</span>
                    </label>
                    <textarea 
                        id="registration_instruction" 
                        name="registration_instruction" 
                        rows="2" 
                        required 
                        maxlength="500"
                        class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >{{ old('registration_instruction', $setting?->registration_instruction) }}</textarea>
                </div>

                <!-- Kebijakan Privasi -->
                <div>
                    <label for="privacy_notice" class="block text-xs font-semibold text-slate-900 mb-1">
                        Pernyataan Privasi Data Tamu <span class="text-rose-500">*</span>
                    </label>
                    <textarea 
                        id="privacy_notice" 
                        name="privacy_notice" 
                        rows="2" 
                        required 
                        maxlength="500"
                        class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >{{ old('privacy_notice', $setting?->privacy_notice) }}</textarea>
                </div>
            </div>

            <!-- Submit -->
            <div class="pt-4 flex items-center justify-end border-t border-slate-100">
                <button 
                    type="submit" 
                    class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-xs transition cursor-pointer"
                >
                    Simpan Perubahan Pengaturan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
