# SI-TAMU (Sistem Informasi Buku Tamu Digital Berbasis Web)

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat&logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat&logo=php)](https://php.net)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-4.x-06B6D4?style=flat&logo=tailwindcss)](https://tailwindcss.com)
[![Alpine.js](https://img.shields.io/badge/Alpine.js-3.x-8BC0D0?style=flat&logo=alpinedotjs)](https://alpinejs.dev)
[![Pint](https://img.shields.io/badge/Code_Style-Laravel_Pint-000000?style=flat)](https://github.com/laravel/pint)
[![Tests](https://img.shields.io/badge/Tests-154_Passed-success?style=flat)](#testing--quality-assurance)

**SI-TAMU** adalah aplikasi buku tamu digital institusional modern yang dibangun menggunakan arsitektur **Modular Monolith** dengan framework **Laravel 12**, **Blade**, **Tailwind CSS v4**, dan **Alpine.js**. Sistem ini dirancang untuk menggantikan buku tamu manual berbasis kertas pada instansi pemerintah, organisasi publik, dan korporat, menghadirkan pencatatan kunjungan terstruktur, pelacakan status pelayanan real-time, auditabilitas mutasi yang ketat, serta ekspor laporan data yang aman.

---

## 1. Fitur Utama Sistem (MVP Scope)

### A. Registrasi Tamu Publik (Front-Desk Kiosk / Mobile)
- **Formulir 1-Halaman Mandiri**: Tamu dapat mendaftarkan kunjungan dengan cepat tanpa registrasi akun yang memberatkan.
- **Unit Kerja Dinamis**: Dropdown unit/bidang tujuan sinkron otomatis dengan status aktif departemen.
- **Idempotensi & Anti Double-Submit**: Mencegah pembuatan duplikat data kunjungan saat koneksi lambat menggunakan token idempotensi dan Alpine.js submit-disable state.
- **Honeypot & Anti-Spam**: Proteksi bot menggunakan field honeypot tak kasat mata dan rate limiting IP (15 request/menit).
- **Tanda Terima Digital**: Menampilkan referensi publik unik berbasis **ULID 26-karakter**, **QR Code tiket check-in** yang aman (token HMAC-SHA256), serta waktu dan instruksi kunjungan. Tiket dapat dicetak dengan kop surat resmi instansi.

### B. Operasional Back-Office (Petugas Loket & Resepsionis)
- **Dashboard Operasional**: Metrik KPI hari ini (Total Kunjungan, Terdaftar, Sedang Dilayani, Selesai) serta tabel kunjungan mutakhir.
- **Manajemen Alur Status Kunjungan**: Transisi siklus hidup kunjungan berurutan (`REGISTERED` → `ARRIVED` → `IN_SERVICE` → `COMPLETED` atau `CANCELLED`) yang divalidasi oleh Domain State Machine.
- **Riwayat & Timeline Status**: Pencatatan riwayat setiap perubahan status beserta stempel waktu dan petugas pelaksana.
- **Pencarian & Penyaringan Multi-Kriteria**: Filter rentang tanggal (boundary UTC), unit kerja tujuan, status kunjungan, dan pencarian teks bebas (nama, instansi, ULID).

### C. Administrasi Master Data & Hak Akses
- **Manajemen Unit Kerja (Departemen)**: CRUD unit/bidang dengan pola *soft-disable* (mencegah penghapusan jika telah memiliki riwayat kunjungan).
- **Manajemen Pengguna & RBAC**: Pengelolaan akun staf loket dan administrator, dengan proteksi diri admin dari deaktivasi/demosi mandiri (*admin self-protection rule*).
- **Audit Log Bisnis Imutabel**: Log aktivitas mutasi sistem bersifat *read-only* dan *append-only* dengan pencatatan diff JSON before/after, IP hash, dan Correlation Request ID.
- **Pengaturan Organisasi & Branding**: Pengaturan nama instansi, alamat, narasi privasi, zona waktu operasional (WIB/WITA/WIT), dan unggah logo resmi.

### D. Mesin Laporan & Ekspor Ephemeral (PDF & Excel)
- **Agregat Ringkasan & Preview Laporan**: Filter laporan konsisten dengan riwayat, visualisasi metrik agregat, dan pratinjau tabel terpaginasi.
- **Laporan PDF Formal & OpenXML XLSX**: PDF F4 portrait (215×330 mm) memakai kop instansi, metadata periode, ringkasan, tabel kunjungan 9 kolom, dan footer halaman dinamis ("Halaman N dari M") di setiap lembar; XLSX memakai header beku, filter kolom, lebar kolom, border sel data, dan sanitasi formula.
- **Format Ekspor Terbatas**: Ekspor baru hanya tersedia dalam PDF dan XLSX. CSV lama tetap dapat diunduh selama masa retensi.
- **Penyimpanan Ephemeral & Retensi 24 Jam**: Berkas ekspor disimpan di *private storage* dan otomatis dibersihkan oleh scheduler setelah 24 jam via command `php artisan sitamu:exports:cleanup`.

### E. Check-In QR Code & Survei Kepuasan Tamu (IKM)
- **Pemindai QR Code**: Petugas memverifikasi kedatangan tamu dengan memindai QR tiket melalui kamera perangkat (html5-qrcode + jsQR) atau memasukkan ULID secara manual, dilengkapi umpan balik suara dan tampilan hasil verifikasi real-time.
- **Tiket Tamu Siap Cetak**: Halaman cetak tiket berformat kartu dengan kop surat resmi instansi (mengikuti font kop yang sama dengan laporan PDF).
- **Survei Kepuasan (IKM)**: Setelah kunjungan berstatus *COMPLETED*, tamu dapat mengisi survei bintang 1–5 (satu kali per kunjungan) melalui tautan publik; hasilnya direkap pada dashboard **Indeks Kepuasan Layanan (IKM)** untuk admin, lengkap dengan skor rata-rata dan filter rating.

---

## 2. Struktur Arsitektur & Teknologi

Sistem menggunakan pola **Modular Monolith** dengan pemisahan layer tanggung jawab:

```text
app/
├── Domain/                 # Enterprise Rules, Enums, State Machine & Exceptions
│   ├── Access/Enums/       # RoleCode (ADMIN, STAFF)
│   ├── Reporting/Enums/    # ExportStatus (PENDING, PROCESSING, COMPLETED, FAILED, EXPIRED)
│   ├── Shared/Contracts/   # PublicIdGenerator interface
│   └── Visits/             # VisitStatus, Transition Rules & Custom Exceptions
├── Application/            # Use-Case Actions, Queries, DTOs, Services & Writers
│   ├── Audit/Actions/      # RecordAuditEventAction
│   ├── Reporting/          # ExportVisitsAction, VisitReportQuery, PdfReportWriter, XlsxReportWriter
│   └── Visits/             # RegisterVisitAction, ChangeVisitStatusAction, CheckInVisitViaQrAction, CorrectVisitDataAction, VisitQrTokenService
├── Infrastructure/         # ULID Generator, Letterhead (Kop Surat), File Storage, External Adapters
├── Models/                 # Eloquent Entities, Casts, Model Relations & Scopes
├── Policies/               # Otorisasi Hak Akses (Visit, Department, User, Report, Audit)
├── Http/                   # Controllers, FormRequests, Middlewares (Security & Active Check)
└── Console/Commands/       # CreateAdminCommand, CleanupExpiredExportsCommand
```

---

## 3. Persyaratan Sistem

- **PHP**: Versi 8.2 atau lebih baru
  - Ekstensi PHP yang dibutuhkan: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `xmlwriter`, `ctype`, `json`, `fileinfo`, `zip`, `gd`.
- **Database**: MariaDB 10.4+ atau MySQL 8.0+
- **Node.js**: Versi 18+ atau 20+ (LTS) & NPM
- **Web Server**: Apache (XAMPP) atau Nginx (Production)
- **Composer**: Versi 2.x

---

## 4. Panduan Instalasi Lokal (Local Setup)

### Langkah 1: Clone Repositori
```bash
git clone https://github.com/Reanaldy03/si-tamu.git
cd si-tamu
```

### Langkah 2: Instal Dependensi PHP & Frontend
```bash
composer install
npm install
```

### Langkah 3: Konfigurasi Environment (`.env`)
Salin berkas konfigurasi default:
```bash
cp .env.example .env
```
Buka `.env` dan sesuaikan pengaturan database Anda (contoh untuk XAMPP default):
```ini
APP_NAME="SI-TAMU"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sitamu
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
```

Buat database MySQL baru dengan nama `sitamu`:
```sql
CREATE DATABASE sitamu CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Langkah 4: Generate Application Key
```bash
php artisan key:generate
```

### Langkah 5: Jalankan Migrasi & Database Seeder
Perintah berikut akan membuat seluruh skema tabel, indeks, foreign keys, serta data awal peran (*ADMIN*, *STAFF*), permission, dan pengaturan organisasi:
```bash
php artisan migrate --seed
```

### Langkah 6: Tautkan Storage Simbolik
```bash
php artisan storage:link
```

### Langkah 7: Buat Akun Administrator Pertama
Gunakan perintah konsol interaktif yang aman berikut untuk membuat akun admin:
```bash
php artisan sitamu:admin:create
```
Masukkan nama lengkap, email (misal `admin@sitamu.local`), dan password admin yang kuat.

### Langkah 8: Kompilasi Aset Frontend
```bash
npm run build
```
*(Gunakan `npm run dev` untuk pengembangan lokal dengan hot module reloading).*

> **Catatan font kop PDF**: Ekspor PDF menyalin font kop resmi (*Haettenschweiler*, *Times New Roman*) dari sistem operasi (`C:/Windows/Fonts` atau `/usr/share/fonts/truetype/msttcorefonts`) ke `storage/app/private/fonts/` saat render pertama. Jika font sumber tidak tersedia, laporan tetap tercetak dengan fallback Helvetica/Times bawaan — tanpa error. Pastikan `storage/` dapat ditulis oleh pengguna web server.

### Langkah 9: Jalankan Server Pengembangan
```bash
php artisan serve
```
Akses aplikasi melalui browser:
- **Formulir Tamu Publik**: `http://127.0.0.1:8000/`
- **Portal Login Petugas & Admin**: `http://127.0.0.1:8000/login`
- **Survei Kepuasan Tamu**: `http://127.0.0.1:8000/visit/{publicId}/feedback`

> **Catatan kamera (Check-In QR)**: Pemindai QR memerlukan *secure context* — HTTPS, `localhost`, atau `127.0.0.1`. Jika aplikasi dibuka lewat IP LAN pada HTTP biasa, browser akan memblokir kamera; gunakan tombol *switch origin* pada halaman scanner atau aktifkan HTTPS.

---

## 5. Penjadwalan & Pembersihan Berkas Berkala

Aplikasi menyertakan pembersihan berkas ekspor laporan kedaluwarsa (retensi 24 jam).

Jalankan pembersihan secara manual:
```bash
php artisan sitamu:exports:cleanup
```

Pada server produksi, tambahkan Cron Job sistem untuk menjalankan scheduler Laravel setiap menit:
```cron
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

---

## 6. Testing & Quality Assurance

Aplikasi dilengkapi dengan rangkaian pengujian otomatis end-to-end (Unit, Feature, Policy, Security Smoke Test, dan 23 Skenario Black-Box Acceptance Test):

### Menjalankan Seluruh Test Suite
```bash
php artisan test
```
*Status: 154 passed (750 assertions) — 100% Green.*

### Menjalankan Black-Box Acceptance Test
```bash
php artisan test --filter=BlackBoxAcceptanceTest
```

### Menjalankan Security Smoke Test
```bash
php artisan test --filter=SecuritySmokeTest
```

### Memeriksa Standar Gaya Kode (Laravel Pint)
```bash
php vendor/bin/pint --test
```

---

## 7. Production Deployment Checklist

Sebelum mempublikasikan aplikasi ke lingkungan produksi:
1. Pastikan `APP_ENV=production` dan `APP_DEBUG=false` pada `.env`.
2. Generate Application Key dan pastikan koneksi database menggunakan kredensial khusus berhak akses terbatas.
3. Jalankan optimasi cache Laravel:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
4. Pastikan symlink storage terpasang: `php artisan storage:link`.
5. Pastikan folder `storage/` dan `bootstrap/cache/` memiliki izin tulis oleh pengguna web server (`chown -R www-data:www-data storage bootstrap/cache`).
6. Pasang SSL/TLS (HTTPS) wajib untuk menjamin enkripsi seluruh transmisi data kunjungan.
7. Konfigurasikan HTTP Security Headers pada Nginx (`X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, CSP).

---

## 8. Lisensi & Hak Cipta

Aplikasi dikembangkan di bawah lisensi open-source [MIT License](LICENSE).
Panduan pengembangan lengkap (arsitektur, konvensi, perintah, dan catatan teknis) tersedia pada berkas [`CLAUDE.md`](CLAUDE.md).

---

## 9. Kredit & Atribusi Aset

- **Logo instansi** (`public/logo.webp`, `public/assets/logo.webp`, `public/images/logo.webp`) adalah lambang resmi Pemerintah Kota Binjai yang digunakan untuk keperluan operasional aplikasi. Jika kamu memakai aplikasi ini untuk instansi lain, ganti logo tersebut dengan identitas instansimu (dapat diunggah melalui menu Pengaturan Organisasi).
- **Format kop surat, laporan PDF F4, dan tata letak XLSX** mengikuti dokumen resmi instansi asal (contoh referensi tidak disertakan dalam repositori ini).
- **Ikon & pustaka pihak ketiga**: [Laravel](https://laravel.com), [Tailwind CSS](https://tailwindcss.com), [Alpine.js](https://alpinejs.dev), [Chart.js](https://www.chartjs.org), [html5-qrcode](https://github.com/mebjas/html5-qrcode), [jsQR](https://github.com/cozmo/jsQR), [dompdf](https://github.com/dompdf/dompdf), [simple-qrcode](https://github.com/SimpleSoftwareIO/simple-qrcode) — masing-masing dengan lisensinya sendiri.
