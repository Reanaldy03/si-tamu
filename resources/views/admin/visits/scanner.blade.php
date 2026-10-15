@extends('layouts.admin')

@section('title', 'Pemindai QR Code & Check-In Tamu')

@section('header_title', 'Pemindai QR Code & Check-In')

@section('content')
<script>
function qrScannerComponent() {
    return {
        scanning: false,
        loading: false,
        imageProcessing: false,
        imageFileName: '',
        submitting: false,
        qrScanner: null,
        cameras: [],
        selectedCameraId: '',
        errorMessage: '',
        errorKind: 'camera',
        successResult: null,
        manualInput: '',

        setError(message, kind = 'camera') {
            this.errorKind = kind;
            this.errorMessage = message;
        },

        init() {
            // Auto-start scanner immediately on page load
            this.$nextTick(() => {
                this.startScanner();
            });
        },

        async startScanner(deviceId = null) {
            this.setError('');
            this.successResult = null;

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                this.setError('Akses kamera tidak didukung atau diblokir oleh browser. Pastikan alamat dibuka melalui HTTPS atau http://localhost.', 'camera');
                return;
            }

            this.loading = true;

            try {
                // Pre-flight: no video input device at all -> skip library attempts and show a clear message
                const targetDevice = deviceId || this.selectedCameraId;
                if (!targetDevice) {
                    const available = await navigator.mediaDevices.enumerateDevices();
                    if (!available.some(d => d.kind === 'videoinput')) {
                        this.setError('Tidak ada perangkat kamera fisik (webcam) yang terhubung pada komputer ini. Silakan gunakan tombol Unggah File QR atau formulir Input Manual ULID yang tersedia.', 'camera');
                        return;
                    }
                }

                if (this.qrScanner) {
                    try { await this.qrScanner.stop(); } catch (e) {}
                    try { this.qrScanner.clear(); } catch (e) {}
                }

                this.qrScanner = new Html5Qrcode('qr-reader', {
                    verbose: false,
                    experimentalFeatures: {
                        useBarCodeDetectorIfSupported: true,
                    },
                });

                // No qrbox restriction: decodes full camera frame with max speed & angle tolerance
                const config = {
                    fps: 15,
                };

                const onScanSuccess = (decodedText) => {
                    this.handleScanSuccess(decodedText);
                };

                const onScanError = (errMsg) => {
                    // Frame scan noise
                };

                const targetId = deviceId || this.selectedCameraId;

                if (targetId) {
                    await this.qrScanner.start(targetId, config, onScanSuccess, onScanError);
                } else {
                    // Strategy 1: facingMode environment (back camera / mobile)
                    try {
                        await this.qrScanner.start({ facingMode: 'environment' }, config, onScanSuccess, onScanError);
                    } catch (envErr) {
                        // Strategy 2: facingMode user (front webcam / laptop)
                        try {
                            await this.qrScanner.start({ facingMode: 'user' }, config, onScanSuccess, onScanError);
                        } catch (userErr) {
                            // Strategy 3: Desktop USB webcams without facingMode attribute
                            const devices = await navigator.mediaDevices.enumerateDevices();
                            const videoInputs = devices.filter(d => d.kind === 'videoinput' && d.deviceId);
                            if (videoInputs.length > 0) {
                                await this.qrScanner.start(videoInputs[0].deviceId, config, onScanSuccess, onScanError);
                            } else {
                                // No usable device id: prefer the most informative earlier failure (permission / busy)
                                const informative = [envErr, userErr].find(e => ['NotAllowedError', 'NotReadableError'].includes(e?.name));
                                throw informative || new DOMException('No camera device available on this computer.', 'NotFoundError');
                            }
                        }
                    }
                }

                // Camera is actively rendering! Update states IMMEDIATELY
                this.scanning = true;
                this.loading = false;

                // Query available cameras safely using enumerateDevices (never calls getUserMedia)
                try {
                    const devices = await navigator.mediaDevices.enumerateDevices();
                    const videoInputs = devices.filter(d => d.kind === 'videoinput');
                    const rgbCameras = videoInputs.filter(d => {
                        const lbl = (d.label || '').toLowerCase();
                        return !lbl.includes('ir') && 
                               !lbl.includes('infrared') && 
                               !lbl.includes('windows hello');
                    });
                    this.cameras = (rgbCameras.length > 0 ? rgbCameras : videoInputs).map(d => ({
                        id: d.deviceId,
                        label: d.label || 'Kamera ' + d.deviceId.slice(0, 5),
                    }));
                    if (!this.selectedCameraId && this.cameras.length > 0) {
                        this.selectedCameraId = this.cameras[0].id;
                    }
                } catch (devErr) {}

            } catch (err) {
                this.scanning = false;
                console.error('Kamera gagal dijalankan:', err);

                // Never interpolate the raw error object: it stringifies to
                // "[object Object]" and tells the operator nothing.
                const errName = typeof err?.name === 'string' ? err.name : '';
                const errMsg = typeof err?.message === 'string' ? err.message : '';
                const detail = errMsg || errName;
                const suffix = detail ? ' [' + detail + ']' : '';
                const insecureOrigin = typeof window !== 'undefined' && window.isSecureContext === false;

                if (insecureOrigin || !navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    this.setError('Halaman ini tidak dijalankan pada koneksi aman, sehingga browser memblokir kamera. Buka lewat HTTPS atau http://localhost, lalu muat ulang halaman.' + suffix, 'camera');
                } else if (errName === 'NotAllowedError' || errMsg.includes('Permission denied') || errMsg.includes('NotAllowedError')) {
                    this.setError('Izin kamera diblokir browser atau Windows. Solusi: (1) Klik ikon gembok/setelan di sebelah URL browser Anda, ubah izin Kamera menjadi "Allow", lalu tekan F5. (2) Pastikan di Windows Settings > Privacy & Security > Camera > "Let desktop apps access your camera" dalam posisi ON.' + suffix, 'camera');
                } else if (errName === 'NotFoundError' || errMsg.includes('Requested device not found') || errMsg.includes('NotFoundError') || errMsg.includes('NOT_FOUND_NO_DEVICE') || errMsg.includes('cameraIdOrConfig is required')) {
                    this.setError('Tidak ada perangkat kamera fisik (webcam) yang terhubung pada komputer ini. Silakan gunakan tombol Unggah File QR atau formulir Input Manual ULID yang tersedia.', 'camera');
                } else if (errName === 'NotReadableError' || errMsg.includes('Could not start video source') || errMsg.includes('NotReadableError')) {
                    this.setError('Kamera sedang digunakan oleh aplikasi lain (Zoom, Google Meet, OBS, atau Kamera Windows). Tutup aplikasi tersebut lalu coba lagi.' + suffix, 'camera');
                } else {
                    this.setError('Kamera gagal dijalankan. Anda masih dapat menggunakan tombol Unggah File QR atau formulir Input Manual ULID.' + suffix, 'camera');
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
            await this.stopScanner();
            await this.startScanner(this.selectedCameraId);
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

        async decodeQrFromImage(file) {
            // 1. Native Chromium BarcodeDetector (instant, hardware accelerated, handles skewed angles)
            if ('BarcodeDetector' in window) {
                try {
                    const detector = new BarcodeDetector({ formats: ['qr_code'] });
                    const bitmap = await createImageBitmap(file);
                    const barcodes = await detector.detect(bitmap);
                    if (barcodes && barcodes.length > 0 && barcodes[0].rawValue) {
                        return barcodes[0].rawValue;
                    }
                } catch (e) {
                    console.debug('BarcodeDetector fallback:', e);
                }
            }

            // 2. Load file into HTMLImageElement
            const img = await new Promise((resolve, reject) => {
                const image = new Image();
                const url = URL.createObjectURL(file);
                image.onload = () => {
                    URL.revokeObjectURL(url);
                    resolve(image);
                };
                image.onerror = (e) => {
                    URL.revokeObjectURL(url);
                    reject(new Error('Gagal memuat file gambar.'));
                };
                image.src = url;
            });

            const naturalWidth = img.naturalWidth || img.width;
            const naturalHeight = img.naturalHeight || img.height;

            if (!naturalWidth || !naturalHeight) {
                throw new Error('Dimensi gambar tidak valid.');
            }

            const tryJsQr = (canvas) => {
                if (!window.jsQR) return null;
                const ctx = canvas.getContext('2d', { willReadFrequently: true });
                if (!ctx) return null;
                const imgData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                const code = window.jsQR(imgData.data, imgData.width, imgData.height, {
                    inversionAttempts: 'attemptBoth',
                });
                return code ? code.data : null;
            };

            // 3. Try jsQR at original full resolution (without downsampling blur)
            const offCanvas = document.createElement('canvas');
            offCanvas.width = naturalWidth;
            offCanvas.height = naturalHeight;
            const offCtx = offCanvas.getContext('2d', { willReadFrequently: true });
            offCtx.drawImage(img, 0, 0, naturalWidth, naturalHeight);

            let result = tryJsQr(offCanvas);
            if (result) return result;

            // 4. Try jsQR with downscaling if image is very large (e.g. > 1200px smartphone photo)
            const maxDim = Math.max(naturalWidth, naturalHeight);
            if (maxDim > 1200) {
                const scale = 1000 / maxDim;
                const scaledCanvas = document.createElement('canvas');
                scaledCanvas.width = Math.round(naturalWidth * scale);
                scaledCanvas.height = Math.round(naturalHeight * scale);
                const sCtx = scaledCanvas.getContext('2d', { willReadFrequently: true });
                sCtx.drawImage(img, 0, 0, scaledCanvas.width, scaledCanvas.height);

                result = tryJsQr(scaledCanvas);
                if (result) return result;
            }

            // 5. Try jsQR with nearest-neighbor 2x upscale if image is small (< 400px icon / screenshot)
            if (naturalWidth < 400 && naturalHeight < 400) {
                const upscaleCanvas = document.createElement('canvas');
                upscaleCanvas.width = naturalWidth * 2;
                upscaleCanvas.height = naturalHeight * 2;
                const uCtx = upscaleCanvas.getContext('2d', { willReadFrequently: true });
                uCtx.imageSmoothingEnabled = false;
                uCtx.drawImage(img, 0, 0, upscaleCanvas.width, upscaleCanvas.height);

                result = tryJsQr(upscaleCanvas);
                if (result) return result;
            }

            // 6. Try jsQR with Binarization / Contrast Enhancement
            try {
                const contrastCanvas = document.createElement('canvas');
                const targetW = Math.min(naturalWidth, 800);
                const targetH = Math.round(naturalHeight * (targetW / naturalWidth));
                contrastCanvas.width = targetW;
                contrastCanvas.height = targetH;
                const cCtx = contrastCanvas.getContext('2d', { willReadFrequently: true });
                cCtx.drawImage(img, 0, 0, targetW, targetH);
                const imgData = cCtx.getImageData(0, 0, targetW, targetH);
                const d = imgData.data;
                for (let i = 0; i < d.length; i += 4) {
                    const gray = 0.299 * d[i] + 0.587 * d[i + 1] + 0.114 * d[i + 2];
                    const val = gray > 140 ? 255 : 0;
                    d[i] = val;
                    d[i + 1] = val;
                    d[i + 2] = val;
                }
                cCtx.putImageData(imgData, 0, 0);
                result = tryJsQr(contrastCanvas);
                if (result) return result;
            } catch (e) {}

            // 7. Fallback to Html5Qrcode.scanFile (with showImage: false to avoid DOM corruption)
            try {
                if (!this.qrScanner) {
                    this.qrScanner = new Html5Qrcode('qr-reader', {
                        verbose: false,
                        experimentalFeatures: {
                            useBarCodeDetectorIfSupported: true,
                        },
                    });
                }
                const html5Result = await this.qrScanner.scanFile(file, false);
                if (html5Result) return html5Result;
            } catch (e) {
                console.debug('Html5Qrcode fallback failed:', e);
            }

            return null;
        },

        async scanImageFile(file) {
            if (!file) return;

            this.errorMessage = '';
            this.successResult = null;
            this.imageProcessing = true;
            this.imageFileName = file.name || 'File Gambar';

            if (this.scanning) {
                await this.stopScanner();
            }

            try {
                const decodedText = await this.decodeQrFromImage(file);
                if (decodedText) {
                    await this.handleScanSuccess(decodedText);
                } else {
                    this.setError('QR Code tidak terdeteksi pada file gambar "' + (file.name || 'yang dipilih') + '". Pastikan file memuat gambar QR Code yang jelas dan tidak terpotong, atau gunakan formulir Input Manual ULID yang tersedia.', 'image');
                }
            } catch (err) {
                console.error('File scan error:', err);
                const fileErrMsg = typeof err?.message === 'string' && err.message
                    ? err.message
                    : 'Berkas gambar tidak dapat diproses';
                this.setError('Gagal memproses file gambar: ' + fileErrMsg + '. Silakan gunakan formulir Input Manual ULID.', 'image');
            } finally {
                // Ensure no leftover canvas clutter in #qr-reader
                try {
                    const readerEl = document.getElementById('qr-reader');
                    if (readerEl && !this.scanning) {
                        const leftoverCanvases = readerEl.querySelectorAll('canvas');
                        leftoverCanvases.forEach(c => c.remove());
                    }
                } catch (e) {}
                this.imageProcessing = false;
            }
        },

        async handleScanSuccess(payload) {
            if (this.submitting) return;
            this.submitting = true;

            if (window.playBeep) window.playBeep(960, 180);
            if (navigator.vibrate) navigator.vibrate([100, 50, 100]);

            await this.stopScanner();
            await this.submitCheckIn(payload);
            this.submitting = false;
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
                    this.setError(data.message || 'Check-in gagal diproses.', 'checkin');
                }
            } catch (err) {
                this.setError('Terjadi kesalahan jaringan atau server saat memproses check-in.', 'checkin');
            } finally {
                this.loading = false;
            }
        },

        submitManual() {
            if (!this.manualInput.trim()) {
                this.setError('Masukkan nomor kunjungan (ULID) terlebih dahulu.', 'checkin');
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
            <h1 class="text-xl font-bold text-slate-900 flex items-center gap-2.5">
                <svg aria-hidden="true" class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                </svg>
                <span>Pemindai QR Code Resepsionis</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Arahkan kamera ke QR Code kartu tamu atau masukkan nomor kunjungan untuk check-in kehadiran di Gedung DPRD Kota Binjai.
            </p>
        </div>

        <a 
            href="{{ route('admin.visits.index') }}" 
            class="inline-flex items-center gap-1.5 min-h-11 px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold whitespace-nowrap shrink-0 transition focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-500"
        >
            <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
            </svg>
            <span>Daftar kunjungan</span>
        </a>
    </div>

    <!-- Error Alert Banner -->
    <template x-if="errorMessage">
        <div role="alert" class="p-4 rounded-2xl bg-danger-50 border border-danger-200 text-danger-800 text-sm flex items-start gap-3 shadow-xs transition animate-fade-in">
            <svg aria-hidden="true" class="w-5 h-5 text-danger-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
            <div class="flex-1 space-y-2.5">
                <div>
                    <span class="font-semibold block" x-text="errorKind === 'image' ? 'Kendala pembacaan file gambar' : (errorKind === 'checkin' ? 'Check-in gagal' : 'Kendala akses kamera')"></span>
                    <span x-text="errorMessage"></span>
                </div>
                <div class="flex flex-wrap items-center gap-2 pt-0.5">
                    <template x-if="errorKind === 'image'">
                        <label class="min-h-11 px-3.5 py-2 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold shadow-xs transition cursor-pointer flex items-center gap-1.5 focus:outline-hidden focus-within:ring-2 focus-within:ring-primary-500 focus-within:ring-offset-2">
                            <svg aria-hidden="true" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span>Pilih gambar QR lain</span>
                            <input type="file" accept="image/*" class="hidden" @change="scanFileEvent($event)">
                        </label>
                    </template>

                    <button 
                        type="button" 
                        @click="errorMessage = ''; startScanner();" 
                        class="min-h-11 px-3.5 py-2 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold shadow-xs transition cursor-pointer flex items-center gap-1.5 focus:outline-hidden focus-within:ring-2 focus-within:ring-primary-500 focus-within:ring-offset-2"
                    >
                        <svg aria-hidden="true" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Coba nyalakan kamera</span>
                    </button>

                    <button 
                        type="button" 
                        @click="window.location.reload()" 
                        class="min-h-11 px-3.5 py-2 rounded-lg bg-danger-600 hover:bg-danger-700 text-white text-xs font-bold shadow-xs transition cursor-pointer flex items-center gap-1.5 focus:outline-hidden focus-within:ring-2 focus-within:ring-danger-500 focus-within:ring-offset-2"
                    >
                        <svg aria-hidden="true" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span>Muat ulang halaman (F5)</span>
                    </button>

                </div>
            </div>
            <button type="button" @click="errorMessage = ''" aria-label="Tutup pesan kendala" class="inline-flex items-center justify-center min-w-9 min-h-9 -m-1.5 rounded-lg text-danger-600 hover:text-danger-700 hover:bg-danger-100 transition cursor-pointer focus:outline-hidden focus-visible:ring-2 focus-visible:ring-danger-500">
                <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </template>

    <!-- Success Result Card -->
    <template x-if="successResult">
        <div role="status" class="bg-success-50 border-2 border-success-500 rounded-2xl p-6 shadow-sm space-y-4 animate-fade-in">
            <div class="flex items-center justify-between pb-3 border-b border-success-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-success-500 text-white flex items-center justify-center font-bold">
                        <svg aria-hidden="true" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-bold text-success-950 text-base">Check-in berhasil dikonfirmasi</h2>
                        <p class="text-xs text-success-800">Tamu telah resmi berstatus <strong>Tiba</strong> di lobi Sekretariat DPRD.</p>
                    </div>
                </div>
                <span class="px-3 py-1 bg-success-700 text-white text-xs font-bold rounded-lg" x-text="successResult.checked_in_at"></span>
            </div>

            <!-- Detail Visitor Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                <div class="p-3 bg-white/80 rounded-xl border border-success-100">
                    <span class="text-slate-500 block">Nama Tamu</span>
                    <span class="text-sm font-bold text-slate-900" x-text="successResult.visitor_name"></span>
                </div>
                <div class="p-3 bg-white/80 rounded-xl border border-success-100">
                    <span class="text-slate-500 block">Asal Instansi</span>
                    <span class="text-sm font-semibold text-slate-900" x-text="successResult.origin_organization"></span>
                </div>
                <div class="p-3 bg-white/80 rounded-xl border border-success-100 sm:col-span-2">
                    <span class="text-slate-500 block">Tujuan Bagian / Komisi</span>
                    <span class="text-sm font-bold text-primary-700" x-text="successResult.department"></span>
                </div>
                <div class="p-3 bg-white/80 rounded-xl border border-success-100 sm:col-span-2">
                    <span class="text-slate-500 block">Keperluan</span>
                    <span class="text-xs font-medium text-slate-800" x-text="successResult.purpose"></span>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="pt-2 flex flex-wrap items-center justify-between gap-3">
                <button 
                    type="button" 
                    @click="successResult = null; startScanner();" 
                    class="min-h-11 px-4 py-2.5 rounded-xl bg-success-700 hover:bg-success-800 text-white text-xs font-bold transition cursor-pointer flex items-center gap-1.5 focus:outline-hidden focus-visible:ring-2 focus-visible:ring-success-600 focus-visible:ring-offset-2"
                >
                    <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Pindai tamu berikutnya</span>
                </button>

                <a 
                    :href="successResult.detail_url" 
                    class="inline-flex items-center min-h-11 px-4 py-2.5 rounded-xl bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-semibold transition focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-500"
                >
                    Buka detail kunjungan &rarr;
                </a>
            </div>
        </div>
    </template>

    <!-- Main Scanner Grid (Camera on Left, Manual Input on Right) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
        <!-- Camera Scanner Section (7 cols) -->
        <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200 shadow-xs p-5 sm:p-6 space-y-4">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-700 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full" :class="scanning ? 'bg-success-500 animate-pulse' : 'bg-slate-400'"></span>
                    Kamera pemindai QR
                </span>

                <!-- Camera Select -->
                <template x-if="cameras.length > 1">
                    <select
                        x-model="selectedCameraId"
                        @change="if (scanning) { stopScanner(); startScanner(); }"
                        aria-label="Pilih kamera pemindai"
                        class="min-h-9 text-xs bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 text-slate-700 focus:outline-hidden focus:ring-2 focus:ring-primary-500"
                    >
                        <template x-for="cam in cameras" :key="cam.id">
                            <option :value="cam.id" x-text="cam.label || 'Kamera ' + cam.id"></option>
                        </template>
                    </select>
                </template>
            </div>

            <!-- Viewfinder Area -->
            <div 
                class="relative bg-slate-950 rounded-2xl overflow-hidden min-h-[340px] flex items-center justify-center border-2 border-slate-700 shadow-inner transition"
                @drop.prevent="handleFileDrop($event)"
                @dragover.prevent
            >
                <div id="qr-reader" class="w-full max-w-md [&>video]:rounded-xl overflow-hidden"></div>

                <!-- Loading State Overlay -->
                <template x-if="loading">
                    <div class="absolute inset-0 flex flex-col items-center justify-center p-6 text-center text-white bg-slate-900/90 space-y-3 z-10 animate-fade-in">
                        <svg aria-hidden="true" class="animate-spin h-9 w-9 text-primary-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-white">Menghubungkan kamera...</p>
                            <p class="text-xs text-slate-400 mt-0.5">Memulai sensor optik pemindai QR Code</p>
                        </div>
                    </div>
                </template>

                <!-- Image Processing State Overlay -->
                <template x-if="imageProcessing">
                    <div class="absolute inset-0 flex flex-col items-center justify-center p-6 text-center text-white bg-slate-900/95 space-y-3 z-20 animate-fade-in">
                        <svg aria-hidden="true" class="animate-spin h-9 w-9 text-primary-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-white">Menganalisis berkas gambar...</p>
                            <p class="text-xs text-slate-400 mt-0.5" x-text="imageFileName ? 'Mengekstrak QR dari: ' + imageFileName : 'Mengekstrak kode QR dari gambar'"></p>
                        </div>
                    </div>
                </template>

                <!-- Inactive Camera Overlay -->
                <template x-if="!scanning && !loading && !imageProcessing">
                    <div class="absolute inset-0 flex flex-col items-center justify-center p-6 text-center text-white bg-slate-900/95 space-y-3.5 z-10 animate-fade-in">
                        <div class="w-14 h-14 rounded-2xl bg-white/10 flex items-center justify-center">
                            <svg aria-hidden="true" class="w-8 h-8 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold">Kamera belum aktif</p>
                            <p class="text-xs text-slate-400 max-w-xs mt-0.5">
                                Klik tombol di bawah untuk menyalakan kamera, atau unggah foto/screenshot QR tamu.
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center justify-center gap-2.5">
                            <button 
                                type="button" 
                                @click="startScanner()" 
                                class="min-h-11 px-5 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold shadow-sm transition cursor-pointer flex items-center gap-2 focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2"
                            >
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Nyalakan kamera</span>
                            </button>

                            <label class="min-h-11 px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white text-xs font-semibold shadow-sm transition cursor-pointer flex items-center gap-2 focus:outline-hidden focus-within:ring-2 focus-within:ring-primary-400">
                                <svg aria-hidden="true" class="w-4 h-4 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>Unggah file QR</span>
                                <input type="file" accept="image/*" class="hidden" @change="scanFileEvent($event)">
                            </label>
                        </div>
                    </div>
                </template>


            </div>

            <!-- Camera Controls -->
            <div class="flex flex-wrap items-center justify-between gap-2 pt-1">
                <div class="flex flex-wrap items-center gap-2">
                    <template x-if="scanning">
                        <button 
                            type="button" 
                            @click="stopScanner()" 
                            class="min-h-9 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition cursor-pointer focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-500"
                        >
                            Hentikan Kamera
                        </button>
                    </template>

                    <template x-if="scanning && cameras.length > 1">
                        <button 
                            type="button" 
                            @click="switchCamera()" 
                            class="min-h-9 px-3.5 py-2 rounded-xl bg-primary-50 hover:bg-primary-100 text-primary-700 text-xs font-semibold transition cursor-pointer flex items-center gap-1.5 focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-500"
                        >
                            <svg aria-hidden="true" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span>Ganti kamera</span>
                        </button>
                    </template>

                    <label class="min-h-9 px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition cursor-pointer inline-flex items-center gap-1.5 focus:outline-hidden focus-within:ring-2 focus-within:ring-primary-500">
                        <svg aria-hidden="true" class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>Unggah file QR</span>
                        <input type="file" accept="image/*" class="hidden" @change="scanFileEvent($event)">
                    </label>
                </div>

                <span class="text-[11px] text-slate-500">
                    Bisa scan kamera, upload file QR, atau input ULID manual.
                </span>
            </div>

            <!-- Dark Screen Troubleshooter Helper -->
            <template x-if="scanning">
                <div class="p-3 rounded-xl bg-warning-50 border border-warning-200 text-xs text-warning-900 flex items-start gap-2.5">
                    <svg aria-hidden="true" class="w-4 h-4 text-warning-600 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                    <div class="space-y-1 text-[11px]">
                        <p class="font-bold text-warning-950">Layar kamera gelap atau hitam?</p>
                        <ul class="list-disc list-inside space-y-0.5 text-warning-800">
                            <li>Periksa <strong>slider penutup fisik webcam</strong> di atas layar laptop Anda (pastikan digeser hingga lensa terbuka).</li>
                            <li>Jika laptop Anda memiliki tombol fungsi kamera (contoh: <code>Fn + F6</code> atau <code>F10</code>), pastikan tidak dalam mode privasi nonaktif.</li>
                            <li>Jika ada lebih dari satu kamera terdeteksi, klik tombol <strong>Ganti Kamera</strong> di atas.</li>
                        </ul>
                    </div>
                </div>
            </template>
        </div>

        <!-- Manual Input Fallback Section (5 cols) -->
        <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200 shadow-xs p-5 sm:p-6 flex flex-col justify-between gap-4">
            <div class="space-y-4">
                <div class="space-y-1">
                    <h2 class="text-xs font-bold text-slate-700 flex items-center gap-2">
                        <svg aria-hidden="true" class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        <span>Input manual / fallback</span>
                    </h2>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Gunakan formulir ini jika kamera bermasalah, buram, atau tamu hanya membawa nomor kunjungan 26 karakter (ULID).
                    </p>
                </div>

            <form @submit.prevent="submitManual()" class="space-y-4 pt-1">
                <div class="space-y-1.5">
                    <label for="manual-ulid" class="text-xs font-semibold text-slate-700">
                        Nomor kunjungan tamu (ULID)
                    </label>
                    <input 
                        id="manual-ulid"
                        type="text" 
                        x-model="manualInput" 
                        placeholder="Masukkan 26 karakter nomor kunjungan"
                        autocomplete="off"
                        spellcheck="false"
                        class="w-full min-h-11 rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs sm:text-sm font-mono tracking-wider placeholder:text-slate-500 focus:outline-hidden focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                        :disabled="loading"
                    />
                </div>

                <button 
                    type="submit" 
                    :disabled="loading || !manualInput.trim()"
                    class="w-full min-h-11 py-2.5 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 disabled:bg-slate-300 disabled:text-slate-700 text-white text-xs font-bold shadow-xs transition flex items-center justify-center gap-2 cursor-pointer disabled:cursor-not-allowed focus:outline-hidden focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:ring-offset-2"
                >
                    <template x-if="loading">
                        <svg aria-hidden="true" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>
                    <span x-text="loading ? 'Memproses check-in...' : 'Verifikasi & check-in tamu'"></span>
                </button>
            </form>
            </div>

            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-600 text-[11px] leading-relaxed space-y-1">
                <span class="font-semibold block text-slate-700">Petunjuk petugas:</span>
                <p>1. Status tamu yang berhasil check-in akan otomatis berubah menjadi <strong>Tiba</strong>.</p>
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
        max-height: 480px;
        border-radius: 0.75rem;
    }
    #qr-reader img {
        margin: 0 auto;
        border-radius: 0.75rem;
    }
    #qr-shaded-region {
        border-color: rgba(59, 130, 246, 0.4) !important;
    }
</style>
@endpush
