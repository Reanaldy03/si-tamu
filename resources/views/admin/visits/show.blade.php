@extends('layouts.admin')

@section('title', 'Detail Kunjungan — ' . $visit->visitor_name . ' — SI-TAMU')
@section('header_title', 'Detail Kunjungan')

@section('content')
<div class="space-y-6" x-data="{ cancelModalOpen: false, qrSurveyModalOpen: false, lastFocused: null }">
    <!-- Navigation Back Link & Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a href="{{ route('admin.visits.index') }}" class="inline-flex items-center gap-1 min-h-9 px-2 -ml-2 mb-2 rounded-lg text-xs font-semibold text-primary-600 hover:text-primary-800 transition focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-500">
                <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali ke Data Kunjungan</span>
            </a>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">{{ $visit->visitor_name }}</h1>
                <x-visits.status-badge :status="$visit->current_status" />
            </div>
            <p class="text-xs text-slate-600 mt-1 font-mono">
                ID Kunjungan: <span class="text-slate-700 font-semibold">{{ $visit->public_id }}</span> &bull; 
                Terdaftar: {{ $visit->registered_at->timezone($tz)->format('d M Y') }}
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
                            @click="lastFocused = $el; cancelModalOpen = true"
                            class="inline-flex items-center min-h-11 px-3.5 py-2.5 rounded-xl border border-danger-200 bg-danger-50 hover:bg-danger-100 text-danger-700 text-xs font-semibold shadow-xs transition cursor-pointer focus:outline-hidden focus-visible:ring-2 focus-visible:ring-danger-500"
                        >
                            Batalkan Kunjungan
                        </button>
                    @else
                        <!-- Primary Transition Buttons -->
                        <form
                            action="{{ route('admin.visits.status.update', $visit) }}"
                            method="POST"
                            x-data="{ submitting: false }"
                            @submit="submitting = true"
                        >
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="target_status" value="{{ $nextStatus->value }}">

                            <x-ui.button type="submit" variant="primary" loading="submitting" loading-label="Menyimpan…">
                                @if ($nextStatus === \App\Domain\Visits\Enums\VisitStatus::ARRIVED)
                                    <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span>Tandai Tiba</span>
                                @elseif ($nextStatus === \App\Domain\Visits\Enums\VisitStatus::IN_SERVICE)
                                    <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    <span>Mulai Dilayani</span>
                                @elseif ($nextStatus === \App\Domain\Visits\Enums\VisitStatus::COMPLETED)
                                    <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>Selesaikan Kunjungan</span>
                                @else
                                    <span>Ubah ke {{ $nextStatus->label() }}</span>
                                @endif
                            </x-ui.button>

                            <span class="sr-only" role="status" aria-live="polite" x-text="submitting ? 'Menyimpan perubahan status…' : ''"></span>
                        </form>
                    @endif
                @endforeach

                @if ($visit->current_status->isTerminal())
                    <span class="inline-flex items-center gap-1.5 min-h-11 text-xs text-slate-600 font-medium px-3 py-1.5 rounded-lg bg-slate-100 border border-slate-200">
                        <svg aria-hidden="true" class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Kunjungan Telah Selesai (Status Final)</span>
                    </span>

                    @if ($visit->current_status === \App\Domain\Visits\Enums\VisitStatus::COMPLETED)
                        @if (! $visit->feedback)
                            <button
                                type="button"
                                @click="lastFocused = $el; qrSurveyModalOpen = true"
                                class="min-h-11 px-3.5 py-2.5 rounded-xl bg-warning-50 hover:bg-warning-100 border border-warning-200 text-warning-800 text-xs font-semibold shadow-xs transition cursor-pointer flex items-center gap-1.5 focus:outline-hidden focus-visible:ring-2 focus-visible:ring-warning-500"
                                title="Tampilkan QR Code Survei Kepuasan Tamu"
                            >
                                <svg aria-hidden="true" class="w-4 h-4 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                </svg>
                                <span>Tampilkan QR Survei</span>
                            </button>

                            <a 
                                href="{{ route('public.feedback.create', ['publicId' => $visit->public_id]) }}" 
                                target="_blank"
                                class="min-h-11 px-3.5 py-2.5 rounded-xl bg-primary-50 hover:bg-primary-100 border border-primary-200 text-primary-700 text-xs font-semibold shadow-xs transition cursor-pointer flex items-center gap-1.5 focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-500"
                                title="Buka form survei di tab baru / tablet kiosk"
                            >
                                <svg aria-hidden="true" class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                                <span>Buka Layar Survei</span>
                            </a>
                        @else
                            <span class="inline-flex items-center gap-1.5 text-xs text-success-700 font-semibold px-3 py-1.5 rounded-lg bg-success-50 border border-success-200">
                                <svg aria-hidden="true" class="w-3.5 h-3.5 text-success-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                                <span>Survei IKM Terisi ({{ $visit->feedback->rating }}/5)</span>
                            </span>
                        @endif
                    @endif
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
                    <svg aria-hidden="true" class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <h2 class="text-sm font-bold text-slate-900">Informasi Tamu / Pengunjung</h2>
                </div>

                <div class="p-5 sm:p-6 grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-6 text-sm">
                    <div>
                        <span class="text-xs text-slate-500 block font-medium">Nama Lengkap</span>
                        <span class="text-slate-900 font-semibold text-base">{{ $visit->visitor_name }}</span>
                    </div>

                    <div>
                        <span class="text-xs text-slate-500 block font-medium">Asal Instansi / Organisasi</span>
                        <span class="text-slate-900 font-semibold">{{ $visit->origin_organization }}</span>
                    </div>

                    <div>
                        <span class="text-xs text-slate-500 block font-medium">Nomor Kontak / WhatsApp</span>
                        <span class="text-slate-900 font-medium font-mono">
                            {{ $visit->contact_phone ?: '—' }}
                        </span>
                    </div>

                    <div>
                        <span class="text-xs text-slate-500 block font-medium">Alamat Email</span>
                        <span class="text-slate-900 font-medium">
                            {{ $visit->visitor_email ?: '—' }}
                        </span>
                    </div>

                    <div>
                        <span class="text-xs text-slate-500 block font-medium">Jumlah Rombongan</span>
                        <span class="text-slate-900 font-semibold">{{ $visit->party_size }} Orang</span>
                    </div>

                    <div>
                        <span class="text-xs text-slate-500 block font-medium">Kanal Registrasi</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200 mt-0.5">
                            {{ $visit->registration_source->value === 'PUBLIC' ? 'Tamu Mandiri (Kiosk/Web)' : 'Petugas Front-Office' }}
                        </span>
                    </div>
                </div>
            </div>


            @if ($visit->feedback)
                <!-- Card 3: Ulasan & Penilaian Tamu (IKM) -->
                <div class="bg-white rounded-2xl border border-warning-200/80 shadow-xs overflow-hidden">
                    <div class="p-5 border-b border-warning-100 bg-gradient-to-r from-warning-50/70 to-warning-50/40 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-warning-500 text-white flex items-center justify-center shadow-xs">
                                <svg aria-hidden="true" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-sm font-bold text-slate-900">Ulasan & Penilaian Tamu (IKM)</h2>
                                <p class="text-[11px] text-slate-500">Hasil survei kepuasan layanan tamu</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-warning-100 text-warning-800 border border-warning-200">
                            {{ $visit->feedback->ratingLabel() }}
                        </span>
                    </div>

                    <div class="p-5 sm:p-6 space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-1">
                                @for ($i = 1; $i <= 5; $i++)
                                    <svg aria-hidden="true" class="w-6 h-6 {{ $i <= $visit->feedback->rating ? 'text-warning-400 fill-current' : 'text-slate-200' }}" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                @endfor
                            </div>
                            <span class="text-lg font-bold text-slate-900">{{ $visit->feedback->rating }} <span class="text-xs text-slate-500 font-normal">/ 5 Bintang</span></span>
                        </div>

                        @if ($visit->feedback->feedback_text)
                            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 text-sm text-slate-700 italic">
                                &ldquo;{{ $visit->feedback->feedback_text }}&rdquo;
                            </div>
                        @else
                            <p class="text-xs text-slate-500 italic">Tidak ada ulasan tertulis yang disertakan.</p>
                        @endif

                        <div class="flex items-center justify-between text-xs text-slate-500 pt-2 border-t border-slate-100">
                            <span>Diserahkan: {{ $visit->feedback->submitted_at ? $visit->feedback->submitted_at->timezone($tz)->format('d M Y, H:i') : '—' }} WIB</span>
                            <span>IP: <code class="font-mono text-slate-600">{{ $visit->feedback->ip_address ?: '—' }}</code></span>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Right 1 Col: Status History Timeline -->
        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                    <h2 class="text-sm font-bold text-slate-900">Riwayat Status</h2>
                    <span class="text-[11px] text-slate-500 font-medium">Timeline</span>
                </div>
                <div class="p-5 sm:p-6">
                    <div class="relative ml-3">
                        @foreach ($visit->statusHistories as $history)
                            <div class="relative pl-6 pb-6 last:pb-0 before:absolute before:left-[-1px] before:top-4 before:bottom-0 before:w-0.5 before:bg-primary-200 last:before:hidden">
                                <!-- Dot indicator -->
                                <div class="absolute -left-[9px] top-0.5 w-4 h-4 rounded-full bg-white border-2 border-primary-600 flex items-center justify-center">
                                    <div class="w-1.5 h-1.5 rounded-full bg-primary-600"></div>
                                </div>

                                <div class="space-y-1">
                                    <div class="flex items-center justify-between gap-2">
                                        <x-visits.status-badge :status="$history->to_status" />
                                        <span class="text-[11px] text-slate-500 font-medium">
                                            {{ $history->changed_at->timezone($tz)->format('H:i') }}
                                        </span>
                                    </div>

                                    <p class="text-xs text-slate-700 font-medium">
                                        {{ $history->reason ?: 'Perubahan status' }}
                                    </p>

                                    <p class="text-[11px] text-slate-500">
                                        Oleh: <span class="text-slate-600 font-semibold">{{ $history->changedBy?->name ?? 'Sistem / Tamu' }}</span>
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Card 2: Detail Kunjungan (Tujuan & Keperluan) -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex items-center gap-2">
                    <svg aria-hidden="true" class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <h2 class="text-sm font-bold text-slate-900">Tujuan & Keperluan Kunjungan</h2>
                </div>

                <div class="p-5 sm:p-6 space-y-4 text-sm">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-6">
                        <div>
                            <span class="text-xs text-slate-500 block font-medium">Tanggal Kunjungan</span>
                            <span class="text-slate-900 font-semibold text-base">
                                {{ $visit->visit_date?->format('d M Y') ?? '—' }}
                            </span>
                        </div>

                        <div>
                            <span class="text-xs text-slate-500 block font-medium">Nomor Surat</span>
                            <span class="text-slate-900 font-medium font-mono">
                                {{ $visit->letter_number ?: '—' }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <span class="text-xs text-slate-500 block font-medium">Unit / Bidang Tujuan</span>
                        <span class="text-slate-900 font-semibold text-base">
                            {{ $visit->department_name_snapshot }}
                        </span>
                    </div>

                    <div>
                        <span class="text-xs text-slate-500 block font-medium">Keperluan Kunjungan</span>
                        <p class="text-slate-800 bg-slate-50 p-3.5 rounded-xl border border-slate-200/80 text-sm whitespace-pre-line leading-relaxed mt-1">{{ $visit->purpose }}</p>
                    </div>

                    @if ($visit->notes)
                        <div>
                            <span class="text-xs text-slate-500 block font-medium">Catatan Tambahan</span>
                            <p class="text-slate-700 bg-slate-50 p-3 rounded-xl border border-slate-200/80 text-xs mt-1">
                                {{ $visit->notes }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Cancellation Modal Dialog (Alpine.js) -->
    <div
        x-show="cancelModalOpen"
        x-trap.noscroll="cancelModalOpen"
        role="dialog"
        aria-modal="true"
        aria-labelledby="cancel-modal-title"
        @keydown.escape.window="cancelModalOpen = false; $nextTick(() => lastFocused?.focus())"
        x-effect="cancelModalOpen && $nextTick(() => $refs.cancelPanel.querySelector('textarea')?.focus())"
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
            x-ref="cancelPanel"
            tabindex="-1"
            class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200 space-y-4 focus:outline-hidden"
            @click.outside="cancelModalOpen = false; $nextTick(() => lastFocused?.focus())"
        >
            <div class="w-11 h-11 rounded-full bg-danger-100 text-danger-600 flex items-center justify-center mx-auto">
                <svg aria-hidden="true" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>

            <div class="text-center space-y-1">
                <h3 id="cancel-modal-title" class="text-lg font-bold text-slate-900">Batalkan Kunjungan?</h3>
                <p class="text-xs text-slate-500">
                    Status akan diubah menjadi <strong>Dibatalkan</strong> dan dicatat secara permanen pada log audit.
                </p>
            </div>

            <form
                action="{{ route('admin.visits.status.update', $visit) }}"
                method="POST"
                class="space-y-4"
                x-data="{ submitting: false }"
                @submit="submitting = true"
            >
                @csrf
                @method('PATCH')
                <input type="hidden" name="target_status" value="{{ \App\Domain\Visits\Enums\VisitStatus::CANCELLED->value }}">

                <div>
                    <label for="reason" class="block text-xs font-semibold text-slate-700 mb-1">
                        Alasan Pembatalan <span class="text-danger-600">*</span>
                    </label>
                    <textarea
                        name="reason"
                        id="reason"
                        rows="3"
                        required
                        placeholder="Contoh: Tamu membatalkan kunjungan / Pejabat tidak berada di tempat"
                        class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs placeholder:text-slate-500 focus:outline-hidden focus:ring-2 focus:ring-danger-500"
                    ></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button
                        type="button"
                        @click="cancelModalOpen = false; $nextTick(() => lastFocused?.focus())"
                        class="min-h-11 px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-50 transition cursor-pointer focus:outline-hidden focus-visible:ring-2 focus-visible:ring-slate-400"
                    >
                        Batal
                    </button>
                    <x-ui.button type="submit" variant="danger" loading="submitting" loading-label="Menyimpan…">
                        Konfirmasi Pembatalan
                    </x-ui.button>
                </div>

                <span class="sr-only" role="status" aria-live="polite" x-text="submitting ? 'Menyimpan perubahan status…' : ''"></span>
            </form>
        </div>
    </div>

    <!-- QR Survey Modal Dialog (Alpine.js) -->
    @if ($visit->current_status === \App\Domain\Visits\Enums\VisitStatus::COMPLETED)
        <div
            x-show="qrSurveyModalOpen"
            x-trap.noscroll="qrSurveyModalOpen"
            role="dialog"
            aria-modal="true"
            aria-labelledby="qr-modal-title"
            @keydown.escape.window="qrSurveyModalOpen = false; $nextTick(() => lastFocused?.focus())"
            x-effect="qrSurveyModalOpen && $nextTick(() => $refs.qrClose.focus())"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            x-cloak
        >
            <div
                x-ref="qrPanel"
                tabindex="-1"
                class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-slate-200 text-center space-y-4 focus:outline-hidden"
                @click.outside="qrSurveyModalOpen = false; $nextTick(() => lastFocused?.focus())"
            >
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 id="qr-modal-title" class="text-base font-bold text-slate-900">QR Code Survei Kepuasan (IKM)</h3>
                    <button
                        type="button"
                        x-ref="qrClose"
                        aria-label="Tutup dialog QR survei"
                        @click="qrSurveyModalOpen = false; $nextTick(() => lastFocused?.focus())"
                        class="inline-flex items-center justify-center w-11 h-11 rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-100 transition cursor-pointer focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-500"
                    >
                        <svg aria-hidden="true" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <p class="text-xs text-slate-500">
                    Arahkan kamera smartphone tamu ke QR Code di bawah untuk mengisi Survei Kepuasan Layanan Sekretariat DPRD Kota Binjai.
                </p>

                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 flex items-center justify-center mx-auto shadow-inner">
                    {!! app(\App\Application\Visits\Services\VisitQrTokenService::class)->renderSvg(route('public.feedback.create', ['publicId' => $visit->public_id]), 220) !!}
                </div>

                <div class="text-[11px] font-mono text-slate-500 break-all bg-slate-100 px-3 py-1.5 rounded-lg border border-slate-200">
                    {{ route('public.feedback.create', ['publicId' => $visit->public_id]) }}
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <a 
                        href="{{ route('public.feedback.create', ['publicId' => $visit->public_id]) }}" 
                        target="_blank"
                        class="flex-1 min-h-11 px-4 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-xs font-semibold shadow-xs transition flex items-center justify-center gap-1.5 focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2"
                    >
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        <span>Buka Tab Baru</span>
                    </a>
                    <button
                        type="button"
                        @click="qrSurveyModalOpen = false; $nextTick(() => lastFocused?.focus())"
                        class="min-h-11 px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-50 transition cursor-pointer focus:outline-hidden focus-visible:ring-2 focus-visible:ring-slate-400"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
