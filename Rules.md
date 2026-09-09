# Rules.md

# Engineering Rules, Coding Conventions, and Contribution Guardrails

**Project:** Sistem Informasi Buku Tamu Digital Berbasis Web  
**Document version:** 1.0  
**Status:** Baseline engineering policy  
**Applies to:** Human contributors, AI coding assistants, reviewers, maintainers, and deployment operators

---

## 1. Purpose

Dokumen ini menetapkan aturan implementasi untuk menjaga aplikasi tetap konsisten, aman, mudah diaudit, dan tidak berkembang menjadi kumpulan kode, tampilan, atau fitur yang saling bertentangan.

Aturan dibuat khusus untuk sistem buku tamu digital dengan karakteristik berikut:

- aplikasi web berbasis Laravel/PHP;
- basis data relasional MySQL;
- aktor utama `Guest`, `Staff`, dan `Admin`;
- transaksi utama berupa kunjungan tamu;
- data kunjungan dan audit trail harus dapat ditelusuri;
- antarmuka ditujukan untuk lingkungan administrasi instansi, bukan produk SaaS generik;
- pengujian fungsional/black-box merupakan bagian wajib dari penerimaan rilis.

Dokumen ini bukan saran opsional. Setiap pelanggaran harus dicatat dan disetujui melalui keputusan teknis tertulis.

---

## 2. Normative Language

Istilah berikut digunakan secara konsisten:

- **WAJIB / MUST**: tidak boleh dilanggar.
- **DILARANG / MUST NOT**: tindakan tidak diizinkan.
- **SEBAIKNYA / SHOULD**: berlaku secara default; pengecualian harus memiliki alasan teknis.
- **BOLEH / MAY**: opsional dan tidak menjadi persyaratan rilis.

---

## 3. Documentation as Source of Truth

Setiap dokumen mengatur ruang lingkup yang berbeda:

| Dokumen | Otoritas utama |
|---|---|
| `PRD.md` | Scope produk, tujuan, aktor, fitur, acceptance criteria, dan KPI |
| `Architecture.md` | Stack, pembagian modul, komunikasi antarlapisan, deployment, dan integrasi |
| `Design.md` | Alur interaksi, bahasa antarmuka, visual system, accessibility, dan responsive behavior |
| `Schema.md` | Struktur penyimpanan, tabel, relasi, constraint, dan indexing |
| `Rules.md` | Cara kode ditulis, diuji, direview, dan diubah |

Aturan konflik:

1. Kontributor DILARANG menebak keputusan ketika dua dokumen bertentangan.
2. Konflik harus dicatat sebagai issue atau Architecture Decision Record (ADR).
3. Perubahan implementasi yang mengubah perilaku produk WAJIB disertai pembaruan dokumen terkait.
4. Kode yang berjalan tidak otomatis menjadi sumber kebenaran apabila bertentangan dengan dokumen yang telah disetujui.
5. Komentar kode tidak boleh digunakan untuk menggantikan dokumentasi arsitektur atau keputusan domain.

---

## 4. Technology Baseline

Baseline proyek:

- PHP 8+ sesuai versi yang dikunci proyek;
- Laravel sesuai `composer.json` dan `composer.lock`;
- MySQL 8+;
- HTML5, CSS, dan JavaScript;
- satu framework UI yang dipilih dan dicatat di `Architecture.md`;
- Nginx dan PHP-FPM untuk deployment produksi yang direkomendasikan.

Aturan versi:

1. Nomor versi aktual WAJIB mengikuti lock file, bukan asumsi atau ingatan kontributor.
2. Upgrade major version framework, PHP, MySQL, atau framework UI memerlukan persetujuan maintainer.
3. Dependency baru hanya boleh ditambahkan jika fungsi tersebut tidak dapat dipenuhi secara wajar oleh framework atau kode yang sudah ada.
4. Paket yang tidak dipakai harus dihapus melalui pull request terpisah agar dampaknya dapat ditinjau.
5. DILARANG mencampur Bootstrap dan Tailwind pada halaman yang sama. Proyek harus memilih satu sistem utilitas/komponen utama.
6. DILARANG menambahkan SPA framework hanya untuk membuat satu formulir atau tabel yang dapat ditangani Blade dan JavaScript ringan.

---

## 5. Repository and Module Boundaries

Struktur implementasi mengikuti konvensi Laravel. Apabila struktur repository aktual berbeda, `Architecture.md` harus diperbarui sebelum pemindahan massal dilakukan.

Struktur tanggung jawab yang diharapkan:

```text
app/
├── Actions/                 # Satu use case bisnis yang jelas
├── Enums/                   # Status dan nilai domain terbatas
├── Http/
│   ├── Controllers/         # Orkestrasi request/response; harus tipis
│   ├── Middleware/          # Pemeriksaan lintas request
│   ├── Requests/            # Validasi dan authorization request
│   └── Resources/           # Representasi response API bila digunakan
├── Models/                  # Eloquent model dan relasi
├── Notifications/           # Email/WhatsApp pada fase yang disetujui
├── Policies/                # Otorisasi per resource
├── Services/                # Koordinasi lintas use case/integrasi
└── Support/                 # Helper kecil yang benar-benar lintas domain

database/
├── factories/
├── migrations/
└── seeders/

resources/
├── css/
├── js/
└── views/

routes/
├── api.php
└── web.php

tests/
├── Feature/
└── Unit/
```

Aturan batas modul:

- Controller WAJIB menangani HTTP concern, bukan menampung seluruh logika bisnis.
- Validasi input WAJIB berada di `FormRequest` untuk alur non-trivial.
- Otorisasi WAJIB menggunakan middleware, policy, atau gate; tidak boleh hanya disembunyikan melalui UI.
- Transisi status kunjungan WAJIB melalui Action/Service khusus, bukan assignment bebas dari banyak controller.
- Model BOLEH memiliki query scope, cast, relasi, dan invariant lokal; model tidak boleh menjadi tempat seluruh workflow aplikasi.
- Repository pattern tidak dibuat secara otomatis. Repository hanya digunakan jika ada kebutuhan abstraksi yang nyata, misalnya beberapa data source atau query domain yang kompleks.
- `Support` DILARANG menjadi folder pembuangan fungsi yang tidak jelas pemilik domainnya.
- Tidak boleh ada business logic pada Blade template.

---

## 6. PHP Coding Conventions

### 6.1 Formatting

- Kode PHP WAJIB mengikuti PSR-12 dan formatter proyek.
- Laravel Pint digunakan sebagai formatter default apabila tersedia.
- File aplikasi baru SEBAIKNYA menggunakan `declare(strict_types=1);`, kecuali file framework-style tertentu seperti migration atau konfigurasi yang sengaja mengikuti format Laravel.
- Satu class utama per file.
- Tidak boleh ada spasi, debug output, atau karakter sebelum tag pembuka PHP.
- DILARANG meninggalkan `dd()`, `dump()`, `var_dump()`, `ray()`, atau debug statement di branch yang akan digabungkan.

Contoh:

```php
<?php

declare(strict_types=1);

namespace App\Actions\Visits;

final class ApproveVisit
{
    public function execute(Visit $visit, User $actor): Visit
    {
        // Domain transition and audit logging live here.
    }
}
```

### 6.2 Naming

| Elemen | Konvensi | Contoh |
|---|---|---|
| Class, interface, trait, enum | `PascalCase` | `ApproveVisit`, `VisitPolicy` |
| Method dan variable | `camelCase` | `approveVisit()`, `$visitDate` |
| Constant | `SCREAMING_SNAKE_CASE` | `MAX_EXPORT_ROWS` |
| Namespace | `PascalCase` | `App\Actions\Visits` |
| Boolean | Awalan semantik | `$isActive`, `$hasPermission`, `$canApprove` |
| Collection plural | Bentuk jamak | `$visits`, `$departments` |
| Single entity singular | Bentuk tunggal | `$visit`, `$guest` |

Nama DILARANG terlalu umum apabila konteks domain tersedia. Hindari nama seperti `DataManager`, `CommonService`, `ProcessData`, `Helper`, atau `handleThing`.

Gunakan nama berdasarkan outcome bisnis:

```php
ApproveVisit
CompleteVisit
CancelVisit
GenerateVisitReport
RegisterGuestVisit
```

Bukan:

```php
VisitManager
DataProcessor
GeneralService
```

### 6.3 Functions and Methods

- Satu method harus memiliki satu tujuan yang dapat dijelaskan dengan satu kalimat.
- Method publik WAJIB memiliki nama yang menjelaskan tindakan atau hasil.
- Hindari lebih dari tiga parameter primitive. Gunakan DTO/value object ketika data input mulai kompleks.
- Return type WAJIB ditulis, kecuali keterbatasan framework mencegahnya.
- DILARANG menggunakan array tanpa bentuk yang jelas untuk payload domain kompleks.
- DILARANG menangkap `Throwable` lalu mengabaikannya.
- Exception harus diterjemahkan menjadi respons pengguna pada boundary HTTP, bukan disembunyikan di semua lapisan.

### 6.4 Enums and Magic Values

Nilai status, role, tipe laporan, dan aksi audit DILARANG ditulis sebagai magic string yang tersebar.

Gunakan PHP backed enum atau constant terpusat:

```php
enum VisitStatus: string
{
    case Waiting = 'waiting';
    case Approved = 'approved';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
```

Contoh yang dilarang:

```php
if ($visit->status === 'approve') {
    // Typo cannot be detected reliably.
}
```

---

## 7. Laravel-Specific Rules

### 7.1 Controllers

Controller harus tipis. Tanggung jawab yang diizinkan:

1. menerima request;
2. memanggil validasi/authorization;
3. mendelegasikan use case ke Action/Service;
4. mengubah hasil menjadi response atau redirect.

Controller DILARANG:

- membuat query panjang bercabang-cabang;
- mengubah beberapa tabel tanpa transaction boundary;
- menentukan aturan transisi status;
- membangun file laporan secara langsung;
- memanggil API pihak ketiga tanpa adapter/service;
- mengandung HTML string.

### 7.2 Form Requests

- Setiap form administrasi atau registrasi kunjungan WAJIB memiliki server-side validation.
- Pesan validasi untuk pengguna ditulis dalam Bahasa Indonesia yang jelas.
- `authorize()` tidak boleh selalu `true` untuk operasi admin tanpa alasan.
- Aturan frontend hanya memperbaiki pengalaman; backend tetap menjadi otoritas validasi.

### 7.3 Models and Mass Assignment

- Gunakan `$fillable` atau `$guarded` secara sengaja.
- DILARANG memakai `$guarded = []` pada model yang menerima input pengguna tanpa pemetaan atribut eksplisit.
- Gunakan casts untuk tanggal, boolean, dan enum.
- Relasi Eloquent harus memiliki return type yang sesuai.
- Hindari observer untuk workflow utama yang sulit dilacak. Efek penting seperti audit log sebaiknya eksplisit pada Action/Service.

### 7.4 Authorization

- Route admin WAJIB dilindungi middleware autentikasi dan pemeriksaan role/policy.
- Tombol yang disembunyikan di UI bukan kontrol keamanan.
- Policy digunakan untuk tindakan terhadap resource: `view`, `create`, `approve`, `complete`, `cancel`, `export`.
- Admin tidak otomatis boleh melakukan tindakan yang merusak audit trail.
- Staff hanya mendapat izin minimum sesuai tugas operasional.

### 7.5 Configuration

- Nilai yang berbeda antar environment diletakkan pada `.env` dan dipetakan melalui file `config/*.php`.
- Pemanggilan `env()` hanya di file konfigurasi.
- DILARANG membaca `env()` langsung dari controller, service, model, atau view.
- Config key menggunakan `snake_case` dan dikelompokkan berdasarkan concern.

---

## 8. Database Naming and Migration Rules

### 8.1 Naming

| Elemen database | Konvensi | Contoh |
|---|---|---|
| Table | plural `snake_case` | `visit_logs`, `departments` |
| Column | singular `snake_case` | `guest_id`, `check_in_time` |
| Foreign key | `<entity>_id` | `department_id` |
| Index | `<table>_<columns>_<type>` | `visits_visit_date_status_index` |
| Unique constraint | `<table>_<columns>_unique` | `users_email_unique` |
| Migration | timestamp + action | `create_visits_table` |

### 8.2 Migration Discipline

- Semua perubahan schema WAJIB melalui migration.
- DILARANG mengubah database produksi secara manual lalu menyesuaikan migration belakangan.
- Migration yang sudah berjalan di environment bersama tidak boleh diedit. Buat migration korektif baru.
- Setiap migration destruktif WAJIB memiliki rencana backup, rollback, dan migrasi data.
- `down()` harus aman dan masuk akal; apabila rollback tidak aman, keterbatasan tersebut harus didokumentasikan.
- Penambahan foreign key WAJIB mempertimbangkan data existing.
- Index harus dibuat berdasarkan query aktual, bukan ditambahkan ke semua kolom secara acak.
- DILARANG menggunakan `SELECT *` pada query laporan produksi.

### 8.3 Transactions

Database transaction WAJIB digunakan ketika satu use case mengubah lebih dari satu data yang harus konsisten, misalnya:

- perubahan status kunjungan dan penambahan `visit_logs`;
- registrasi kunjungan dan pembuatan bukti registrasi metadata;
- pembatalan kunjungan dan pengiriman event/notifikasi terkait.

External API tidak boleh dipanggil di tengah transaction database yang lama. Gunakan event/queue setelah commit.

### 8.4 Deletion Policy

- `visits` dan `visit_logs` adalah data historis/audit dan DILARANG di-hard-delete melalui UI operasional.
- `visit_logs` bersifat append-only: tidak boleh diedit atau dihapus oleh pengguna aplikasi.
- Department yang pernah digunakan pada kunjungan dinonaktifkan melalui `status`, bukan dihapus.
- User yang tidak lagi aktif dinonaktifkan; histori tindakan tetap dipertahankan.
- Penghapusan data pribadi karena kewajiban kebijakan/aturan harus melalui prosedur khusus yang tetap menjaga integritas audit, misalnya anonymization yang disetujui.

### 8.5 Query Rules

- Query daftar WAJIB memiliki pagination.
- Query yang menampilkan relasi harus mencegah N+1 dengan eager loading terukur.
- Filter tanggal menggunakan batas waktu yang eksplisit dan konsisten.
- Query pencarian tidak boleh mengekspose data lintas role tanpa policy.
- Export besar harus diproses secara streaming atau queue, bukan memuat seluruh tabel ke memory.

---

## 9. Open Schema Consistency Issues

`Schema.md` versi awal memiliki beberapa keputusan yang belum konsisten. Kontributor DILARANG menyelesaikannya dengan asumsi diam-diam.

### RUL-DB-001 — Tipe `visits.status`

Deskripsi tabel menyebut `ENUM`, sementara contoh DDL menggunakan `VARCHAR(30)`.

Keputusan yang direkomendasikan untuk dibahas:

- simpan sebagai `VARCHAR(30)`;
- gunakan PHP backed enum sebagai representasi aplikasi;
- tambahkan constraint/check yang sesuai jika disepakati.

Implementasi migration pertama harus menunggu keputusan yang dicatat di `Schema.md` atau ADR.

### RUL-DB-002 — Nullability `guests.phone`

Deskripsi tabel menyebut `NOT NULL`, sementara contoh DDL membolehkan `NULL`.

Tim harus menetapkan apakah nomor telepon wajib berdasarkan alur registrasi nyata dan kebutuhan notifikasi. Jangan membuat aturan validasi sebelum keputusan ditulis.

### RUL-DB-003 — Makna `visits.user_id`

`user_id` perlu ditetapkan secara eksplisit sebagai salah satu dari:

- akun tamu yang membuat kunjungan;
- petugas yang mencatat kunjungan;
- petugas yang terakhir memproses kunjungan.

Satu kolom tidak boleh memiliki tiga makna. Apabila diperlukan, gunakan nama terpisah seperti `registered_by_user_id` dan `processed_by_user_id` melalui revisi schema.

### RUL-DB-004 — Timestamps and timezone

Seluruh timestamp harus menggunakan satu strategi yang disetujui. Rekomendasi: simpan waktu dalam UTC dan tampilkan sesuai zona waktu instansi. Keputusan final harus dicatat sebelum implementasi produksi.

---

## 10. Domain Rules for Visits

### 10.1 Status State Machine

Status kunjungan baseline:

```text
waiting
approved
completed
cancelled
```

Transisi yang diizinkan:

```text
waiting  -> approved
waiting  -> cancelled
approved -> completed
approved -> cancelled
```

Transisi yang dilarang tanpa workflow koreksi khusus:

```text
completed -> waiting
completed -> approved
cancelled -> approved
cancelled -> completed
waiting   -> completed
```

Aturan:

1. Setiap perubahan status WAJIB mencatat actor, waktu, status lama, status baru, dan alasan bila relevan.
2. `completed` hanya boleh dibuat setelah proses kunjungan benar-benar selesai.
3. `cancelled` harus memiliki alasan singkat untuk kebutuhan audit operasional.
4. Perubahan status tidak boleh dilakukan dengan update bebas pada endpoint CRUD generik.
5. Koreksi data historis harus melalui prosedur admin khusus dan tetap meninggalkan audit log.

### 10.2 Check-in and Check-out

- `check_in_time` ditetapkan oleh server, bukan dipercaya dari jam perangkat klien.
- `check_out_time` tidak boleh lebih kecil dari `check_in_time`.
- Kunjungan berstatus `completed` harus memiliki `check_out_time`.
- Kunjungan `waiting` tidak boleh memiliki `check_out_time`.
- Pengubahan waktu secara manual hanya boleh dilakukan oleh role berwenang dan harus diaudit.

### 10.3 Guest Identity

- Aplikasi hanya mengumpulkan data yang diperlukan untuk pelayanan.
- `identity_number` tetap opsional sampai ada kebutuhan formal yang disetujui.
- Nomor telepon dinormalisasi sebelum disimpan.
- Email dinormalisasi dengan trimming dan lowercase pada bagian yang relevan.
- DILARANG menggabungkan dua tamu hanya berdasarkan nama yang sama.
- Deteksi duplikasi boleh menggunakan kombinasi nomor telepon, email, atau identitas, tetapi keputusan merge harus eksplisit.

### 10.4 Reports

- Laporan adalah hasil turunan dari data kunjungan, bukan sumber data utama.
- Angka pada dashboard dan export harus berasal dari definisi filter yang sama.
- Rentang tanggal, timezone, status yang dihitung, dan department filter harus terlihat pada hasil laporan.
- File laporan tidak boleh menjadi satu-satunya penyimpan hasil. Data sumber tetap berada pada tabel transaksi.
- Setiap export harus mencatat pembuat, waktu, tipe laporan, dan filter utama apabila metadata laporan disimpan.

---

## 11. Route and API Conventions

### 11.1 Web Routes

- URL menggunakan lowercase dan kebab-case.
- Route name menggunakan dot notation.
- Route dikelompokkan berdasarkan role dan modul.

Contoh:

```php
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:admin'])
    ->group(function (): void {
        Route::get('/visits', [VisitController::class, 'index'])
            ->name('visits.index');

        Route::post('/visits/{visit}/approve', ApproveVisitController::class)
            ->name('visits.approve');
    });
```

Hindari endpoint ambigu seperti:

```text
POST /process-data
POST /update-status
GET  /get-all
```

### 11.2 API Responses

Apabila API digunakan, response harus konsisten:

```json
{
  "data": {},
  "meta": {},
  "message": "Kunjungan berhasil didaftarkan."
}
```

Untuk error validasi:

```json
{
  "message": "Data yang diberikan belum valid.",
  "errors": {
    "department_id": ["Bagian tujuan wajib dipilih."]
  }
}
```

Aturan API:

- Gunakan status HTTP sesuai makna.
- DILARANG mengembalikan `200 OK` untuk semua error.
- Detail exception, SQL, stack trace, dan path server tidak boleh dikirim ke pengguna.
- Endpoint daftar harus memiliki pagination dan batas maksimum.
- Perubahan kontrak response yang sudah digunakan client memerlukan versioning atau migration plan.
- Operasi registrasi yang berisiko terkirim ulang SEBAIKNYA memiliki perlindungan duplicate submission/idempotency.

---

## 12. Frontend and UI Rules

### 12.1 General Direction

UI harus mencerminkan aplikasi administrasi pelayanan publik yang tenang, jelas, dan operasional.

DILARANG membuat tampilan “AI slop” atau dashboard template generik, termasuk:

- gradient dekoratif tanpa fungsi;
- glassmorphism berlebihan;
- kartu statistik dalam jumlah besar hanya untuk memenuhi ruang;
- ikon acak yang tidak menjelaskan tindakan;
- emoji sebagai ikon navigasi utama;
- ilustrasi stok generik pada setiap empty state;
- chart yang tidak mendukung keputusan operasional;
- teks pemasaran seperti “revolutionize your workflow” pada aplikasi internal;
- kombinasi warna baru yang tidak berasal dari design token;
- animasi masuk pada setiap card, baris tabel, atau field form;
- copywriting yang terdengar seperti landing page startup.

Setiap elemen visual harus menjawab setidaknya satu kebutuhan: navigasi, status, input, feedback, prioritas, atau penjelasan.

### 12.2 Design Tokens

Warna baseline mengikuti `Design.md`:

```text
Primary:   #12355B
Secondary: #F3F4F6
Success:   #2E8B57
Warning:   #D97706
```

- Warna status harus konsisten pada seluruh halaman.
- Jangan menggunakan success green untuk tombol biasa.
- Warning amber bukan dekorasi.
- Error harus memiliki token tersendiri yang disepakati, bukan warna acak per halaman.
- Kontras warna harus memenuhi kebutuhan aksesibilitas dasar.

### 12.3 Components

- Komponen yang sama tidak boleh diimplementasikan ulang dengan markup berbeda di banyak halaman.
- Button harus memiliki variant terbatas: primary, secondary, danger, dan text/ghost jika benar-benar diperlukan.
- Modal tidak digunakan untuk form panjang atau workflow utama.
- Konfirmasi hanya diminta untuk tindakan berdampak, bukan setiap klik.
- Table harus memiliki empty state, loading state, error state, pagination, dan responsive behavior.
- Badge status harus menampilkan teks, tidak hanya warna.
- Form field WAJIB memiliki label tetap; placeholder bukan pengganti label.

### 12.4 Forms

- Form registrasi tamu mengikuti alur bertahap hanya jika pembagian langkah benar-benar mengurangi beban kognitif.
- Field disusun berdasarkan urutan operasional, bukan urutan tabel database.
- Required field ditandai jelas.
- Error ditampilkan dekat field terkait.
- Data yang telah valid tidak boleh hilang setelah satu field lain gagal.
- Tombol submit harus mencegah klik ganda saat request berlangsung.
- Default value tidak boleh mengarahkan pengguna pada pilihan sensitif tanpa sadar.

### 12.5 Copywriting

Gunakan Bahasa Indonesia operasional yang langsung.

Baik:

```text
Kunjungan berhasil didaftarkan.
Bagian tujuan wajib dipilih.
Belum ada kunjungan pada periode ini.
```

Hindari:

```text
Oops! Something went wrong.
Unlock seamless visitor experiences.
Data tidak ditemukan!!!
```

- Satu istilah harus dipakai konsisten. Pilih `kunjungan`, bukan bergantian dengan `booking`, `entry`, dan `appointment` tanpa alasan.
- Pilih `bagian tujuan` atau `unit tujuan`, lalu gunakan konsisten.
- Jangan menerjemahkan istilah teknis ke pengguna apabila tidak membantu tugas mereka.

### 12.6 Responsive Behavior

- Halaman tamu diprioritaskan untuk mobile satu kolom.
- Halaman admin diprioritaskan untuk desktop, tetapi tetap dapat digunakan pada tablet.
- Tabel boleh horizontal scroll pada viewport sempit; data penting tidak boleh hilang tanpa alternatif.
- Target sentuh harus cukup besar untuk penggunaan di meja resepsionis.
- DILARANG menyembunyikan tindakan kritis hanya pada hover.

---

## 13. JavaScript and CSS Conventions

### 13.1 JavaScript

- Variable dan function menggunakan `camelCase`.
- Class/component menggunakan `PascalCase`.
- Constant menggunakan `SCREAMING_SNAKE_CASE` untuk nilai global yang benar-benar konstan.
- Hindari global mutable state.
- DILARANG menyimpan token sensitif atau data pribadi persisten di `localStorage` tanpa keputusan keamanan.
- DOM selector menggunakan `data-*` attribute untuk behavior, bukan bergantung pada class visual.
- Request asynchronous harus memiliki loading, success, dan error handling.
- Tidak boleh menelan promise rejection.

### 13.2 CSS

- Gunakan design token/CSS variable untuk warna, spacing, radius, dan shadow yang berulang.
- DILARANG menggunakan inline style untuk styling reguler.
- Hindari specificity war dan `!important`; penggunaan harus dijelaskan jika tidak dapat dihindari.
- Class CSS harus menggambarkan komponen atau tujuan, bukan bentuk sementara seperti `.blue-box-2`.
- Jangan menambahkan shadow, border radius, atau gradient yang berbeda pada setiap halaman.
- Satu framework UI harus menjadi baseline; custom CSS hanya untuk kebutuhan yang tidak terlayani.

---

## 14. Security Rules

### 14.1 Secrets

DILARANG:

- commit `.env` produksi;
- menyimpan password, token WhatsApp, API key, atau credential database di source code;
- menaruh secret pada JavaScript client;
- mengirim secret melalui log, screenshot, issue publik, atau prompt AI.

Gunakan secret/environment management yang sesuai deployment.

### 14.2 Authentication and Sessions

- Password wajib di-hash menggunakan mekanisme Laravel yang didukung.
- Login admin dan staff harus memiliki rate limiting.
- Session cookie produksi harus menggunakan konfigurasi secure yang sesuai HTTPS.
- Regenerasi session dilakukan setelah login.
- Logout harus menginvalidasi session.
- Pesan login gagal tidak boleh mengungkap apakah email tertentu terdaftar.

### 14.3 Input and Output Safety

- Semua input dianggap tidak tepercaya.
- Gunakan parameter binding/Eloquent; DILARANG menyusun SQL dari concatenation input.
- Output pada Blade harus di-escape secara default.
- Raw HTML hanya diizinkan untuk konten tepercaya dan telah disanitasi.
- CSRF protection tidak boleh dinonaktifkan untuk route web tanpa alasan dan mitigasi yang terdokumentasi.
- Upload file, jika ditambahkan, wajib membatasi MIME type, ukuran, nama, lokasi, dan hak akses.

### 14.4 Personal Data

Data tamu dapat mencakup nama, kontak, instansi, alamat, dan identitas. Aturan minimum:

- Kumpulkan hanya data yang dibutuhkan.
- Jangan mencatat password, identity number penuh, email, atau nomor telepon pada application log.
- Mask data sensitif pada layar yang tidak memerlukan detail penuh.
- Export data tamu hanya tersedia untuk role berwenang.
- Link export tidak boleh dapat ditebak dan diakses publik.
- Backup harus memiliki kontrol akses yang setara dengan database utama.
- Data contoh pada development/test tidak boleh menggunakan data pribadi nyata.

### 14.5 Third-Party Notification

Integrasi WhatsApp/email adalah fitur fase lanjutan dan tidak boleh ditambahkan diam-diam.

Sebelum integrasi:

1. provider dan biaya disetujui;
2. data yang dikirim didefinisikan;
3. consent/kebijakan penggunaan ditinjau;
4. retry dan failure behavior ditentukan;
5. secret storage ditentukan;
6. pesan notifikasi tidak mengandung data berlebih;
7. kegagalan notifikasi tidak merusak transaksi kunjungan utama.

---

## 15. Logging, Audit, and Observability

- Gunakan level log secara benar: `debug`, `info`, `warning`, `error`, `critical`.
- Log harus memiliki context terstruktur, misalnya `visit_id`, `actor_id`, dan correlation/request ID jika tersedia.
- Jangan log seluruh request body registrasi tamu.
- Exception harus dicatat sekali pada boundary yang tepat; hindari duplikasi log di setiap lapisan.
- Audit log domain berbeda dari application log. Audit log harus menyimpan perubahan bisnis yang relevan dan tidak bergantung pada retention log server.
- Perubahan role, status user, status kunjungan, dan export sensitif harus dapat diaudit.
- DILARANG menulis “success” pada log sebelum transaksi benar-benar commit.

Contoh context yang aman:

```php
Log::info('Visit approved', [
    'visit_id' => $visit->id,
    'actor_id' => $actor->id,
]);
```

Contoh yang dilarang:

```php
Log::info('Guest payload', $request->all());
```

---

## 16. Error Handling Rules

- Pengguna mendapat pesan yang dapat ditindaklanjuti.
- Tim teknis mendapat detail melalui log, bukan melalui layar pengguna.
- Jangan mengubah exception menjadi `null` atau `false` tanpa konteks.
- Error integrasi pihak ketiga harus dibedakan dari error transaksi utama.
- Validation error menggunakan 422 untuk API.
- Unauthorized menggunakan 401; forbidden menggunakan 403.
- Not found menggunakan 404 tanpa membocorkan resource lintas organisasi/role.
- Conflict transisi status dapat menggunakan 409 untuk API.
- Halaman error produksi tidak boleh menampilkan stack trace.

---

## 17. Testing Rules

Referensi proyek menempatkan pengujian black-box sebagai validasi fungsi sistem. Implementasi proyek ini menggabungkan pengujian otomatis dengan acceptance/black-box testing.

### 17.1 Required Test Layers

1. **Unit test** untuk aturan domain murni, formatter, enum, dan value object.
2. **Feature test** untuk route, authorization, validation, database, dan workflow.
3. **Black-box/UAT checklist** untuk alur pengguna dari antarmuka.
4. **Integration test** untuk provider eksternal ketika integrasi diaktifkan.

### 17.2 Critical Scenarios

Minimal test berikut WAJIB tersedia sebelum MVP dinyatakan siap:

- tamu dapat mengirim registrasi valid;
- registrasi tidak valid ditolak dan data valid tetap ditampilkan;
- server menentukan waktu kunjungan sesuai kebijakan;
- pengguna tanpa izin tidak dapat membuka dashboard admin;
- staff/admin dapat melihat daftar kunjungan sesuai hak akses;
- transisi `waiting -> approved` berhasil dan membuat audit log;
- transisi status yang tidak sah ditolak;
- kunjungan dapat diselesaikan dengan `check_out_time` valid;
- pembatalan mencatat alasan dan actor;
- pencarian dan filter periode menghasilkan data yang tepat;
- export menghormati filter dan authorization;
- department nonaktif tidak dapat dipilih untuk kunjungan baru;
- data kunjungan historis tetap dapat dibaca setelah user/department dinonaktifkan;
- double submit tidak membuat duplikasi yang tidak diinginkan;
- error provider notifikasi tidak menghilangkan data kunjungan.

### 17.3 Test Naming

Gunakan nama yang menjelaskan behavior:

```php
public function test_admin_can_approve_a_waiting_visit(): void
public function test_staff_cannot_reopen_a_completed_visit(): void
public function test_guest_registration_rejects_an_inactive_department(): void
```

Hindari:

```php
public function testVisit(): void
public function testData(): void
```

### 17.4 Test Data

- Gunakan factory dan seeder khusus development/test.
- DILARANG menyalin data produksi ke test lokal tanpa anonymization resmi.
- Test harus independen dan dapat dijalankan berulang.
- Jangan bergantung pada urutan eksekusi test.
- Waktu pada test harus dibekukan ketika menguji tanggal/timestamp.
- External API harus di-fake pada test reguler.

### 17.5 Test Integrity

- Kontributor atau AI DILARANG menyatakan test lulus jika command belum dijalankan.
- DILARANG menghapus test hanya agar pipeline hijau.
- Perbaiki kode atau revisi requirement secara eksplisit; jangan melemahkan assertion diam-diam.
- Setiap bug fix harus memiliki regression test bila layak diotomatisasi.

---

## 18. Performance Rules

- Daftar kunjungan harus menggunakan pagination server-side.
- Dashboard tidak boleh menjalankan query agregasi berat berulang tanpa strategi cache atau precomputation.
- Cache hanya boleh digunakan jika invalidation behavior jelas.
- Jangan cache data yang berbeda per role menggunakan key global.
- Hindari loading seluruh riwayat kunjungan hanya untuk menghitung ringkasan.
- Export besar dijalankan melalui job/streaming setelah batas yang disepakati.
- Index baru harus dikaitkan dengan query yang terukur.
- Optimasi tidak boleh mengorbankan authorization atau akurasi laporan.

Target performa detail mengikuti KPI di `PRD.md`; perubahan target harus dilakukan di PRD, bukan hanya pada komentar kode.

---

## 19. Git and Branch Rules

### 19.1 Branch Names

Gunakan pola:

```text
feature/visit-registration
fix/duplicate-visit-submit
refactor/visit-status-action
chore/update-pint-config
docs/update-schema-rules
```

### 19.2 Commit Messages

Commit harus kecil, dapat dipahami, dan fokus.

Format yang disarankan:

```text
feat(visits): add approval transition and audit log
fix(reports): apply department filter to CSV export
test(auth): cover staff access restrictions
docs(schema): clarify visit actor columns
```

Dilarang:

```text
update
fix bug
final
changes
AI update
```

### 19.3 Pull Requests

Setiap PR harus menjelaskan:

- masalah yang diselesaikan;
- perubahan perilaku;
- file/modul utama yang diubah;
- migration atau perubahan data;
- risiko keamanan dan kompatibilitas;
- test yang dijalankan beserta hasil nyata;
- screenshot untuk perubahan UI;
- dokumen yang diperbarui;
- rollback plan jika perubahan berisiko.

Satu PR sebaiknya tidak mencampur feature, refactor besar, dependency upgrade, dan restyling menyeluruh.

---

## 20. Code Review Rules

Reviewer memeriksa lebih dari sekadar format.

Checklist minimum:

- scope sesuai PRD;
- tidak ada perubahan behavior tersembunyi;
- authorization berada di server;
- aturan domain tidak tersebar;
- migration aman;
- query tidak menimbulkan N+1 atau export tanpa batas;
- data pribadi tidak bocor melalui log/response;
- UI sesuai design system dan tidak generik;
- test menguji behavior, bukan hanya baris kode;
- dokumentasi sesuai implementasi;
- error handling tidak menyembunyikan kegagalan;
- tidak ada secret atau debug artifact.

Approval tidak boleh diberikan hanya karena aplikasi “berjalan di laptop pengembang”.

---

## 21. Protected Areas and Change Permission Matrix

### 21.1 Changes Allowed Without Special Approval

Tetap memerlukan review normal:

- koreksi typo dokumentasi tanpa mengubah makna;
- penambahan test untuk behavior yang sudah disetujui;
- perbaikan bug lokal tanpa perubahan kontrak publik;
- perbaikan accessibility yang tidak mengubah workflow;
- refactor kecil dengan output dan API tetap sama;
- penghapusan dead code yang terbukti tidak digunakan dan memiliki test coverage.

### 21.2 Changes Requiring Explicit Maintainer Approval

- tabel, kolom, foreign key, index, dan data retention;
- role, permission, policy, dan middleware autentikasi;
- state machine status kunjungan;
- route publik atau kontrak API;
- format laporan resmi;
- dependency baru atau upgrade major/minor berisiko;
- framework UI atau design token utama;
- integrasi WhatsApp, email, QR, cloud storage, atau provider lain;
- CI/CD, Docker, Nginx, PHP-FPM, dan konfigurasi produksi;
- mekanisme backup/restore;
- perubahan timezone;
- penghapusan atau anonymization data;
- bulk rewrite atau pemindahan struktur folder besar;
- penggantian Laravel/MySQL atau pola arsitektur utama.

### 21.3 Changes Strictly Prohibited

Tanpa pengecualian dalam kontribusi biasa:

- commit secret, credential, production dump, atau `.env` produksi;
- bypass authorization untuk “sementara”;
- menonaktifkan CSRF/rate limit tanpa mitigasi disetujui;
- hard-delete `visit_logs` dari aplikasi operasional;
- mengubah status kunjungan langsung di database produksi;
- menyimpan password dalam plaintext;
- mencatat data pribadi lengkap dalam log;
- mengklaim test/build berhasil tanpa menjalankannya;
- menghapus test atau menurunkan assertion agar pipeline lolos;
- menambahkan backdoor, akun admin tersembunyi, atau universal password;
- menggunakan data produksi sebagai fixture publik;
- menghasilkan SQL dari concatenation input pengguna;
- mencampur dua framework UI tanpa keputusan arsitektur;
- mengubah file dokumentasi untuk membenarkan bug setelah implementasi tanpa review produk.

---

## 22. AI Contributor Guardrails

AI coding assistant diperlakukan sebagai kontributor tanpa otoritas produk. AI tidak boleh membuat keputusan lintas domain secara mandiri.

### 22.1 Required Pre-Change Behavior

Sebelum mengubah kode, AI WAJIB:

1. membaca dokumen yang relevan;
2. membaca file target dan test terkait;
3. menyatakan asumsi yang belum didukung;
4. membuat rencana perubahan kecil dan spesifik;
5. mengidentifikasi apakah area tersebut dilindungi;
6. tidak mengarang table, route, role, field, service, atau provider yang belum ada.

### 22.2 Minimal-Diff Rule

- AI harus membuat perubahan sekecil mungkin untuk memenuhi requirement.
- DILARANG menulis ulang seluruh file hanya untuk mengubah satu method.
- DILARANG melakukan restyling massal saat tugasnya memperbaiki validasi.
- DILARANG mengganti pattern yang sudah bekerja dengan pattern favorit model tanpa kebutuhan.
- DILARANG menambahkan abstraction, interface, repository, factory, atau event hanya agar kode terlihat “enterprise”.

### 22.3 No Fabrication Rule

AI DILARANG:

- menyatakan class/function tersedia tanpa memeriksa repository;
- membuat hasil test palsu;
- menyebut migration berhasil tanpa menjalankannya;
- mengasumsikan provider WhatsApp tertentu;
- mengisi nilai konfigurasi produksi;
- menciptakan requirement baru dari pola aplikasi lain;
- menganggap mockup jurnal sebagai spesifikasi pixel-perfect;
- membuat sample statistik seolah-olah berasal dari data nyata.

### 22.4 Design-Specific AI Rules

AI yang mengubah UI WAJIB mematuhi `Design.md` dan aturan berikut:

- tidak menambah hero marketing pada halaman administrasi;
- tidak membuat semua panel menjadi rounded card;
- tidak menambah gradient, glow, glass, floating shape, atau decorative blob;
- tidak mengisi dashboard dengan angka vanity;
- tidak menggunakan copy generik yang terdengar diproduksi AI;
- tidak mengubah warna status demi estetika;
- tidak menggunakan ikon berbeda untuk tindakan yang sama;
- harus menunjukkan empty, loading, validation, success, dan error state yang nyata.

### 22.5 Security and Privacy for AI

DILARANG memasukkan ke prompt AI:

- `.env`;
- API key;
- database dump produksi;
- data identitas tamu;
- log yang memuat kontak pribadi;
- private key/certificate;
- password atau session token.

Gunakan data sintetis dan potongan kode minimum.

### 22.6 Required AI Handoff

Setelah perubahan, AI harus memberikan:

```text
Summary:
- Perubahan yang dibuat.

Files changed:
- Daftar file.

Behavior affected:
- Perilaku yang berubah/tetap.

Tests executed:
- Command nyata dan hasil.

Not executed:
- Test/build yang belum dapat dijalankan.

Assumptions or open issues:
- Keputusan yang masih memerlukan manusia.
```

AI harus berhenti dan meminta persetujuan apabila tugas menyentuh protected area pada bagian 21.2.

---

## 23. Dependency Rules

- Dependency harus memiliki tujuan yang jelas dan maintainer aktif.
- Periksa lisensi sebelum penambahan.
- Jangan menambahkan package hanya untuk satu helper sederhana.
- Lock file WAJIB ikut berubah saat dependency berubah.
- Dependency upgrade harus menyertakan changelog impact dan test result.
- DILARANG menjalankan update seluruh dependency tanpa scope.
- Security patch boleh diprioritaskan, tetapi tetap harus diuji.
- Wrapper/adaptor digunakan untuk third-party API agar domain tidak tergantung pada SDK provider secara langsung.

---

## 24. Seeders, Factories, and Demo Data

- Seeder produksi hanya berisi data referensi yang disetujui, seperti role dasar.
- Seeder tidak boleh membuat akun admin dengan password default yang dapat ditebak.
- Akun bootstrap admin harus dibuat melalui prosedur deployment aman.
- Factory menggunakan data sintetis.
- Demo data harus diberi label jelas dan tidak bercampur dengan produksi.
- Nama instansi, nomor telepon, dan email contoh tidak boleh menyerupai data personal nyata tanpa izin.

---

## 25. Documentation and Comments

- Komentar menjelaskan **mengapa**, bukan mengulang **apa** yang sudah terlihat dari kode.
- Workaround harus mencantumkan alasan dan kondisi penghapusan.
- TODO harus memiliki konteks atau issue reference; hindari TODO permanen tanpa pemilik.
- Public API dan workflow kompleks harus memiliki dokumentasi ringkas.
- Setiap perubahan schema memperbarui `Schema.md`.
- Setiap perubahan alur pengguna memperbarui `Design.md`.
- Setiap perubahan stack atau boundary modul memperbarui `Architecture.md`.
- Setiap perubahan fitur/acceptance criteria memperbarui `PRD.md`.

Contoh komentar yang baik:

```php
// Preserve the original department reference so historical reports remain auditable.
```

Contoh yang tidak berguna:

```php
// Set status to approved.
$visit->status = VisitStatus::Approved;
```

---

## 26. Release and Deployment Guardrails

Sebelum release:

- test suite lulus;
- formatter/linter lulus;
- asset production build berhasil;
- migration ditinjau dan diuji pada salinan data non-produksi;
- backup tersedia;
- environment variables terdokumentasi tanpa nilai secret;
- authorization critical path diuji;
- queue/scheduler dicek jika digunakan;
- error reporting produksi tidak menampilkan detail sensitif;
- rollback plan tersedia;
- smoke test selesai setelah deployment.

Deployment DILARANG dilakukan langsung dari branch lokal yang tidak teridentifikasi.

Migration dan deployment harus terkoordinasi agar kode baru tidak mengakses kolom yang belum tersedia atau menghapus kolom yang masih digunakan versi sebelumnya.

---

## 27. Definition of Done

Sebuah task hanya dianggap selesai apabila seluruh kondisi relevan terpenuhi:

- requirement dan acceptance criteria jelas;
- implementasi mengikuti architecture boundary;
- server-side validation dan authorization tersedia;
- domain invariant dijaga;
- migration aman jika ada;
- UI memiliki state lengkap dan sesuai design system;
- test otomatis ditambahkan/diperbarui;
- black-box scenario diperbarui untuk perubahan user-facing;
- formatter/linter/build/test dijalankan;
- tidak ada secret, debug code, atau data pribadi;
- dokumentasi yang terdampak diperbarui;
- reviewer memahami risiko dan rollback;
- tidak ada open issue kritis yang disembunyikan.

“Berjalan pada satu skenario manual” tidak cukup untuk memenuhi Definition of Done.

---

## 28. Recommended Local Quality Commands

Command aktual harus mengikuti script repository. Baseline Laravel yang lazim:

```bash
composer install
php artisan migrate --pretend
php artisan test
./vendor/bin/pint --test
npm ci
npm run build
```

Aturan:

- Jangan mengklaim command berhasil jika tidak dijalankan.
- `migrate:fresh` DILARANG dijalankan pada database yang berisi data penting.
- Production command memerlukan prosedur deployment, bukan copy-paste dari dokumentasi lokal.
- Jika suatu command tidak tersedia, catat dengan jujur dan sesuaikan script proyek melalui review.

---

## 29. Final Non-Negotiable Principles

1. Data kunjungan harus dapat ditelusuri.
2. Authorization selalu ditegakkan di server.
3. Audit log tidak boleh dapat dimanipulasi melalui UI biasa.
4. Perubahan schema tidak dilakukan dengan asumsi.
5. UI melayani pekerjaan operasional, bukan tren visual.
6. Data pribadi tidak boleh masuk log, source control, atau prompt AI.
7. Test result tidak boleh direkayasa.
8. Kontributor membuat perubahan minimum yang dapat dipahami dan dibatalkan.
9. Fitur fase lanjutan tidak boleh masuk ke MVP tanpa persetujuan scope.
10. Ketidakpastian harus dinyatakan; jangan disembunyikan dengan implementasi yang terlihat meyakinkan.
