@extends('layouts.admin')

@section('title', 'Survei & Indeks Kepuasan Layanan (IKM)')

@section('header_title', 'Survei Kepuasan (IKM)')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 sm:p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 flex items-center gap-2.5">
                <svg aria-hidden="true" class="w-6 h-6 text-warning-500" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
                <span>Indeks Kepuasan Layanan (IKM)</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Laporan evaluasi ulasan dan saran masyarakat terhadap pelayanan publik di Sekretariat DPRD Kota Binjai.
            </p>
        </div>
    </div>

    <!-- IKM Metrics & Star Breakdown Card -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
        <!-- Average Score Box (4 cols) -->
        <div class="md:col-span-4 bg-white rounded-2xl border border-slate-200 shadow-xs p-6 flex flex-col items-center justify-center text-center space-y-3">
            <span class="text-xs font-bold text-slate-500">Skor rata-rata IKM</span>
            <div class="text-5xl font-black tracking-tight text-slate-900">
                {{ number_format($avgRating, 1) }}
                <span class="text-base font-medium text-slate-500">/ 5.0</span>
            </div>

            <div class="flex items-center gap-1 text-warning-400" aria-hidden="true">
                @for($i = 1; $i <= 5; $i++)
                    <svg aria-hidden="true" class="w-6 h-6 {{ $i <= round($avgRating) ? 'text-warning-400 fill-current' : 'text-slate-200 fill-current' }}" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                @endfor
            </div>

            <div class="pt-1">
                @if($avgRating >= 4.5)
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-success-100 text-success-800">Mutu pelayanan: sangat baik (A)</span>
                @elseif($avgRating >= 3.5)
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-info-100 text-info-800">Mutu pelayanan: baik (B)</span>
                @elseif($avgRating >= 2.5)
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-warning-100 text-warning-800">Mutu pelayanan: cukup (C)</span>
                @elseif($avgRating > 0)
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-danger-100 text-danger-800">Mutu pelayanan: perlu perbaikan (D)</span>
                @else
                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600">Belum ada responden</span>
                @endif
            </div>

            <p class="text-xs text-slate-500 pt-1">
                Berdasarkan <strong>{{ number_format($totalCount) }}</strong> responden tamu.
            </p>
        </div>

        <!-- Star Breakdown Progress Bars (8 cols) -->
        <div class="md:col-span-8 bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-3">
            <span class="text-xs font-bold text-slate-500 block mb-2">Distribusi penilaian bintang</span>

            @foreach($distribution as $star => $data)
                <div class="flex items-center gap-3 text-xs">
                    <span class="w-16 font-semibold text-slate-700 flex items-center gap-1" aria-hidden="true">
                        {{ $star }}
                        <svg aria-hidden="true" class="w-3.5 h-3.5 text-warning-400 fill-current" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    </span>

                    <div
                        class="flex-1 h-3 bg-slate-100 rounded-full overflow-hidden"
                        role="progressbar"
                        aria-valuenow="{{ $data['percentage'] }}"
                        aria-valuemin="0"
                        aria-valuemax="100"
                        aria-label="Penilaian {{ $star }} bintang: {{ $data['count'] }} responden ({{ $data['percentage'] }} persen)"
                    >
                        <div
                            class="h-full bg-warning-400 rounded-full motion-reduce:transition-none transition-all duration-500"
                            style="width: {{ $data['percentage'] }}%"
                        ></div>
                    </div>

                    <span class="w-20 text-right text-slate-600 font-medium" aria-hidden="true">
                        {{ $data['count'] }} ({{ $data['percentage'] }}%)
                    </span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Filter Pills -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:flex-wrap">
        <a
            href="{{ route('admin.feedback.index') }}"
            @if(!request()->filled('rating')) aria-current="page" @endif
            class="inline-flex items-center min-h-11 px-3.5 py-2 rounded-xl text-xs font-semibold shrink-0 transition focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2 {{ !request()->filled('rating') ? 'bg-primary-600 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50' }}"
        >
            Semua rating
        </a>
        @for($r = 5; $r >= 1; $r--)
            <a
                href="{{ route('admin.feedback.index', ['rating' => $r]) }}"
                @if(request('rating') == $r) aria-current="page" @endif
                class="inline-flex items-center min-h-11 px-3.5 py-2 rounded-xl text-xs font-semibold shrink-0 transition gap-1 focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2 {{ request('rating') == $r ? 'bg-primary-600 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50' }}"
            >
                <span>{{ $r }} bintang</span>
                <svg aria-hidden="true" class="w-3.5 h-3.5 {{ request('rating') == $r ? 'text-warning-300' : 'text-warning-400' }} fill-current" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
            </a>
        @endfor
    </div>

    <!-- Feedback List -->
    <h2 class="sr-only">Daftar Tanggapan Survei Tamu</h2>
    @if($feedbacks->isEmpty())
        @if(request()->filled('rating'))
            <x-ui.empty-state
                kind="filtered"
                title="Tidak ada tanggapan dengan rating ini"
                description="Belum ada tamu yang memberi penilaian tersebut. Coba pilih rating lain atau tampilkan semua tanggapan."
            />
        @else
            <x-ui.empty-state
                kind="empty"
                title="Belum ada tanggapan survei"
                description="Ulasan dari tamu yang telah selesai berkunjung akan ditampilkan di sini."
            />
        @endif
    @else
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="divide-y divide-slate-100">
                @foreach($feedbacks as $fb)
                    <div class="p-5 sm:p-6 space-y-3 hover:bg-slate-50/50 transition">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-700 font-bold flex items-center justify-center text-xs">
                                    {{ strtoupper(substr($fb->masked_visitor_name, 0, 2)) }}
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-slate-900">{{ $fb->masked_visitor_name }}</h3>
                                    <p class="text-xs text-slate-500">
                                        {{ $fb->visit?->origin_organization }} &bull; Tujuan: <strong class="text-slate-700">{{ $fb->visit?->department_name_snapshot }}</strong>
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <div class="flex items-center gap-1 text-warning-400" aria-hidden="true">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg aria-hidden="true" class="w-4 h-4 {{ $i <= $fb->rating ? 'text-warning-400 fill-current' : 'text-slate-200 fill-current' }}" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    @endfor
                                </div>
                                <span class="sr-only">Penilaian {{ $fb->rating }} dari 5 bintang.</span>
                                <time
                                    class="text-xs text-slate-600"
                                    @if($fb->submitted_at) datetime="{{ $fb->submitted_at->toIso8601String() }}" title="{{ $fb->submitted_at->timezone($tz)->format('d M Y, H:i') }} WIB" @endif
                                >
                                    {{ $fb->submitted_at ? $fb->submitted_at->diffForHumans() : '—' }}
                                </time>
                            </div>
                        </div>

                        @if($fb->feedback_text)
                            <p class="text-xs sm:text-sm text-slate-700 bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                                &ldquo;{{ $fb->feedback_text }}&rdquo;
                            </p>
                        @endif

                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 pt-2 border-t border-slate-100/80 text-xs">
                            <span class="text-slate-500 font-mono">
                                Ref: {{ $fb->visit?->public_id }}
                            </span>
                            @if($fb->visit)
                                <a
                                    href="{{ route('admin.visits.show', $fb->visit) }}"
                                    class="inline-flex items-center min-h-9 px-2 rounded-lg text-primary-600 hover:text-primary-800 font-semibold focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-500"
                                >
                                    Detail kunjungan &rarr;
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination Links -->
            @if($feedbacks->hasPages())
                <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $feedbacks->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
