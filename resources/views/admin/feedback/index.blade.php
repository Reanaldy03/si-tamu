@extends('layouts.admin')

@section('title', 'Survei & Indeks Kepuasan Layanan (IKM)')

@section('page-title', 'Survei Kepuasan (IKM)')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 sm:p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2.5">
                <svg class="w-6 h-6 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
                <span>Indeks Kepuasan Layanan (IKM)</span>
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Laporan evaluasi ulasan dan saran masyarakat terhadap pelayanan publik di Sekretariat DPRD Kota Binjai.
            </p>
        </div>
    </div>

    <!-- IKM Metrics & Star Breakdown Card -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
        <!-- Average Score Box (4 cols) -->
        <div class="md:col-span-4 bg-white rounded-2xl border border-slate-200 shadow-xs p-6 flex flex-col items-center justify-center text-center space-y-3">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Skor Rata-Rata IKM</span>
            <div class="text-5xl font-black tracking-tight text-slate-900">
                {{ number_format($avgRating, 1) }}
                <span class="text-base font-medium text-slate-400">/ 5.0</span>
            </div>

            <div class="flex items-center gap-1 text-amber-400">
                @for($i = 1; $i <= 5; $i++)
                    <svg class="w-6 h-6 {{ $i <= round($avgRating) ? 'text-amber-400 fill-current' : 'text-slate-200 fill-current' }}" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                @endfor
            </div>

            <div class="pt-1">
                @if($avgRating >= 4.5)
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">Mutu Pelayanan: Sangat Baik (A)</span>
                @elseif($avgRating >= 3.5)
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-sky-100 text-sky-800">Mutu Pelayanan: Baik (B)</span>
                @elseif($avgRating >= 2.5)
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">Mutu Pelayanan: Cukup (C)</span>
                @elseif($avgRating > 0)
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800">Mutu Pelayanan: Perlu Perbaikan (D)</span>
                @else
                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600">Belum Ada Responden</span>
                @endif
            </div>

            <p class="text-xs text-slate-500 pt-1">
                Berdasarkan <strong>{{ number_format($totalCount) }}</strong> responden tamu.
            </p>
        </div>

        <!-- Star Breakdown Progress Bars (8 cols) -->
        <div class="md:col-span-8 bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-3">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 block mb-2">Distribusi Penilaian Bintang</span>

            @foreach($distribution as $star => $data)
                <div class="flex items-center gap-3 text-xs">
                    <span class="w-16 font-semibold text-slate-700 flex items-center gap-1">
                        {{ $star }}
                        <svg class="w-3.5 h-3.5 text-amber-400 fill-current" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    </span>

                    <div class="flex-1 h-3 bg-slate-100 rounded-full overflow-hidden">
                        <div 
                            class="h-full bg-amber-400 rounded-full transition-all duration-500" 
                            style="width: {{ $data['percentage'] }}%"
                        ></div>
                    </div>

                    <span class="w-20 text-right text-slate-500 font-medium">
                        {{ $data['count'] }} ({{ $data['percentage'] }}%)
                    </span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Filter Pills -->
    <div class="flex flex-wrap items-center gap-2">
        <a 
            href="{{ route('admin.feedback.index') }}" 
            class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition {{ !request()->filled('rating') ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50' }}"
        >
            Semua Rating
        </a>
        @for($r = 5; $r >= 1; $r--)
            <a 
                href="{{ route('admin.feedback.index', ['rating' => $r]) }}" 
                class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition flex items-center gap-1 {{ request('rating') == $r ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50' }}"
            >
                <span>{{ $r }}</span>
                <svg class="w-3.5 h-3.5 {{ request('rating') == $r ? 'text-amber-300' : 'text-amber-400' }} fill-current" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
            </a>
        @endfor
    </div>

    <!-- Feedback List -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        @if($feedbacks->isEmpty())
            <div class="p-12 text-center text-slate-500 space-y-2">
                <svg class="w-12 h-12 text-slate-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                <p class="text-sm font-semibold text-slate-700">Belum ada tanggapan survei</p>
                <p class="text-xs text-slate-400">Ulasan dari tamu yang telah selesai berkunjung akan ditampilkan di sini.</p>
            </div>
        @else
            <div class="divide-y divide-slate-100">
                @foreach($feedbacks as $fb)
                    <div class="p-5 sm:p-6 space-y-3 hover:bg-slate-50/50 transition">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-700 font-bold flex items-center justify-center text-xs">
                                    {{ strtoupper(substr($fb->visit?->visitor_name ?? 'T', 0, 2)) }}
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900">{{ $fb->visit?->visitor_name ?? 'Tamu Anonim' }}</h4>
                                    <p class="text-xs text-slate-500">
                                        {{ $fb->visit?->origin_organization }} &bull; Tujuan: <strong class="text-slate-700">{{ $fb->visit?->department_name_snapshot }}</strong>
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <div class="flex items-center gap-1 text-amber-400">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="w-4 h-4 {{ $i <= $fb->rating ? 'text-amber-400 fill-current' : 'text-slate-200 fill-current' }}" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    @endfor
                                </div>
                                <span class="text-xs text-slate-400">
                                    {{ $fb->submitted_at->format('d M Y, H:i') }}
                                </span>
                            </div>
                        </div>

                        @if($fb->feedback_text)
                            <p class="text-xs sm:text-sm text-slate-700 bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                                &ldquo;{{ $fb->feedback_text }}&rdquo;
                            </p>
                        @endif

                        <div class="flex items-center justify-between text-xs pt-1">
                            <span class="text-slate-400 font-mono">
                                Ref: {{ $fb->visit?->public_id }}
                            </span>
                            @if($fb->visit)
                                <a 
                                    href="{{ route('admin.visits.show', $fb->visit) }}" 
                                    class="text-indigo-600 hover:text-indigo-800 font-semibold"
                                >
                                    Detail Kunjungan &rarr;
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
        @endif
    </div>
</div>
@endsection
