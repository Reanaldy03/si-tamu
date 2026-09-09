@extends('layouts.public')

@section('title', 'Survei Kepuasan Layanan — ' . ($organization?->organization_name ?? 'SI-TAMU'))

@section('content')
<div class="space-y-6 max-w-xl mx-auto">
    <!-- Header Banner -->
    <div class="text-center space-y-2">
        <div class="w-14 h-14 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center mx-auto shadow-xs">
            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
            </svg>
        </div>
        <h2 class="text-2xl font-bold tracking-tight text-slate-900">
            Survei Kepuasan Layanan
        </h2>
        <p class="text-xs sm:text-sm text-slate-600 max-w-md mx-auto">
            Bantu Sekretariat DPRD Kota Binjai meningkatkan mutu dan profesionalitas pelayanan dengan memberikan ulasan jujur Anda.
        </p>
    </div>

    <!-- Alert Notifications -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm flex items-start gap-3 shadow-xs">
            <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
            </svg>
            <p>{{ session('success') }}</p>
        </div>
    @endif

    @if(session('info'))
        <div class="p-4 rounded-2xl bg-sky-50 border border-sky-200 text-sky-800 text-xs sm:text-sm flex items-start gap-3 shadow-xs">
            <svg class="w-5 h-5 text-sky-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p>{{ session('info') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm flex items-start gap-3 shadow-xs">
            <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <!-- Main Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-7 space-y-6">
        <!-- Visit Reference Details -->
        <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 text-xs space-y-2">
            <div class="flex justify-between items-center">
                <span class="text-slate-500">Nomor Kunjungan</span>
                <span class="font-mono font-bold text-slate-900">{{ $visit->public_id }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-slate-500">Nama Tamu</span>
                <span class="font-semibold text-slate-900">{{ $visit->visitor_name }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-slate-500">Unit / Bagian Tujuan</span>
                <span class="font-medium text-slate-800">{{ $visit->department_name_snapshot }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-slate-500">Status Kunjungan</span>
                <span class="font-semibold text-indigo-700">{{ $visit->current_status->label() }}</span>
            </div>
        </div>

        @if($visit->feedback)
            <!-- Already Submitted State -->
            <div class="text-center py-6 space-y-3">
                <div class="flex items-center justify-center gap-1 text-amber-400">
                    @for($i = 1; $i <= 5; $i++)
                        <svg class="w-7 h-7 {{ $i <= $visit->feedback->rating ? 'text-amber-400 fill-current' : 'text-slate-200 fill-current' }}" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    @endfor
                </div>

                <div class="space-y-1">
                    <p class="text-sm font-bold text-slate-800">
                        Penilaian Anda: {{ $visit->feedback->rating }} dari 5 Bintang
                    </p>
                    @if($visit->feedback->feedback_text)
                        <p class="text-xs text-slate-600 italic bg-slate-50 p-3 rounded-lg border border-slate-200 max-w-md mx-auto">
                            &ldquo;{{ $visit->feedback->feedback_text }}&rdquo;
                        </p>
                    @endif
                    <p class="text-xs text-slate-500 pt-2">
                        Dikirim pada {{ $visit->feedback->submitted_at->timezone($organization?->timezone ?? 'Asia/Jakarta')->format('d M Y, H:i') }} WIB
                    </p>
                </div>

                <div class="pt-4">
                    <a 
                        href="{{ route('public.registration.create') }}" 
                        class="inline-flex items-center px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold shadow-xs transition"
                    >
                        Kembali ke Halaman Utama
                    </a>
                </div>
            </div>

        @elseif($visit->current_status !== \App\Domain\Visits\Enums\VisitStatus::COMPLETED)
            <!-- Not Completed Yet State -->
            <div class="text-center py-6 space-y-3">
                <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="space-y-1">
                    <p class="text-sm font-bold text-slate-800">Kunjungan Masih Berlangsung</p>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto">
                        Survei kepuasan akan terbuka secara otomatis setelah petugas bagian/komisi menandai kunjungan Anda selesai.
                    </p>
                </div>
                <div class="pt-2">
                    <a 
                        href="{{ route('public.registration.success', ['publicId' => $visit->public_id]) }}" 
                        class="inline-flex items-center px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition"
                    >
                        Buka Kartu Bukti Kunjungan
                    </a>
                </div>
            </div>

        @else
            <!-- Feedback Form -->
            <form 
                action="{{ route('public.feedback.store', ['publicId' => $visit->public_id]) }}" 
                method="POST"
                x-data="{
                    rating: {{ old('rating', 5) }},
                    hoverRating: 0,
                    labels: {
                        1: 'Sangat Tidak Puas',
                        2: 'Tidak Puas',
                        3: 'Cukup Puas',
                        4: 'Puas',
                        5: 'Sangat Puas'
                    }
                }"
                class="space-y-6"
            >
                @csrf

                <!-- Star Rating Section -->
                <div class="text-center space-y-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Tingkat Kepuasan Pelayanan <span class="text-rose-500">*</span>
                    </label>

                    <div class="flex items-center justify-center gap-2 pt-1">
                        <template x-for="star in [1, 2, 3, 4, 5]" :key="star">
                            <button 
                                type="button" 
                                @click="rating = star"
                                @mouseenter="hoverRating = star"
                                @mouseleave="hoverRating = 0"
                                class="p-1 rounded-lg transition hover:scale-110 focus:outline-none cursor-pointer"
                                :aria-label="'Bintang ' + star"
                            >
                                <svg 
                                    class="w-9 h-9 transition"
                                    :class="(hoverRating ? star <= hoverRating : star <= rating) ? 'text-amber-400 fill-current' : 'text-slate-200 fill-current'"
                                    viewBox="0 0 20 20"
                                >
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            </button>
                        </template>
                    </div>

                    <!-- Hidden input for form submit -->
                    <input type="hidden" name="rating" :value="rating">

                    <!-- Rating Label Description -->
                    <div class="h-5">
                        <span 
                            class="text-xs font-bold text-amber-700 bg-amber-50 px-3 py-1 rounded-full border border-amber-200" 
                            x-text="labels[hoverRating || rating]"
                        ></span>
                    </div>

                    @error('rating')
                        <p class="text-xs text-rose-600 font-medium pt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Textarea Feedback -->
                <div class="space-y-1.5">
                    <label for="feedback_text" class="block text-xs font-semibold text-slate-700">
                        Kritik, Saran, atau Pengalaman Layanan (Opsional)
                    </label>
                    <textarea 
                        id="feedback_text" 
                        name="feedback_text" 
                        rows="3" 
                        maxlength="1000"
                        placeholder="Contoh: Petugas lobi sangat ramah dan proses audiensi dengan Komisi A berjalan tepat waktu..."
                        class="w-full rounded-xl border border-slate-300 p-3 text-xs sm:text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >{{ old('feedback_text') }}</textarea>
                    @error('feedback_text')
                        <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit" 
                    class="w-full py-3 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-xs sm:text-sm font-bold shadow-xs transition cursor-pointer flex items-center justify-center gap-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    <span>Kirim Penilaian Layanan</span>
                </button>
            </form>
        @endif
    </div>
</div>
@endsection
