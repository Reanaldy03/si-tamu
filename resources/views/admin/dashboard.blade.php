@extends('layouts.admin')

@section('title', 'Ringkasan Hari Ini — SI-TAMU')
@section('header_title', 'Ringkasan Hari Ini')

@section('content')
<div class="space-y-6">
    {{-- 1. Page identity + the two actions an operator reaches for most. --}}
    <x-layout.page-header
        title="Ringkasan Hari Ini"
        subtitle="Aktivitas buku tamu digital Sekretariat DPRD Kota Binjai periode {{ now($tz)->format('d F Y') }} ({{ $tz }})."
    >
        <x-slot:actions>
            @can('checkIn', \App\Models\Visit::class)
                <x-ui.button :href="route('admin.checkin.scan')" variant="primary">
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                    </x-slot:icon>
                    Pindai QR / Check-In
                </x-ui.button>
            @endcan
            <x-ui.button :href="route('admin.visits.index')" variant="secondary">
                <x-slot:icon>
                    <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </x-slot:icon>
                Kelola Semua Kunjungan
            </x-ui.button>
        </x-slot:actions>
    </x-layout.page-header>

    {{--
        2. Worklist. This is the first thing on the page on purpose: the job is
        "someone is standing in front of me", not "read five counters".
    --}}
    <x-ui.card
        title="Perlu Tindakan"
        description="Tamu yang sudah berada di lobi dan menunggu keputusan petugas — diurutkan dari yang paling lama menunggu."
        :flush="true"
    >
        <x-slot:header>
            <x-ui.badge :tone="$waitingCount > 0 ? 'warning' : 'neutral'" :dot="true">
                @if ($waitingCount > 0)
                    {{ number_format($waitingCount) }} tamu menunggu
                @else
                    Antrian bersih
                @endif
            </x-ui.badge>
        </x-slot:header>

        @if ($waitingVisits->isEmpty())
            <div class="p-5">
                <x-ui.empty-state
                    title="Tidak ada tamu menunggu tindakan. Antrian bersih."
                    description="Tamu akan muncul di sini begitu petugas memindai QR check-in di meja lobi."
                    kind="empty"
                    :heading-level="3"
                />
            </div>
        @else
            <ul role="list" class="divide-y divide-slate-100">
                @foreach ($waitingVisits as $visit)
                    @php
                        $waitingSince = $visit->checked_in_at ?? $visit->registered_at;
                        $waitingFor = $waitingSince->diffInMinutes(now()) < 1
                            ? 'kurang dari 1 menit'
                            : $waitingSince
                                ->locale('id')
                                ->diffForHumans(now(), \Carbon\CarbonInterface::DIFF_ABSOLUTE, false, 2);
                    @endphp
                    <li
                        x-data="{ submitting: false }"
                        class="p-4 sm:p-5 flex flex-col lg:flex-row lg:items-center gap-4 hover:bg-slate-50/60 transition"
                    >
                        <div class="min-w-0 flex-1 space-y-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <a
                                    href="{{ route('admin.visits.show', $visit) }}"
                                    class="text-sm font-semibold text-slate-900 rounded hover:text-primary-700 hover:underline underline-offset-2 focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-500"
                                >{{ $visit->visitor_name }}</a>
                                <x-visits.status-badge :status="$visit->current_status" />
                            </div>

                            <dl class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <dt class="text-slate-500">Asal</dt>
                                    <dd class="font-medium text-slate-700 truncate">{{ $visit->origin_organization ?: 'Tidak dicatat' }}</dd>
                                </div>
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <dt class="text-slate-500">Tujuan</dt>
                                    <dd class="font-medium text-slate-700 truncate">{{ $visit->department_name_snapshot ?: 'Belum ditentukan' }}</dd>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <dt class="text-slate-500">Menunggu</dt>
                                    <dd class="font-medium text-slate-700">
                                        <time datetime="{{ $waitingSince->toIso8601String() }}" class="tabular-nums">{{ $waitingFor }}</time>
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 lg:shrink-0 lg:justify-end">
                            @can('updateStatus', $visit)
                                @foreach ($visit->current_status->allowedNextStatuses() as $nextStatus)
                                    @if ($nextStatus === \App\Domain\Visits\Enums\VisitStatus::CANCELLED)
                                        {{--
                                            Cancelling is irreversible and the
                                            status endpoint requires a reason, so
                                            it goes through the shared confirmation
                                            dialog (same as the visit detail page).

                                            The payload is interpolated with `{{ }}`
                                            on purpose: Blade never compiles `@js()`
                                            (or any directive) inside a component
                                            attribute value, so `@js()` there would
                                            render as literal text.
                                        --}}
                                        <x-ui.button
                                            type="button"
                                            variant="dangerSoft"
                                            size="sm"
                                            @click="$dispatch('confirm-action', {
                                                url: '{{ route('admin.visits.status.update', $visit) }}',
                                                method: 'PATCH',
                                                title: 'Batalkan kunjungan ini?',
                                                body: 'Status akan diubah menjadi Dibatalkan dan tercatat permanen di log audit. Alasan pembatalan wajib diisi.',
                                                confirm: 'Batalkan kunjungan',
                                                tone: 'danger',
                                                withReason: true,
                                                reasonRequired: true,
                                                trigger: $el,
                                            })"
                                        >Batalkan</x-ui.button>
                                    @else
                                        <form
                                            action="{{ route('admin.visits.status.update', $visit) }}"
                                            method="POST"
                                            class="inline-flex"
                                            @submit="submitting = true"
                                        >
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="target_status" value="{{ $nextStatus->value }}">
                                            <x-ui.button
                                                type="submit"
                                                :variant="$nextStatus === \App\Domain\Visits\Enums\VisitStatus::COMPLETED ? 'success' : 'primary'"
                                                size="sm"
                                                :loading="'submitting'"
                                                loading-label="Menyimpan…"
                                            >
                                                <x-slot:icon>
                                                    @if ($nextStatus === \App\Domain\Visits\Enums\VisitStatus::ARRIVED)
                                                        <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    @elseif ($nextStatus === \App\Domain\Visits\Enums\VisitStatus::IN_SERVICE)
                                                        <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                                    @elseif ($nextStatus === \App\Domain\Visits\Enums\VisitStatus::COMPLETED)
                                                        <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    @endif
                                                </x-slot:icon>
                                                @switch($nextStatus)
                                                    @case(\App\Domain\Visits\Enums\VisitStatus::ARRIVED)
                                                        Tandai Tiba
                                                        @break
                                                    @case(\App\Domain\Visits\Enums\VisitStatus::IN_SERVICE)
                                                        Mulai Dilayani
                                                        @break
                                                    @case(\App\Domain\Visits\Enums\VisitStatus::COMPLETED)
                                                        Selesaikan
                                                        @break
                                                    @default
                                                        Ubah ke {{ $nextStatus->label() }}
                                                @endswitch
                                            </x-ui.button>
                                        </form>
                                    @endif
                                @endforeach
                            @endcan

                            <x-ui.button :href="route('admin.visits.show', $visit)" variant="ghost" size="sm">Detail</x-ui.button>
                        </div>
                    </li>
                @endforeach
            </ul>

            @if ($waitingCount > $waitingVisits->count())
                <div class="px-5 py-3 border-t border-slate-100 bg-slate-50/70 flex flex-wrap items-center justify-between gap-2">
                    <p class="text-xs text-slate-500">
                        Menampilkan {{ $waitingVisits->count() }} tamu paling lama menunggu dari total {{ number_format($waitingCount) }}.
                    </p>
                    <a
                        href="{{ route('admin.visits.index') }}"
                        class="inline-flex items-center min-h-11 px-2 rounded-lg text-xs font-semibold text-primary-700 hover:text-primary-800 focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-500"
                    >Lihat semua antrian &rarr;</a>
                </div>
            @endif
        @endif
    </x-ui.card>

    {{-- 3. Counters — every figure here is scoped to today, and says so. --}}
    <section aria-labelledby="metrik-hari-ini" class="space-y-3">
        <div class="flex flex-wrap items-baseline justify-between gap-2">
            <h2 id="metrik-hari-ini" class="text-sm font-semibold text-slate-900">Metrik operasional</h2>
            <p class="text-xs text-slate-500">Periode: hari ini ({{ now($tz)->format('d F Y') }})</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <x-ui.card body-class="p-4">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-slate-600">Total</p>
                        <p class="text-2xl font-bold text-slate-900 mt-0.5 tabular-nums">{{ number_format($totalToday) }}</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">Seluruh kunjungan masuk hari ini</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center shrink-0">
                        <svg aria-hidden="true" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card body-class="p-4">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-info-700">Dilayani</p>
                        <p class="text-2xl font-bold text-slate-900 mt-0.5 tabular-nums">{{ number_format($inServiceCount) }}</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">Dalam proses unit/bagian</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-info-50 text-info-600 flex items-center justify-center shrink-0">
                        <svg aria-hidden="true" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card body-class="p-4">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-success-700">Selesai</p>
                        <p class="text-2xl font-bold text-slate-900 mt-0.5 tabular-nums">{{ number_format($completedCount) }}</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">Kunjungan tuntas hari ini</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-success-50 text-success-600 flex items-center justify-center shrink-0">
                        <svg aria-hidden="true" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </x-ui.card>

            <a
                href="{{ route('admin.feedback.index') }}"
                class="block rounded-2xl group focus:outline-hidden focus-visible:ring-2 focus-visible:ring-warning-500 focus-visible:ring-offset-2"
            >
                <x-ui.card body-class="p-4" class="h-full transition group-hover:border-warning-300">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-warning-700">Indeks IKM</p>
                            <p class="text-2xl font-bold text-slate-900 mt-0.5 tabular-nums flex items-baseline gap-1">
                                {{ $ikmAvg > 0 ? number_format($ikmAvg, 1) : '—' }}
                                <span class="text-xs font-normal text-slate-500">/ 5,0</span>
                            </p>
                            <p class="text-[11px] text-slate-500 mt-0.5">{{ number_format($ikmCount) }} responden sepanjang waktu</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-warning-50 text-warning-500 motion-reduce:transition-none transition group-hover:scale-105 motion-reduce:group-hover:scale-100 flex items-center justify-center shrink-0">
                            <svg aria-hidden="true" class="w-5 h-5 fill-current" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        </div>
                    </div>
                </x-ui.card>
            </a>
        </div>
    </section>

    {{--
        4. Analytics. Collapsed by default so the queue and the counters lead;
        every canvas, its id, its role and its aria-label are unchanged, and
        each chart ships a real text alternative for the numbers it encodes.
    --}}
    <section x-data="{ showAnalytics: false }" class="space-y-4" aria-labelledby="analitik-title">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="min-w-0">
                <h2 id="analitik-title" class="text-base font-semibold text-slate-900">Analitik dan Tren Kunjungan</h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Sebaran 30 hari terakhir, kecuali periode tren yang kamu pilih sendiri.
                </p>
            </div>
            <x-ui.button
                type="button"
                variant="secondary"
                @click="showAnalytics = ! showAnalytics"
                ::aria-expanded="showAnalytics"
                aria-controls="analytics-panel"
            >
                <x-slot:icon>
                    <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </x-slot:icon>
                <span x-text="showAnalytics ? 'Sembunyikan analitik' : 'Lihat analitik'">Lihat analitik</span>
            </x-ui.button>
        </div>

        <div id="analytics-panel" x-show="showAnalytics" x-cloak class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Chart 1: Trend Kunjungan (Mingguan / Bulanan / Tahunan / Tahun Ini) -->
                <div
                    x-data="{
                        currentPeriod: @js($defaultPeriod),
                        periods: @js($trendPeriods),
                        changePeriod(key) {
                            this.currentPeriod = key;
                            if (window.updateTrendChart) {
                                window.updateTrendChart(this.periods[key]);
                            }
                        }
                    }"
                    class="lg:col-span-7"
                >
                    <x-ui.card body-class="p-5 sm:p-6 space-y-4" class="h-full">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-sm font-semibold text-slate-900" x-text="periods[currentPeriod].title">
                                        {{ $trendPeriods[$defaultPeriod]['title'] }}
                                    </h3>
                                    <x-ui.badge tone="primary">
                                        <span x-text="periods[currentPeriod].total + ' tamu'">{{ $trendPeriods[$defaultPeriod]['total'] }} tamu</span>
                                    </x-ui.badge>
                                </div>
                                <p class="text-xs text-slate-500 mt-0.5" x-text="periods[currentPeriod].subtitle">
                                    {{ $trendPeriods[$defaultPeriod]['subtitle'] }}
                                </p>
                            </div>

                            <!-- Period selector: each option names its own window -->
                            <div class="inline-flex items-center p-1 bg-slate-100 rounded-xl border border-slate-200 self-start sm:self-auto shrink-0">
                                <template x-for="(p, key) in periods" :key="key">
                                    <button
                                        type="button"
                                        @click="changePeriod(key)"
                                        class="min-h-11 px-3 py-2 text-xs font-semibold rounded-lg transition duration-150 cursor-pointer focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-500"
                                        :aria-pressed="currentPeriod === key"
                                        :class="currentPeriod === key
                                            ? 'bg-white text-primary-700 shadow-xs font-bold border border-slate-200/80'
                                            : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/60'"
                                        :title="p.badge"
                                        x-text="p.name"
                                    ></button>
                                </template>
                            </div>
                        </div>

                        <div class="h-64 relative">
                            <canvas id="trendChart" role="img" aria-label="Grafik tren jumlah kunjungan per periode terpilih"></canvas>
                        </div>

                        <!-- Text alternative for the trend line -->
                        <details>
                            <summary class="inline-flex items-center gap-1.5 min-h-11 px-2 -ml-2 rounded-lg text-xs font-semibold text-primary-700 hover:text-primary-800 cursor-pointer list-none focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-500">
                                <svg aria-hidden="true" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                Lihat tabel data tren
                            </summary>
                            <div class="mt-2 overflow-x-auto" tabindex="0" role="region" aria-label="Tabel data tren kunjungan">
                                <table class="w-full text-left text-xs divide-y divide-slate-100">
                                    <caption class="sr-only">Jumlah kunjungan pada setiap titik waktu di periode tren yang sedang dipilih</caption>
                                    <thead class="text-slate-600">
                                        <tr>
                                            <th scope="col" class="px-2 py-1.5 font-semibold">Periode</th>
                                            <th scope="col" class="px-2 py-1.5 font-semibold text-right">Jumlah kunjungan</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <template x-for="(label, index) in periods[currentPeriod].labels" :key="index">
                                            <tr>
                                                <th scope="row" class="px-2 py-1.5 font-normal text-slate-600" x-text="label"></th>
                                                <td class="px-2 py-1.5 text-right font-medium text-slate-900 tabular-nums" x-text="periods[currentPeriod].values[index]"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </details>
                    </x-ui.card>
                </div>

                <!-- Chart 2: Proporsi Kategori Instansi -->
                <div class="lg:col-span-5">
                    <x-ui.card
                        title="Proporsi Asal Tamu"
                        description="Segmentasi instansi dalam 30 hari terakhir."
                        body-class="p-5 sm:p-6 space-y-3"
                        class="h-full"
                    >
                        <x-slot:header>
                            <x-ui.badge tone="neutral">30 hari terakhir</x-ui.badge>
                        </x-slot:header>

                        <div class="h-64 relative flex items-center justify-center">
                            <canvas id="orgChart" role="img" aria-label="Diagram proporsi asal instansi tamu dalam 30 hari terakhir"></canvas>
                        </div>

                        <!-- Text alternative for the donut -->
                        <details>
                            <summary class="inline-flex items-center gap-1.5 min-h-11 px-2 -ml-2 rounded-lg text-xs font-semibold text-primary-700 hover:text-primary-800 cursor-pointer list-none focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-500">
                                <svg aria-hidden="true" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                Lihat tabel data asal tamu
                            </summary>
                            <table class="mt-2 w-full text-left text-xs divide-y divide-slate-100">
                                <caption class="sr-only">Jumlah kunjungan per kategori asal instansi dalam 30 hari terakhir</caption>
                                <thead class="text-slate-600">
                                    <tr>
                                        <th scope="col" class="px-2 py-1.5 font-semibold">Kategori asal</th>
                                        <th scope="col" class="px-2 py-1.5 font-semibold text-right">Jumlah kunjungan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach ($orgLabels as $index => $orgLabel)
                                        <tr>
                                            <th scope="row" class="px-2 py-1.5 font-normal text-slate-600">{{ $orgLabel }}</th>
                                            <td class="px-2 py-1.5 text-right font-medium text-slate-900 tabular-nums">{{ number_format($orgValues[$index] ?? 0) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </details>
                    </x-ui.card>
                </div>
            </div>

            <!-- Chart 3: Distribusi Kunjungan per Bagian / Komisi DPRD -->
            @if (count($deptLabels) > 0)
                <x-ui.card
                    title="Distribusi Kunjungan per Bagian / Komisi DPRD"
                    description="Frekuensi audiensi dan penerimaan tamu per unit kerja, 30 hari terakhir."
                    body-class="p-5 sm:p-6 space-y-3"
                >
                    <x-slot:header>
                        <x-ui.badge tone="neutral">Top unit kerja</x-ui.badge>
                    </x-slot:header>

                    <div class="h-56 relative">
                        <canvas id="deptChart" role="img" aria-label="Diagram distribusi kunjungan per bagian atau komisi DPRD dalam 30 hari terakhir"></canvas>
                    </div>

                    <!-- Text alternative for the bars -->
                    <details>
                        <summary class="inline-flex items-center gap-1.5 min-h-11 px-2 -ml-2 rounded-lg text-xs font-semibold text-primary-700 hover:text-primary-800 cursor-pointer list-none focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-500">
                            <svg aria-hidden="true" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                            Lihat tabel data unit kerja
                        </summary>
                        <div class="mt-2 overflow-x-auto" tabindex="0" role="region" aria-label="Tabel data distribusi kunjungan per unit kerja">
                            <table class="w-full text-left text-xs divide-y divide-slate-100">
                                <caption class="sr-only">Jumlah kunjungan per bagian atau komisi DPRD dalam 30 hari terakhir</caption>
                                <thead class="text-slate-600">
                                    <tr>
                                        <th scope="col" class="px-2 py-1.5 font-semibold">Unit kerja</th>
                                        <th scope="col" class="px-2 py-1.5 font-semibold text-right">Jumlah kunjungan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach ($deptLabels as $index => $deptLabel)
                                        <tr>
                                            <th scope="row" class="px-2 py-1.5 font-normal text-slate-600">{{ $deptLabel }}</th>
                                            <td class="px-2 py-1.5 text-right font-medium text-slate-900 tabular-nums">{{ number_format($deptValues[$index] ?? 0) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </details>
                </x-ui.card>
            @endif
        </div>
    </section>

    {{-- 5. Latest visits — latest N across all days, so it is never empty while data exists. --}}
    <x-ui.card
        title="Kunjungan Terkini"
        description="10 kunjungan terbaru yang tercatat, tanpa batas hari."
        :flush="true"
    >
        <x-slot:header>
            @if ($latestVisits->isNotEmpty())
                <x-ui.button :href="route('admin.visits.index')" variant="ghost" size="sm">Lihat riwayat lengkap</x-ui.button>
            @endif
        </x-slot:header>

        @if ($latestVisits->isEmpty())
            <div class="p-5">
                <x-ui.empty-state
                    title="Belum ada kunjungan yang tercatat."
                    description="Kunjungan baru akan muncul di sini setelah tamu mendaftar di meja lobi Gedung DPRD Kota Binjai."
                    kind="empty"
                    :heading-level="3"
                />
            </div>
        @else
            <div class="overflow-x-auto" tabindex="0" role="region" aria-label="Tabel kunjungan terbaru">
                <table class="w-full xl:table-fixed text-left text-sm divide-y divide-slate-200">
                    <caption class="sr-only">Kunjungan terbaru beserta tanggal, tamu, instansi, unit tujuan, status, dan tautan detail</caption>
                    <thead class="bg-slate-50 text-[11px] font-semibold text-slate-600">
                        <tr>
                            <th scope="col" class="w-[13%] px-3 py-2.5">Tanggal</th>
                            <th scope="col" class="w-[20%] px-3 py-2.5">Nama tamu</th>
                            <th scope="col" class="hidden sm:table-cell sm:w-[19%] px-3 py-2.5">Instansi</th>
                            <th scope="col" class="hidden sm:table-cell sm:w-[19%] px-3 py-2.5">Unit tujuan</th>
                            <th scope="col" class="w-[18%] px-3 py-2.5">Status</th>
                            <th scope="col" class="w-[11%] px-3 py-2.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($latestVisits as $visit)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-3 py-2.5 text-xs text-slate-600">
                                    <div class="font-medium text-slate-900 tabular-nums">{{ $visit->visit_date?->format('d M Y') ?? '—' }}</div>
                                    <div class="text-[11px] text-slate-500 tabular-nums">Reg: {{ $visit->registered_at->timezone($tz)->format('d M Y') }}</div>
                                </td>
                                <td class="px-3 py-2.5">
                                    <div class="font-semibold text-slate-900 text-sm truncate" title="{{ $visit->visitor_name }}">{{ $visit->visitor_name }}</div>
                                    <div class="text-[11px] text-slate-500 font-mono truncate" title="{{ $visit->public_id }}">{{ $visit->public_id }}</div>
                                </td>
                                <td class="hidden sm:table-cell px-3 py-2.5 text-xs text-slate-600">
                                    <div class="truncate" title="{{ $visit->origin_organization }}">
                                        {{ $visit->origin_organization ?: '—' }}
                                    </div>
                                </td>
                                <td class="hidden sm:table-cell px-3 py-2.5 text-xs text-slate-600">
                                    <div class="truncate" title="{{ $visit->department_name_snapshot }}">
                                        {{ $visit->department_name_snapshot ?: '—' }}
                                    </div>
                                </td>
                                <td class="px-3 py-2.5">
                                    <x-visits.status-badge :status="$visit->current_status" />
                                </td>
                                <td class="px-3 py-2.5 text-right text-xs">
                                    <x-ui.button :href="route('admin.visits.show', $visit)" variant="ghost" size="sm">
                                        Detail
                                        <x-slot:icon>
                                            <svg aria-hidden="true" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </x-slot:icon>
                                    </x-ui.button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-ui.card>
</div>

{{-- One confirmation dialog per page, used by the worklist cancel action. --}}
<x-ui.confirm-modal />
@endsection

@push('scripts')
<script>
(function() {
    function initDashboardCharts() {
        if (!window.Chart) {
            setTimeout(initDashboardCharts, 50);
            return;
        }

    // 1. Trend Line Chart
    const ctxTrend = document.getElementById('trendChart')?.getContext('2d');
    let trendChartInstance = null;

    if (ctxTrend) {
        const initialPeriod = @json($trendPeriods[$defaultPeriod] ?? ['labels' => $trendLabels, 'values' => $trendValues]);

        trendChartInstance = new Chart(ctxTrend, {
            type: 'line',
            data: {
                labels: initialPeriod.labels,
                datasets: [{
                    label: 'Jumlah Kunjungan',
                    data: initialPeriod.values,
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.08)',
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#4f46e5',
                    pointRadius: initialPeriod.labels.length > 20 ? 2 : 4,
                    pointHoverRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return ' ' + context.parsed.y + ' Tamu';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0, stepSize: 1 }
                    }
                }
            }
        });

        window.updateTrendChart = function(periodData) {
            if (!trendChartInstance) return;
            trendChartInstance.data.labels = periodData.labels;
            trendChartInstance.data.datasets[0].data = periodData.values;
            trendChartInstance.data.datasets[0].pointRadius = periodData.labels.length > 20 ? 2 : 4;
            trendChartInstance.update();
        };
    }

    // 2. Organization Category Donut Chart
    const ctxOrg = document.getElementById('orgChart')?.getContext('2d');
    if (ctxOrg) {
        new Chart(ctxOrg, {
            type: 'doughnut',
            data: {
                labels: @json($orgLabels),
                datasets: [{
                    data: @json($orgValues),
                    backgroundColor: [
                        '#3b82f6', // Pemerintah
                        '#f59e0b', // LSM/Ormas
                        '#10b981', // Akademisi
                        '#8b5cf6', // Umum/Swasta
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, font: { size: 11 } }
                    }
                },
                cutout: '65%',
            }
        });
    }

    // 3. Department Distribution Horizontal Bar Chart
    const ctxDept = document.getElementById('deptChart')?.getContext('2d');
    if (ctxDept) {
        new Chart(ctxDept, {
            type: 'bar',
            data: {
                labels: @json($deptLabels),
                datasets: [{
                    label: 'Tamu 30 Hari Terakhir',
                    data: @json($deptValues),
                    backgroundColor: '#6366f1',
                    borderRadius: 6,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: { left: 8 }
                },
                plugins: {
                    legend: { display: false },
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    },
                    y: {
                        ticks: {
                            callback: function(val, index) {
                                const raw = this.getLabelForValue ? this.getLabelForValue(val) : (Array.isArray(val) ? val.join(' ') : val);
                                const label = typeof raw === 'string' ? raw : String(raw ?? '');
                                return label.length > 16 ? label.slice(0, 16) + '…' : label;
                            }
                        }
                    }
                }
            }
        });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDashboardCharts);
} else {
    initDashboardCharts();
}
})();
</script>
@endpush
