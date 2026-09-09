@extends('layouts.admin')

@section('title', 'Detail Kunjungan — ' . $visit->visitor_name . ' — SI-TAMU')
@section('header_title', 'Detail Kunjungan')

@section('content')
<div class="space-y-6" x-data="{ cancelModalOpen: false }">
    <!-- Navigation Back Link & Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a href="{{ route('admin.visits.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali ke Data Kunjungan</span>
            </a>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">{{ $visit->visitor_name }}</h1>
                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold border {{ $visit->current_status->badgeClasses() }}">
                    {{ $visit->current_status->label() }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1 font-mono">
                ID Kunjungan: <span class="text-slate-700 font-semibold">{{ $visit->public_id }}</span> &bull; 
                Terdaftar: {{ $visit->registered_at->timezone($tz)->format('d M Y, H:i') }} WIB
            </p>
        </div>

        <!-- Dynamic Action Buttons according to VisitStatusTransitionRule -->
        <div class="flex items-center gap-2 flex-wrap">
            @can('updateStatus', $visit)
                @foreach ($allowedNextStatuses as $nextStatus)
                    @if ($nextStatus === \App\Domain\Visits\Enums\VisitStatus::CANCELLED)
                        <!-- Cancel Button opens confirmation modal -->
                        <button 
                            type="button" 
                            @click="cancelModalOpen = true"
                            class="px-3.5 py-2 rounded-xl border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-semibold shadow-xs transition cursor-pointer"
                        >
                            Batalkan Kunjungan
                        </button>
                    @else
                        <!-- Primary Transition Buttons -->
                        <form action="{{ route('admin.visits.status.update', $visit) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="target_status" value="{{ $nextStatus->value }}">
                            
                            <button 
                                type="submit" 
                                class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-xs font-semibold shadow-xs transition cursor-pointer flex items-center gap-1.5"
                            >
                                @if ($nextStatus === \App\Domain\Visits\Enums\VisitStatus::ARRIVED)
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span>Tandai Tiba</span>
                                @elseif ($nextStatus === \App\Domain\Visits\Enums\VisitStatus::IN_SERVICE)
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    <span>Mulai Dilayani</span>
                                @elseif ($nextStatus === \App\Domain\Visits\Enums\VisitStatus::COMPLETED)
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>Selesaikan Kunjungan</span>
                                @else
                                    <span>Ubah ke {{ $nextStatus->label() }}</span>
                                @endif
                            </button>
                        </form>
                    @endif
                @endforeach

                @if ($visit->current_status->isTerminal())
                    <span class="inline-flex items-center gap-1.5 text-xs text-slate-500 font-medium px-3 py-1.5 rounded-lg bg-slate-100 border border-slate-200">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Kunjungan Telah Selesai (Status Final)</span>
                    </span>
                @endif
            @endcan
        </div>
    </div>

    <!-- Main Detail Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left 2 Cols: Guest & Visit Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Card 1: Informasi Tamu -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <h2 class="text-sm font-bold text-slate-900">Informasi Tamu / Pengunjung</h2>
                </div>

                <div class="p-5 sm:p-6 grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-6 text-sm">
                    <div>
                        <span class="text-xs text-slate-400 block font-medium">Nama Lengkap</span>
                        <span class="text-slate-900 font-semibold text-base">{{ $visit->visitor_name }}</span>
                    </div>

                    <div>
                        <span class="text-xs text-slate-400 block font-medium">Asal Instansi / Organisasi</span>
                        <span class="text-slate-900 font-semibold">{{ $visit->origin_organization }}</span>
                    </div>

                    <div>
                        <span class="text-xs text-slate-400 block font-medium">Nomor Kontak / WhatsApp</span>
                        <span class="text-slate-900 font-medium font-mono">
                            {{ $visit->contact_phone ?: '—' }}
                        </span>
                    </div>

                    <div>
                        <span class="text-xs text-slate-400 block font-medium">Alamat Email</span>
                        <span class="text-slate-900 font-medium">
                            {{ $visit->visitor_email ?: '—' }}
                        </span>
                    </div>

                    <div>
                        <span class="text-xs text-slate-400 block font-medium">Jumlah Rombongan</span>
                        <span class="text-slate-900 font-semibold">{{ $visit->party_size }} Orang</span>
                    </div>

                    <div>
                        <span class="text-xs text-slate-400 block font-medium">Kanal Registrasi</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200 mt-0.5">
                            {{ $visit->registration_source->value === 'PUBLIC' ? 'Tamu Mandiri (Kiosk/Web)' : 'Petugas Front-Office' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Card 2: Detail Kunjungan -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <h2 class="text-sm font-bold text-slate-900">Tujuan & Keperluan Kunjungan</h2>
                </div>

                <div class="p-5 sm:p-6 space-y-4 text-sm">
                    <div>
                        <span class="text-xs text-slate-400 block font-medium">Unit / Bidang Tujuan</span>
                        <span class="text-slate-900 font-semibold text-base">
                            {{ $visit->department_name_snapshot }}
                        </span>
                    </div>

                    <div>
                        <span class="text-xs text-slate-400 block font-medium">Keperluan Kunjungan</span>
                        <p class="text-slate-800 bg-slate-50 p-3.5 rounded-xl border border-slate-200/80 text-sm whitespace-pre-line leading-relaxed mt-1">
                            {{ $visit->purpose }}
                        </p>
                    </div>

                    @if ($visit->notes)
                        <div>
                            <span class="text-xs text-slate-400 block font-medium">Catatan Tambahan</span>
                            <p class="text-slate-700 bg-slate-50 p-3 rounded-xl border border-slate-200/80 text-xs mt-1">
                                {{ $visit->notes }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right 1 Col: Status History Timeline -->
        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                    <h2 class="text-sm font-bold text-slate-900">Riwayat Status</h2>
                    <span class="text-[11px] text-slate-500 font-medium">Timeline</span>
                </div>

                <div class="p-5 sm:p-6">
                    <div class="relative border-l-2 border-indigo-200 ml-3 space-y-6">
                        @foreach ($visit->statusHistories as $history)
                            <div class="relative pl-6">
                                <!-- Dot indicator -->
                                <div class="absolute -left-[9px] top-0.5 w-4 h-4 rounded-full bg-white border-2 border-indigo-600 flex items-center justify-center">
                                    <div class="w-1.5 h-1.5 rounded-full bg-indigo-600"></div>
                                </div>

                                <div class="space-y-1">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold border {{ $history->to_status->badgeClasses() }}">
                                            {{ $history->to_status->label() }}
                                        </span>
                                        <span class="text-[11px] text-slate-400 font-medium">
                                            {{ $history->changed_at->timezone($tz)->format('H:i') }}
                                        </span>
                                    </div>

                                    <p class="text-xs text-slate-700 font-medium">
                                        {{ $history->reason ?: 'Perubahan status' }}
                                    </p>

                                    <p class="text-[11px] text-slate-400">
                                        Oleh: <span class="text-slate-600 font-semibold">{{ $history->changedBy?->name ?? 'Sistem / Tamu' }}</span>
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cancellation Modal Dialog (Alpine.js) -->
    <div 
        x-show="cancelModalOpen" 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        x-cloak
    >
        <div 
            class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200 space-y-4"
            @click.outside="cancelModalOpen = false"
        >
            <div class="w-11 h-11 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>

            <div class="text-center space-y-1">
                <h3 class="text-lg font-bold text-slate-900">Batalkan Kunjungan?</h3>
                <p class="text-xs text-slate-500">
                    Status akan diubah menjadi <strong>Dibatalkan</strong> dan dicatat secara permanen pada log audit.
                </p>
            </div>

            <form action="{{ route('admin.visits.status.update', $visit) }}" method="POST" class="space-y-4">
                @csrf
                @method('PATCH')
                <input type="hidden" name="target_status" value="{{ \App\Domain\Visits\Enums\VisitStatus::CANCELLED->value }}">

                <div>
                    <label for="reason" class="block text-xs font-semibold text-slate-700 mb-1">
                        Alasan Pembatalan <span class="text-rose-500">*</span>
                    </label>
                    <textarea 
                        name="reason" 
                        id="reason" 
                        rows="3" 
                        required 
                        placeholder="Contoh: Tamu membatalkan kunjungan / Pejabat tidak berada di tempat" 
                        class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:outline-none focus:ring-2 focus:ring-rose-500"
                    ></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button 
                        type="button" 
                        @click="cancelModalOpen = false" 
                        class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-50 transition"
                    >
                        Batal
                    </button>
                    <button 
                        type="submit" 
                        class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-xs transition"
                    >
                        Konfirmasi Pembatalan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
