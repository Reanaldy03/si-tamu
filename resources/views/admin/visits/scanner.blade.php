@extends('layouts.admin')

@section('title', 'Pemindai QR Code & Check-In Tamu')

@section('page-title', 'Pemindai QR Code & Check-In')

@section('content')
<div 
    x-data="{
        scanning: false,
        loading: false,
        qrScanner: null,
        cameras: [],
        selectedCameraId: '',
        errorMessage: '',
        successResult: null,
        manualInput: '',

        async init() {
            if (window.Html5Qrcode) {
                try {
                    const devices = await Html5Qrcode.getCameras();
                    if (devices && devices.length) {
                        this.cameras = devices;
                        this.selectedCameraId = devices[0].id;
                        // Prefer back/environment camera if available
                        const backCam = devices.find(d => d.label.toLowerCase().includes('back') || d.label.toLowerCase().includes('belakang') || d.label.toLowerCase().includes('environment'));
                        if (backCam) {
                            this.selectedCameraId = backCam.id;
                        }
                    }
                } catch (e) {
                    console.warn('Gagal memuat daftar kamera:', e);
                }
            }
        },

        async startScanner() {
            if (!this.selectedCameraId) {
                this.errorMessage = 'Tidak ada kamera yang terdeteksi pada perangkat ini.';
                return;
            }

            this.errorMessage = '';
            this.successResult = null;
            this.scanning = true;

            try {
                if (!this.qrScanner) {
                    this.qrScanner = new Html5Qrcode('qr-reader');
                }

                await this.qrScanner.start(
                    this.selectedCameraId,
                    {
                        fps: 10,
                        qrbox: { width: 250, height: 250 },
                        aspectRatio: 1.0,
                    },
                    (decodedText) => {
                        this.handleScanSuccess(decodedText);
                    },
                    (errorMessage) => {
                        // ignore parse frame errors
                    }
                );
            } catch (err) {
                this.scanning = false;
                this.errorMessage = 'Gagal mengakses kamera: ' + (err?.message || err);
            }
        },

        async stopScanner() {
            if (this.qrScanner && this.scanning) {
                try {
                    await this.qrScanner.stop();
                } catch (e) {}
                this.scanning = false;
            }
        },

        async handleScanSuccess(payload) {
            // Stop scanning immediately upon read to prevent repeat calls
            await this.stopScanner();

            // Play audio chime and haptic feedback
            if (window.playBeep) window.playBeep(960, 180);
            if (navigator.vibrate) navigator.vibrate([100, 50, 100]);

            this.submitCheckIn(payload);
        },

        async submitCheckIn(payloadText) {
            if (!payloadText || this.loading) return;

            this.loading = true;
            this.errorMessage = '';
            this.successResult = null;

            try {
                const response = await fetch('{{ route('admin.checkin.process') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ payload: payloadText })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    this.successResult = data.data;
                    this.manualInput = '';
                } else {
                    this.errorMessage = data.message || 'Check-in gagal diproses.';
                }
            } catch (err) {
                this.errorMessage = 'Terjadi kesalahan jaringan atau server saat memproses check-in.';
            } finally {
                this.loading = false;
            }
        },

        submitManual() {
            if (!this.manualInput.trim()) {
                this.errorMessage = 'Masukkan nomor kunjungan (ULID) terlebih dahulu.';
                return;
            }
            this.submitCheckIn(this.manualInput.trim());
        }
    }"
    class="max-w-4xl mx-auto space-y-6"
>
    <!-- Header Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 sm:p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2.5">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                </svg>
                <span>Pemindai QR Code Resepsionis</span>
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Arahkan kamera ke QR Code kartu tamu atau masukkan nomor kunjungan untuk check-in kehadiran di Gedung DPRD Kota Binjai.
            </p>
        </div>

        <a 
            href="{{ route('admin.visits.index') }}" 
            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
            </svg>
            <span>Daftar Kunjungan</span>
        </a>
    </div>

    <!-- Error Alert Banner -->
    <template x-if="errorMessage">
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-start gap-3 shadow-xs transition animate-fade-in">
            <svg class="w-5 h-5 text-rose-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
            <div class="flex-1">
                <span class="font-semibold block">Gagal Check-In</span>
                <span x-text="errorMessage"></span>
            </div>
            <button type="button" @click="errorMessage = ''" class="text-rose-500 hover:text-rose-700 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </template>

    <!-- Success Result Card -->
    <template x-if="successResult">
        <div class="bg-emerald-50 border-2 border-emerald-500 rounded-2xl p-6 shadow-sm space-y-4 animate-fade-in">
            <div class="flex items-center justify-between pb-3 border-b border-emerald-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-emerald-500 text-white flex items-center justify-center font-bold">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-emerald-950 text-base">Check-In Berhasil Dikonfirmasi!</h3>
                        <p class="text-xs text-emerald-800">Tamu telah resmi berstatus <strong>TIBA (ARRIVED)</strong> di lobi Sekretariat DPRD.</p>
                    </div>
                </div>
                <span class="px-3 py-1 bg-emerald-600 text-white text-xs font-bold rounded-lg uppercase tracking-wider" x-text="successResult.checked_in_at"></span>
            </div>

            <!-- Detail Visitor Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                <div class="p-3 bg-white/80 rounded-xl border border-emerald-100">
                    <span class="text-slate-500 block">Nama Tamu</span>
                    <span class="text-sm font-bold text-slate-900" x-text="successResult.visitor_name"></span>
                </div>
                <div class="p-3 bg-white/80 rounded-xl border border-emerald-100">
                    <span class="text-slate-500 block">Asal Instansi</span>
                    <span class="text-sm font-semibold text-slate-900" x-text="successResult.origin_organization"></span>
                </div>
                <div class="p-3 bg-white/80 rounded-xl border border-emerald-100 sm:col-span-2">
                    <span class="text-slate-500 block">Tujuan Bagian / Komisi</span>
                    <span class="text-sm font-bold text-indigo-700" x-text="successResult.department"></span>
                </div>
                <div class="p-3 bg-white/80 rounded-xl border border-emerald-100 sm:col-span-2">
                    <span class="text-slate-500 block">Keperluan</span>
                    <span class="text-xs font-medium text-slate-800" x-text="successResult.purpose"></span>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="pt-2 flex flex-wrap items-center justify-between gap-3">
                <button 
                    type="button" 
                    @click="successResult = null; startScanner();" 
                    class="px-4 py-2 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold transition cursor-pointer flex items-center gap-1.5"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Pindai Tamu Berikutnya</span>
                </button>

                <a 
                    :href="successResult.detail_url" 
                    class="px-4 py-2 rounded-xl bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-semibold transition"
                >
                    Buka Detail Kunjungan &rarr;
                </a>
            </div>
        </div>
    </template>

    <!-- Main Scanner Grid (Camera on Left, Manual Input on Right) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Camera Scanner Section (7 cols) -->
        <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200 shadow-xs p-5 sm:p-6 space-y-4">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full" :class="scanning ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400'"></span>
                    Kamera Pemindai QR
                </span>

                <!-- Camera Select -->
                <template x-if="cameras.length > 1">
                    <select 
                        x-model="selectedCameraId" 
                        @change="if (scanning) { stopScanner(); startScanner(); }"
                        class="text-xs bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1 text-slate-700 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    >
                        <template x-for="cam in cameras" :key="cam.id">
                            <option :value="cam.id" x-text="cam.label || 'Kamera ' + cam.id"></option>
                        </template>
                    </select>
                </template>
            </div>

            <!-- Viewfinder Area -->
            <div class="relative bg-slate-950 rounded-2xl overflow-hidden min-h-[300px] flex items-center justify-center border-2 border-dashed border-slate-300">
                <div id="qr-reader" class="w-full max-w-sm [&>video]:rounded-xl overflow-hidden"></div>

                <template x-if="!scanning">
                    <div class="absolute inset-0 flex flex-col items-center justify-center p-6 text-center text-white bg-slate-900/90 space-y-3">
                        <div class="w-14 h-14 rounded-2xl bg-white/10 flex items-center justify-center">
                            <svg class="w-8 h-8 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold">Kamera Belum Aktif</p>
                            <p class="text-xs text-slate-400 max-w-xs mt-0.5">
                                Klik tombol di bawah untuk mengaktifkan pemindai kamera perangkat.
                            </p>
                        </div>
                        <button 
                            type="button" 
                            @click="startScanner()" 
                            class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-sm transition cursor-pointer flex items-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Nyalakan Kamera</span>
                        </button>
                    </div>
                </template>

                <!-- Scanning overlay line -->
                <template x-if="scanning">
                    <div class="pointer-events-none absolute inset-x-8 top-1/2 -translate-y-1/2 h-0.5 bg-indigo-500 shadow-[0_0_12px_#6366f1] animate-pulse"></div>
                </template>
            </div>

            <!-- Camera Controls -->
            <div class="flex items-center justify-between pt-1">
                <template x-if="scanning">
                    <button 
                        type="button" 
                        @click="stopScanner()" 
                        class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition cursor-pointer"
                    >
                        Hentikan Kamera
                    </button>
                </template>
                <template x-if="!scanning">
                    <span class="text-xs text-slate-400 italic">Kamera dalam status siaga</span>
                </template>

                <span class="text-xs text-slate-500">
                    Pastikan QR Code tegak lurus dan pencahayaan memadai.
                </span>
            </div>
        </div>

        <!-- Manual Input Fallback Section (5 cols) -->
        <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200 shadow-xs p-5 sm:p-6 space-y-4">
            <div class="space-y-1">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <span>Input Manual / Fallback</span>
                </h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Gunakan formulir ini jika kamera bermasalah, buram, atau tamu hanya membawa nomor kunjungan 26 karakter (ULID).
                </p>
            </div>

            <form @submit.prevent="submitManual()" class="space-y-4 pt-1">
                <div class="space-y-1.5">
                    <label for="manual-ulid" class="text-xs font-semibold text-slate-700">
                        Nomor Kunjungan Tamu (ULID)
                    </label>
                    <input 
                        id="manual-ulid"
                        type="text" 
                        x-model="manualInput" 
                        placeholder="Contoh: 01M22EAX525034MJ7W7XX3EQJ0"
                        class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs sm:text-sm font-mono tracking-wider focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        :disabled="loading"
                    />
                </div>

                <button 
                    type="submit" 
                    :disabled="loading || !manualInput.trim()"
                    class="w-full py-2.5 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 disabled:bg-slate-300 text-white text-xs font-bold shadow-xs transition flex items-center justify-center gap-2 cursor-pointer disabled:cursor-not-allowed"
                >
                    <template x-if="loading">
                        <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>
                    <span x-text="loading ? 'Memproses Check-In...' : 'Verifikasi & Check-In Tamu'"></span>
                </button>
            </form>

            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-600 text-[11px] leading-relaxed space-y-1">
                <span class="font-semibold block text-slate-700">Petunjuk Petugas:</span>
                <p>1. Status tamu yang berhasil check-in akan otomatis berubah menjadi <strong>TIBA</strong>.</p>
                <p>2. Arahkan tamu menuju lantai atau ruang sidang/komisi terkait di Gedung DPRD.</p>
            </div>
        </div>
    </div>
</div>
@endsection
