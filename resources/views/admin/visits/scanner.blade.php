@extends('layouts.admin')

@section('title', 'Pemindai QR Code & Check-In Tamu')

@section('page-title', 'Pemindai QR Code & Check-In')

@section('content')
<script>
function qrScannerComponent() {
    return {
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
                        const rgbCameras = devices.filter(d => {
                            const lbl = (d.label || '').toLowerCase();
                            return !lbl.includes('ir') && 
                                   !lbl.includes('infrared') && 
                                   !lbl.includes('windows hello') && 
                                   !lbl.includes('virtual');
                        });
                        const pool = rgbCameras.length > 0 ? rgbCameras : devices;
                        const backCam = pool.find(d => {
                            const lbl = (d.label || '').toLowerCase();
                            return lbl.includes('back') || lbl.includes('belakang') || lbl.includes('environment');
                        });
                        this.selectedCameraId = backCam ? backCam.id : pool[0].id;
                    }
                } catch (e) {
                    // Silently ignore before permission granted
                }
            }
        },

        async startScanner() {
            this.errorMessage = '';
            this.successResult = null;

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                this.errorMessage = 'Akses kamera tidak didukung atau diblokir oleh browser. Pastikan URL dibuka melalui http://localhost:8000 atau HTTPS.';
                return;
            }

            this.loading = true;

            try {
                if (this.qrScanner) {
                    try { await this.qrScanner.stop(); } catch (e) {}
                    try { this.qrScanner.clear(); } catch (e) {}
                }
                this.qrScanner = new Html5Qrcode('qr-reader');

                const config = {
                    fps: 15,
                    qrbox: (viewfinderWidth, viewfinderHeight) => {
                        const minEdge = Math.min(viewfinderWidth, viewfinderHeight);
                        const edge = Math.floor(minEdge * 0.7);
                        return {
                            width: Math.max(160, Math.min(260, edge)),
                            height: Math.max(160, Math.min(260, edge)),
                        };
                    },
                };

                const onScanSuccess = (decodedText) => {
                    this.handleScanSuccess(decodedText);
                };

                const onScanError = (errMsg) => {
                    // Frame scan noise
                };

                // If specific camera ID is selected, use it
                if (this.selectedCameraId) {
                    await this.qrScanner.start(this.selectedCameraId, config, onScanSuccess, onScanError);
                } else {
                    // Try facingMode user first (standard for laptop/webcam)
                    try {
                        await this.qrScanner.start({ facingMode: 'user' }, config, onScanSuccess, onScanError);
                    } catch (userErr) {
                        console.warn('Start facing user failed, trying environment...', userErr);
                        await this.qrScanner.start({ facingMode: 'environment' }, config, onScanSuccess, onScanError);
                    }

                    // Query available cameras now that permission is active
                    try {
                        const devices = await Html5Qrcode.getCameras();
                        if (devices && devices.length) {
                            this.cameras = devices;
                            const rgbCameras = devices.filter(d => {
                                const lbl = (d.label || '').toLowerCase();
                                return !lbl.includes('ir') && 
                                       !lbl.includes('infrared') && 
                                       !lbl.includes('windows hello') && 
                                       !lbl.includes('virtual');
                            });
                            const pool = rgbCameras.length > 0 ? rgbCameras : devices;
                            this.selectedCameraId = pool[0].id;
                        }
                    } catch (devErr) {}
                }

                this.scanning = true;

            } catch (err) {
                this.scanning = false;
                console.error('Kamera gagal dijalankan:', err);

                const errName = err?.name || '';
                const errMsg = String(err?.message || err);

                if (errName === 'NotAllowedError' || errMsg.includes('Permission denied') || errMsg.includes('NotAllowedError')) {
                    this.errorMessage = 'Izin kamera diblokir browser atau Windows. Solusi: (1) Klik tombol "Reset izin" di ikon setelan URL browser Anda, lalu klik Muat Ulang Halaman (F5). (2) Pastikan di Windows Settings > Privacy & Security > Camera > "Let desktop apps access your camera" dalam posisi ON. [' + (errMsg || errName) + ']';
                } else if (errName === 'NotFoundError' || errMsg.includes('Requested device not found') || errMsg.includes('NotFoundError') || errMsg.includes('NOT_FOUND_NO_DEVICE')) {
                    this.errorMessage = 'Tidak ada perangkat kamera fisik (webcam) yang terhubung pada komputer ini. Silakan gunakan tombol Unggah File QR atau formulir Input Manual ULID di sebelah kanan.';
                } else if (errName === 'NotReadableError' || errMsg.includes('Could not start video source') || errMsg.includes('NotReadableError')) {
                    this.errorMessage = 'Kamera sedang digunakan oleh aplikasi lain (Zoom, Google Meet, OBS, atau Kamera Windows). Tutup aplikasi tersebut lalu coba lagi. [' + (errMsg || errName) + ']';
                } else {
                    this.errorMessage = 'Gagal mengakses kamera: ' + errMsg + '. Anda dapat menggunakan tombol Unggah File QR atau formulir Input Manual ULID.';
                }
            } finally {
                this.loading = false;
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

        async switchCamera() {
            if (this.cameras.length <= 1) return;
            const currentIndex = this.cameras.findIndex(c => c.id === this.selectedCameraId);
            const nextIndex = (currentIndex + 1) % this.cameras.length;
            this.selectedCameraId = this.cameras[nextIndex].id;
            if (this.scanning) {
                await this.stopScanner();
                await this.startScanner();
            }
        },

        async scanFileEvent(event) {
            const file = event.target?.files?.[0];
            if (file) {
                await this.scanImageFile(file);
            }
            event.target.value = '';
        },

        async handleFileDrop(event) {
            const file = event.dataTransfer?.files?.[0];
            if (file && file.type.startsWith('image/')) {
                await this.scanImageFile(file);
            }
        },

        async scanImageFile(file) {
            if (!file) return;

            this.errorMessage = '';
            this.successResult = null;
            this.loading = true;

            try {
                if (!this.qrScanner) {
                    this.qrScanner = new Html5Qrcode('qr-reader');
                }

                if (this.scanning) {
                    await this.stopScanner();
                }

                const decodedText = await this.qrScanner.scanFile(file, true);
                if (decodedText) {
                    await this.handleScanSuccess(decodedText);
                } else {
                    this.errorMessage = 'QR Code tidak terdeteksi pada file gambar yang dipilih. Pastikan gambar jelas dan QR Code tidak terpotong.';
                }
            } catch (err) {
                console.error('File scan error:', err);
                this.errorMessage = 'Gagal membaca QR Code dari file gambar. Pastikan gambar memuat QR Code yang jelas atau gunakan formulir Input Manual ULID.';
            } finally {
                this.loading = false;
            }
        },

        async handleScanSuccess(payload) {
            await this.stopScanner();
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
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
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
    };
}
</script>

<div 
    x-data="qrScannerComponent()"
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
            <div class="flex-1 space-y-2.5">
                <div>
                    <span class="font-semibold block">Kendala Akses Kamera</span>
                    <span x-text="errorMessage"></span>
                </div>
                <div class="flex flex-wrap items-center gap-2 pt-0.5">
                    <button 
                        type="button" 
                        @click="window.location.reload()" 
                        class="px-3.5 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-xs transition cursor-pointer flex items-center gap-1.5"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span>Muat Ulang Halaman (F5)</span>
                    </button>
                    <label class="px-3.5 py-1.5 rounded-lg bg-white border border-rose-300 hover:bg-rose-100 text-rose-700 text-xs font-semibold shadow-xs transition cursor-pointer flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>Gunakan Unggah File QR</span>
                        <input type="file" accept="image/*" class="hidden" @change="scanFileEvent($event)">
                    </label>
                </div>
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
            <div 
                class="relative bg-slate-950 rounded-2xl overflow-hidden min-h-[320px] flex items-center justify-center border-2 border-dashed border-slate-300 transition"
                @drop.prevent="handleFileDrop($event)"
                @dragover.prevent
            >
                <div id="qr-reader" class="w-full max-w-sm [&>video]:rounded-xl overflow-hidden"></div>

                <template x-if="!scanning">
                    <div class="absolute inset-0 flex flex-col items-center justify-center p-6 text-center text-white bg-slate-900/90 space-y-3.5">
                        <div class="w-14 h-14 rounded-2xl bg-white/10 flex items-center justify-center">
                            <svg class="w-8 h-8 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold">Kamera Belum Aktif</p>
                            <p class="text-xs text-slate-400 max-w-xs mt-0.5">
                                Nyalakan kamera untuk scan langsung, atau unggah foto/screenshot QR tamu.
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center justify-center gap-2.5">
                            <button 
                                type="button" 
                                @click="startScanner()" 
                                :disabled="loading"
                                class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:bg-indigo-400 text-white text-xs font-bold shadow-sm transition cursor-pointer flex items-center gap-2"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Nyalakan Kamera</span>
                            </button>

                            <label class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white text-xs font-semibold shadow-sm transition cursor-pointer flex items-center gap-2">
                                <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>Unggah File QR</span>
                                <input type="file" accept="image/*" class="hidden" @change="scanFileEvent($event)">
                            </label>
                        </div>
                    </div>
                </template>

                <!-- Scanning overlay line -->
                <template x-if="scanning">
                    <div class="pointer-events-none absolute inset-x-8 top-1/2 -translate-y-1/2 h-0.5 bg-indigo-500 shadow-[0_0_12px_#6366f1] animate-pulse"></div>
                </template>
            </div>

            <!-- Camera Controls -->
            <div class="flex flex-wrap items-center justify-between gap-2 pt-1">
                <div class="flex flex-wrap items-center gap-2">
                    <template x-if="scanning">
                        <button 
                            type="button" 
                            @click="stopScanner()" 
                            class="px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition cursor-pointer"
                        >
                            Hentikan Kamera
                        </button>
                    </template>

                    <template x-if="scanning && cameras.length > 1">
                        <button 
                            type="button" 
                            @click="switchCamera()" 
                            class="px-3.5 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold transition cursor-pointer flex items-center gap-1.5"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span>Ganti Kamera</span>
                        </button>
                    </template>

                    <label class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition cursor-pointer inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>Pindai File Gambar</span>
                        <input type="file" accept="image/*" class="hidden" @change="scanFileEvent($event)">
                    </label>
                </div>

                <span class="text-[11px] text-slate-400">
                    Bisa scan kamera, upload file QR, atau input ULID manual.
                </span>
            </div>

            <!-- Dark Screen Troubleshooter Helper -->
            <template x-if="scanning">
                <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-900 flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                    <div class="space-y-1 text-[11px]">
                        <p class="font-bold text-amber-950">Layar kamera gelap atau hitam?</p>
                        <ul class="list-disc list-inside space-y-0.5 text-amber-800">
                            <li>Periksa <strong>slider penutup fisik webcam</strong> di atas layar laptop Anda (pastikan digeser hingga lensa terbuka).</li>
                            <li>Jika laptop Anda memiliki tombol fungsi kamera (contoh: <code>Fn + F6</code> atau <code>F10</code>), pastikan tidak dalam mode privasi nonaktif.</li>
                            <li>Jika ada lebih dari satu kamera terdeteksi, klik tombol <strong>Ganti Kamera</strong> di atas.</li>
                        </ul>
                    </div>
                </div>
            </template>
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

@push('scripts')
<style>
    #qr-reader {
        width: 100% !important;
        border: none !important;
    }
    #qr-reader video {
        width: 100% !important;
        height: auto !important;
        max-height: 420px;
        object-fit: cover;
        border-radius: 0.75rem;
    }
    #qr-reader img {
        margin: 0 auto;
        border-radius: 0.75rem;
    }
    #qr-shaded-region {
        border-color: rgba(99, 102, 241, 0.4) !important;
    }
</style>
@endpush
