@extends('layouts.public')

@section('title', 'Survei Kepuasan Layanan (IKM) — Sekretariat DPRD Kota Binjai')

@section('content')
<div class="space-y-6">
    <!-- Header Banner with Official DPRD Binjai Identity -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-7 text-center space-y-3">
        <div class="hidden sm:inline-flex items-center justify-center p-2 rounded-2xl bg-slate-50 border border-slate-100 shadow-inner">
            <img 
                src="{{ asset('assets/logo.webp') }}" 
                alt="Logo Sekretariat DPRD Kota Binjai" 
                class="h-16 sm:h-20 w-auto object-contain drop-shadow-xs"
            >
        </div>

        <div>
            <span class="inline-block px-3 py-1 rounded-full text-[11px] font-semibold bg-primary-50 text-primary-700 border border-primary-200 mb-2">
                Indeks Kepuasan Masyarakat (IKM) &bull; DPRD Kota Binjai
            </span>
            <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">
                Survei Kepuasan Layanan (IKM) — Sekretariat DPRD Kota Binjai
            </h2>
            <p class="text-xs sm:text-sm text-slate-600 max-w-lg mx-auto mt-1.5 leading-relaxed">
                Bantu kami meningkatkan mutu, profesionalitas, dan transparansi pelayanan publik di Gedung DPRD Kota Binjai dengan memberikan penilaian Anda.
            </p>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if(session('success'))
        <div role="status" class="p-4 rounded-2xl bg-success-50 border border-success-200 text-success-800 text-xs sm:text-sm flex items-start gap-3 shadow-xs animate-fade-in">
            <svg aria-hidden="true" class="w-5 h-5 text-success-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
            </svg>
            <div>
                <span class="font-bold block text-success-950">Terima Kasih!</span>
                <p class="mt-0.5">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if(session('info'))
        <div role="status" class="p-4 rounded-2xl bg-info-50 border border-info-200 text-info-800 text-xs sm:text-sm flex items-start gap-3 shadow-xs animate-fade-in">
            <svg aria-hidden="true" class="w-5 h-5 text-info-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p>{{ session('info') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div role="alert" class="p-4 rounded-2xl bg-danger-50 border border-danger-200 text-danger-800 text-xs sm:text-sm flex items-start gap-3 shadow-xs animate-fade-in">
            <svg aria-hidden="true" class="w-5 h-5 text-danger-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <!-- Main Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
        <!-- Ringkasan Kunjungan -->
        <div class="p-4 sm:p-5 bg-slate-50/80 rounded-2xl border border-slate-200/80 space-y-3">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 border-b border-slate-200/70 pb-2.5">
                <span class="text-xs font-semibold text-slate-500">Ringkasan Kunjungan</span>
                <span class="font-mono text-xs font-bold text-slate-700 bg-white px-2.5 py-1 rounded-lg border border-slate-200 shadow-2xs">
                    {{ $visit->public_id }}
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                <div>
                    <span class="text-slate-600 block font-medium">Nama Tamu</span>
                    <strong class="text-slate-900 text-sm font-semibold">{{ $visit->visitor_name }}</strong>
                </div>
                <div>
                    <span class="text-slate-600 block font-medium">Asal Instansi / Organisasi</span>
                    <strong class="text-slate-900 font-semibold">{{ $visit->origin_organization }}</strong>
                </div>
                <div class="sm:col-span-2 pt-1 border-t border-slate-100">
                    <span class="text-slate-600 block font-medium">Unit / Bidang yang Dikunjungi</span>
                    <strong class="text-primary-700 font-semibold">{{ $visit->department_name_snapshot }}</strong>
                </div>
            </div>
        </div>

        @if($visit->current_status !== \App\Domain\Visits\Enums\VisitStatus::COMPLETED)
            <!-- Status Incomplete Notice -->
            <div class="p-5 rounded-2xl bg-warning-50 border border-warning-200 text-warning-900 text-xs sm:text-sm space-y-3 text-center">
                <div class="w-10 h-10 rounded-full bg-warning-100 text-warning-600 flex items-center justify-center mx-auto">
                    <svg aria-hidden="true" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="font-bold text-base text-warning-950">Kunjungan Masih Berlangsung</h3>
                <p class="max-w-md mx-auto text-warning-800">
                    Formulir survei kepuasan hanya dapat diisi setelah kunjungan Anda dinyatakan selesai oleh petugas resepsionis atau staf penerima tamu.
                </p>
                <div class="pt-1">
                    <a
                        href="{{ route('public.registration.create') }}"
                        class="inline-flex items-center justify-center min-h-11 px-4 py-2.5 rounded-xl bg-white border border-warning-300 hover:bg-warning-100/50 text-warning-900 text-xs font-semibold shadow-xs transition focus:outline-hidden focus-visible:ring-2 focus-visible:ring-warning-600 focus-visible:ring-offset-2"
                    >
                        Kembali ke Halaman Utama
                    </a>
                </div>
            </div>
        @elseif($visit->feedback)
            <!-- Already Submitted Notice -->
            <div class="p-6 rounded-2xl bg-success-50/70 border border-success-200 text-center space-y-3">
                <div class="w-12 h-12 rounded-full bg-success-100 text-success-600 flex items-center justify-center mx-auto shadow-xs">
                    <svg aria-hidden="true" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-base text-success-950">Survei Telah Terisi</h3>
                    <p class="text-xs text-success-800 mt-1">
                        Anda telah memberikan penilaian untuk kunjungan ini pada {{ $visit->feedback->submitted_at ? $visit->feedback->submitted_at->format('d M Y, H:i') : '' }} WIB.
                    </p>
                </div>

                <div aria-hidden="true" class="flex items-center justify-center gap-1.5 text-warning-400 py-1">
                    @for($i = 1; $i <= 5; $i++)
                        <svg aria-hidden="true" class="w-6 h-6 {{ $i <= $visit->feedback->rating ? 'text-warning-400 fill-current' : 'text-slate-300 fill-current' }}" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    @endfor
                </div>

                @if($visit->feedback->feedback_text)
                    <p class="text-xs text-slate-700 bg-white p-3 rounded-xl border border-slate-200 italic max-w-md mx-auto">
                        &ldquo;{{ $visit->feedback->feedback_text }}&rdquo;
                    </p>
                @endif

                <div class="pt-1">
                    <a
                        href="{{ route('public.registration.create') }}"
                        class="inline-flex items-center justify-center min-h-11 px-4 py-2.5 rounded-xl bg-white border border-success-300 hover:bg-success-100/50 text-success-900 text-xs font-semibold shadow-xs transition focus:outline-hidden focus-visible:ring-2 focus-visible:ring-success-600 focus-visible:ring-offset-2"
                    >
                        Kembali ke Halaman Utama
                    </a>
                </div>
            </div>
        @else
            <!-- Survey Form -->
            <form 
                action="{{ route('public.feedback.store', ['publicId' => $visit->public_id]) }}" 
                method="POST"
                x-data="{
                    rating: 0,
                    hoverRating: 0,
                    submitting: false,
                    charCount: 0,
                    labels: {
                        1: 'Sangat Tidak Puas',
                        2: 'Tidak Puas',
                        3: 'Cukup',
                        4: 'Puas',
                        5: 'Sangat Puas'
                    },
                    setRating(value) {
                        this.rating = Math.min(5, Math.max(1, value));
                        this.$nextTick(() => {
                            this.$root.querySelectorAll('[role=radio]')[this.rating - 1]?.focus();
                        });
                    }
                }"
                @submit="submitting = true"
                class="space-y-6"
            >
                @csrf

                <!-- Star Rating Section -->
                <div class="text-center space-y-3">
                    <span id="rating-label" class="block text-xs font-semibold text-slate-700">
                        Tingkat Kepuasan Pelayanan <span class="text-danger-600" aria-hidden="true">*</span><span class="sr-only">(wajib diisi)</span>
                    </span>

                    <div role="radiogroup" aria-labelledby="rating-label" @error('rating') aria-describedby="rating-error" @enderror class="flex items-center justify-center gap-1.5 sm:gap-3 pt-1">
                        <template x-for="star in [1, 2, 3, 4, 5]" :key="star">
                            <button
                                type="button"
                                role="radio"
                                :aria-checked="rating === star"
                                :aria-label="'Bintang ' + star + ' dari 5: ' + labels[star]"
                                :tabindex="(rating || 1) === star ? 0 : -1"
                                @click="rating = star"
                                @keydown.arrow-right.prevent="setRating((rating || 0) + 1)"
                                @keydown.arrow-left.prevent="setRating((rating || 6) - 1)"
                                @keydown.arrow-down.prevent="setRating((rating || 0) + 1)"
                                @keydown.arrow-up.prevent="setRating((rating || 6) - 1)"
                                @keydown.home.prevent="setRating(1)"
                                @keydown.end.prevent="setRating(5)"
                                @mouseenter="hoverRating = star"
                                @mouseleave="hoverRating = 0"
                                class="min-h-10 min-w-10 p-1 sm:min-h-11 sm:min-w-11 sm:p-1.5 flex items-center justify-center rounded-xl transition duration-150 hover:scale-115 motion-reduce:hover:scale-100 cursor-pointer focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2"
                            >
                                <svg
                                    aria-hidden="true"
                                    class="w-8 h-8 sm:w-12 sm:h-12 transition drop-shadow-xs"
                                    :class="(hoverRating ? star <= hoverRating : star <= rating) ? 'text-warning-400 fill-current drop-shadow-md' : 'text-slate-300 fill-current hover:text-warning-200'"
                                    viewBox="0 0 20 20"
                                >
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                        </template>
                    </div>

                    <!-- Hidden input for form submit -->
                    <input type="hidden" name="rating" :value="rating">

                    <!-- Dynamic Rating Label Description -->
                    <div class="h-8 flex items-center justify-center">
                        <span
                            aria-live="polite"
                            class="text-xs font-bold px-4 py-1 rounded-full border transition duration-150" 
                            :class="{
                                'text-success-800 bg-success-50 border-success-200': (hoverRating || rating) === 5,
                                'text-info-800 bg-info-50 border-info-200': (hoverRating || rating) === 4,
                                'text-warning-800 bg-warning-50 border-warning-200': (hoverRating || rating) === 3,
                                'text-warning-800 bg-warning-50 border-warning-200': (hoverRating || rating) === 2,
                                'text-danger-800 bg-danger-50 border-danger-200': (hoverRating || rating) === 1,
                            }"
                            x-text="labels[hoverRating || rating] ?? 'Ketuk salah satu bintang untuk menilai'"
                        ></span>
                    </div>

                    @error('rating')
                        <p id="rating-error" role="alert" class="text-xs text-danger-600 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Textarea Feedback -->
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label for="feedback_text" class="block text-xs font-semibold text-slate-700">
                            Kritik, Saran, atau Apresiasi <span class="text-[11px] font-normal text-slate-500 lowercase">(opsional)</span>
                        </label>
                        <span id="feedback-counter" aria-hidden="true" class="text-[11px] text-slate-600 font-mono" x-text="charCount + '/1000'"></span>
                    </div>
                    <textarea
                        id="feedback_text"
                        name="feedback_text"
                        rows="4"
                        maxlength="1000"
                        @input="charCount = $event.target.value.length"
                        @error('feedback_text') aria-invalid="true" aria-describedby="feedback-counter feedback_text-error" @else aria-describedby="feedback-counter" @enderror
                        placeholder="Berikan kritik, saran membangun, atau apresiasi Anda terhadap keramahan dan profesionalitas petugas Sekretariat DPRD Kota Binjai..."
                        class="w-full rounded-xl border border-slate-300 p-4 text-xs sm:text-sm text-slate-900 placeholder:text-slate-500 focus:outline-hidden focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('feedback_text') border-danger-400 @enderror"
                    ></textarea>
                    @error('feedback_text')
                        <p id="feedback_text-error" role="alert" class="text-xs text-danger-600 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button with Loading State -->
                <button 
                    type="submit" 
                    :disabled="submitting || !rating"
                    class="w-full py-3.5 px-5 rounded-xl bg-primary-600 hover:bg-primary-700 active:bg-primary-800 disabled:opacity-60 disabled:cursor-not-allowed text-white text-xs sm:text-sm font-bold shadow-sm hover:shadow-md transition cursor-pointer flex items-center justify-center gap-2 focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2"
                >
                    <template x-if="submitting">
                        <svg aria-hidden="true" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>
                    <span x-text="submitting ? 'Mengirim Penilaian...' : 'Kirim Penilaian Kepuasan'"></span>
                </button>
            </form>
        @endif
    </div>
</div>
@endsection
