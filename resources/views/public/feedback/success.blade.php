@extends('layouts.public')

@section('title', 'Terima Kasih Atas Ulasan Anda — Sekretariat DPRD Kota Binjai')

@section('content')
<div class="space-y-6 max-w-xl mx-auto">
    <!-- Success Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-7 sm:p-9 text-center space-y-5 animate-fade-in">
        <div class="inline-flex items-center justify-center p-2 rounded-2xl bg-slate-50 border border-slate-100 shadow-inner">
            <img 
                src="{{ asset('assets/logo.webp') }}" 
                alt="Logo Sekretariat DPRD Kota Binjai" 
                class="h-16 sm:h-20 w-auto object-contain drop-shadow-xs"
            >
        </div>

        <div class="space-y-2">
            <span class="inline-block px-3 py-1 rounded-full text-[11px] font-semibold bg-success-50 text-success-800 border border-success-200">
                Survei Berhasil Diserahkan
            </span>
            <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">
                Terima Kasih Atas Penilaian Anda!
            </h2>
            <p class="text-xs sm:text-sm text-slate-600 max-w-md mx-auto leading-relaxed">
                Partisipasi Anda sangat berharga bagi <strong>Sekretariat DPRD Kota Binjai</strong> dalam menjaga mutu integritas, transparansi, dan kenyamanan pelayanan publik bagi seluruh masyarakat.
            </p>
        </div>

        @if($visit->feedback)
            <div class="p-4 sm:p-5 bg-warning-50/70 rounded-2xl border border-warning-200 max-w-md mx-auto space-y-2.5">
                <div aria-hidden="true" class="flex items-center justify-center gap-1.5 text-warning-400">
                    @for($i = 1; $i <= 5; $i++)
                        <svg aria-hidden="true" class="w-6 h-6 {{ $i <= $visit->feedback->rating ? 'text-warning-400 fill-current' : 'text-slate-200 fill-current' }}" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    @endfor
                </div>

                <p class="text-xs font-bold text-slate-800">
                    Penilaian: {{ $visit->feedback->rating }} dari 5 Bintang ({{ $visit->feedback->ratingLabel() }})
                </p>

                @if($visit->feedback->feedback_text)
                    <p class="text-xs text-slate-700 bg-white p-3.5 rounded-xl border border-slate-200 italic">
                        &ldquo;{{ $visit->feedback->feedback_text }}&rdquo;
                    </p>
                @endif
            </div>
        @endif

        <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-3">
            <a
                href="{{ route('public.registration.create') }}"
                class="w-full sm:w-auto min-h-11 px-6 py-3.5 rounded-xl bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white text-xs sm:text-sm font-bold shadow-xs transition cursor-pointer flex items-center justify-center gap-2 focus:outline-hidden focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:ring-offset-2"
            >
                <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span>Halaman Utama / Registrasi Baru</span>
            </a>

            <a
                href="{{ route('public.registration.success', ['publicId' => $visit->public_id]) }}"
                class="w-full sm:w-auto min-h-11 px-5 py-3.5 rounded-xl bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs sm:text-sm font-semibold transition flex items-center justify-center gap-1.5 focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2"
            >
                <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <span>Lihat Kartu Tiket Tamu</span>
            </a>
        </div>
    </div>
</div>
@endsection
