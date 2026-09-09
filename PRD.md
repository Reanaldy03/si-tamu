# PRD.md — SI-TAMU

> **Product Requirements Document**  
> **Nama produk sementara:** SI-TAMU — Sistem Informasi Buku Tamu Digital Berbasis Web  
> **Versi dokumen:** 0.1 (Draft untuk review)  
> **Status:** Draft — menunggu validasi pemilik proyek  
> **Target domain:** Instansi pemerintahan / organisasi pelayanan publik  
> **Platform utama:** Web responsif  
> **Dokumen berikutnya setelah PRD disetujui:** `Architecture.md`

---

## 1. Ringkasan Eksekutif

SI-TAMU adalah aplikasi buku tamu digital berbasis web untuk menggantikan proses pencatatan kunjungan yang masih dilakukan menggunakan buku fisik, lembar kertas, atau rekap manual. Sistem ditujukan untuk mendukung proses registrasi tamu, pencatatan waktu kunjungan, pengelolaan tujuan/bidang yang dikunjungi, pencarian riwayat kunjungan, pemantauan oleh petugas/admin, dan pembuatan laporan secara lebih cepat, terstruktur, serta mudah diaudit.

Empat jurnal yang menjadi dasar proyek ini menunjukkan pola masalah yang konsisten pada instansi pemerintah:

1. pencatatan manual menimbulkan antrean dan memperlambat proses layanan;
2. data sulit dicari kembali dan rawan hilang/rusak;
3. data sering tidak konsisten karena ditulis secara manual;
4. rekap dan laporan membutuhkan waktu karena dilakukan ulang secara manual;
5. petugas tidak memiliki ringkasan statistik kunjungan yang langsung tersedia;
6. komunikasi kepada unit/pihak yang dituju belum otomatis;
7. proses digital perlu tetap sederhana agar dapat digunakan oleh petugas non-teknis dan tamu.

PRD ini menerjemahkan temuan tersebut menjadi produk baru yang bersifat **generalizable** untuk instansi pemerintah. Karena pemilik proyek belum menetapkan nama instansi tertentu, nama organisasi, logo, daftar unit/bidang, aturan data wajib, serta terminologi internal diperlakukan sebagai **konfigurasi**, bukan hard-coded.

### 1.1 Product Vision

> Menyediakan sistem penerimaan dan pencatatan kunjungan yang cepat, akurat, mudah digunakan, aman, serta menghasilkan data kunjungan yang dapat dicari, dilaporkan, dan dianalisis tanpa ketergantungan pada buku tamu fisik.

### 1.2 Prinsip Produk

Produk harus dibangun dengan prinsip berikut:

- **Frictionless guest registration** — tamu tidak dipaksa membuat akun hanya untuk mencatat kunjungan.
- **Minimal data collection** — hanya meminta data pribadi yang benar-benar dibutuhkan untuk kepentingan operasional.
- **Fast front-office workflow** — langkah yang dilakukan tamu dan petugas harus sesingkat mungkin.
- **Traceable operations** — perubahan status, pengelolaan data, dan ekspor harus dapat ditelusuri.
- **Responsive by default** — dapat digunakan pada desktop resepsionis, tablet/kiosk, dan ponsel.
- **Configurable organization model** — nama instansi, logo, unit/bidang, dan beberapa field formulir dapat diubah tanpa modifikasi kode inti.
- **Progressive enhancement** — MVP menyelesaikan kebutuhan inti terlebih dahulu; fitur seperti WhatsApp, QR/barcode scanning, offline mode, dan integrasi keamanan masuk fase lanjutan.

---

## 2. Basis Referensi Jurnal dan Batas Interpretasi

PRD ini disusun dari sintesis empat sumber yang diunggah pengguna. Temuan jurnal dipakai sebagai **basis kebutuhan domain**, sedangkan detail seperti target SLA, RBAC granular, audit log, praktik keamanan aplikasi, dan prioritas MVP merupakan **keputusan produk/rekayasa yang diturunkan dari kebutuhan tersebut** dan perlu divalidasi pada iterasi berikutnya.

### 2.1 Sumber 1 — Sistem Informasi Buku Tamu DPRD Kabupaten Minahasa

Temuan relevan:

- pencatatan manual menyebabkan pencarian data tidak efisien, risiko kehilangan informasi, dan kesulitan merangkum kunjungan;
- sistem web yang dirancang menyediakan input data tamu digital, pencarian berdasarkan kriteria, dan laporan kunjungan otomatis;
- halaman admin menampilkan statistik dan data pengunjung hari berjalan;
- halaman rekapitulasi dapat mengekspor data ke Excel;
- pengembangan selanjutnya disarankan mencakup integrasi sistem keamanan gedung, notifikasi otomatis, statistik kunjungan, dan peningkatan keamanan data.

### 2.2 Sumber 2 — Buku Tamu Website Sekretariat DPRD Kabupaten Magetan

Temuan relevan:

- proses manual menimbulkan antrean dan pencarian data satu per satu memerlukan waktu lama;
- sistem memiliki aktor admin dan tamu;
- admin mengelola data unit/bagian, pengguna, buku tamu, riwayat, bukti registrasi, dan laporan;
- tamu dapat mengajukan kunjungan dan memperoleh bukti registrasi;
- implementasi menggunakan Laravel dan MySQL;
- pengujian Black Box digunakan untuk memastikan fungsi sistem berjalan sesuai spesifikasi.

### 2.3 Sumber 3 — Digitalisasi Buku Tamu Disdukcapil Kabupaten Pati

Temuan relevan:

- proses manual menimbulkan antrean, ketidakakuratan data, kesulitan menelusuri riwayat, dan kesulitan membuat laporan;
- kebutuhan utama mencakup self-service registration, pemindaian barcode, pembaruan status, riwayat terstruktur, dan laporan otomatis;
- implementasi mencakup pencatatan waktu otomatis, pengarahan tamu ke bidang tujuan, survei kepuasan, riwayat, pelaporan, dan autentikasi;
- kendala jaringan ditemukan dan offline mode disarankan sebagai pengembangan berikutnya;
- notifikasi digital antarbidang dan dashboard statistik juga diusulkan untuk fase lanjutan;
- sistem menggunakan PHP-Laravel, Bootstrap, dan MySQL.

### 2.4 Sumber 4 — Buku Tamu Bawaslu Situbondo dengan Notifikasi WhatsApp

Temuan relevan:

- sistem manual menyulitkan pencarian, menghasilkan data yang tidak konsisten, dan memiliki risiko kehilangan dokumen;
- sistem web mengintegrasikan WhatsApp API untuk pemberitahuan otomatis;
- laporan harian dan bulanan tersedia;
- form input menggunakan validasi;
- dashboard menampilkan ringkasan dan grafik kunjungan;
- pengujian bersama petugas menunjukkan penurunan waktu pencatatan lebih dari 50% dibanding proses manual pada studi tersebut;
- pengembangan lanjutan menyarankan autentikasi dua langkah dan backup otomatis.

### 2.5 Asumsi Produk yang Perlu Dikonfirmasi

Sebelum proyek masuk ke implementasi penuh, asumsi berikut perlu dikonfirmasi:

- sistem akan digunakan oleh satu instansi pada MVP;
- terdapat minimal satu meja/front-office atau petugas penerima tamu;
- instansi memiliki daftar unit/bidang yang dapat dipilih sebagai tujuan kunjungan;
- tamu dapat menggunakan formulir tanpa akun;
- admin/petugas wajib login;
- tidak ada integrasi dengan NIK, Dukcapil, access control, atau sistem eksternal pada MVP;
- WhatsApp API bukan ketergantungan MVP;
- sistem harus berjalan pada browser modern dan koneksi jaringan kantor biasa;
- field data pribadi dapat dikonfigurasi sesuai kebijakan instansi.

---

## 3. Scope Project

### 3.1 In-Scope MVP

MVP mencakup alur end-to-end berikut:

1. tamu membuka halaman registrasi;
2. tamu mengisi data identitas minimum dan keperluan;
3. sistem merekam waktu registrasi secara otomatis;
4. sistem membuat ID/nomor kunjungan unik;
5. petugas/admin dapat melihat kunjungan yang masuk;
6. petugas dapat mengubah status kunjungan;
7. petugas dapat mencari dan memfilter data kunjungan;
8. admin dapat melihat ringkasan statistik dasar;
9. admin dapat membuat dan mengekspor laporan;
10. admin dapat mengelola unit/bidang tujuan dan akun petugas;
11. sistem menyimpan jejak audit minimum untuk aktivitas sensitif;
12. sistem menyediakan backup dan prosedur pemulihan yang terdefinisi.

### 3.2 In-Scope Setelah MVP

Fase berikutnya dapat mencakup:

- QR/barcode untuk check-in cepat;
- WhatsApp notification;
- notifikasi internal real-time;
- survey kepuasan;
- offline/PWA mode;
- integrasi sistem keamanan/access control;
- 2FA untuk admin;
- advanced analytics;
- multi-location/multi-office;
- integrasi SSO;
- public appointment/pre-registration;
- visitor badge printing;
- API integrasi dengan sistem organisasi lain.

### 3.3 Out-of-Scope MVP

Hal berikut **tidak termasuk MVP**, kecuali pemilik proyek mengubah prioritas:

- aplikasi native Android/iOS;
- biometrik atau face recognition;
- OCR identitas/KTP;
- verifikasi identitas ke database pemerintah;
- integrasi CCTV;
- integrasi mesin gate/turnstile;
- integrasi langsung dengan sistem absensi pegawai;
- sistem antrean kompleks lintas layanan;
- pembayaran;
- tanda tangan digital tersertifikasi;
- AI untuk klasifikasi/analisis pengunjung;
- data warehouse skala enterprise;
- deployment multi-tenant untuk banyak instansi dalam satu instance.

---

## 4. Problem Statement

### 4.1 Masalah Utama

Proses buku tamu manual menciptakan bottleneck operasional di front-office. Data yang ditulis tangan tidak langsung tersedia untuk pencarian, pemantauan, atau pelaporan. Ketika jumlah kunjungan meningkat, petugas harus mencari arsip satu per satu, melakukan rekap ulang, dan berisiko kehilangan atau salah membaca data.

### 4.2 Dampak Operasional

Masalah manual dapat menimbulkan:

- waktu registrasi yang lebih lama;
- antrean pada jam sibuk;
- beban kerja administratif tinggi;
- data tidak lengkap atau tidak konsisten;
- duplikasi pencatatan;
- kesulitan mengetahui siapa yang sedang/baru saja berkunjung;
- sulit menemukan histori kunjungan berdasarkan nama, instansi, tujuan, atau tanggal;
- laporan harian/bulanan membutuhkan rekap manual;
- risiko buku fisik rusak, hilang, atau diakses tanpa kontrol;
- komunikasi kepada unit tujuan terlambat;
- minimnya data statistik untuk evaluasi layanan.

### 4.3 Problem Statement Formal

> Petugas front-office membutuhkan cara yang lebih cepat dan terstruktur untuk mencatat, mencari, memantau, dan melaporkan kunjungan; sementara tamu membutuhkan proses registrasi yang singkat dan mudah. Sistem saat ini yang berbasis pencatatan manual tidak memberikan konsistensi data, traceability, pencarian cepat, maupun reporting otomatis yang dibutuhkan untuk operasi layanan modern.

---

## 5. Goals dan Non-Goals

### 5.1 Product Goals

#### G-01 — Digitalisasi Pencatatan
Mengalihkan pencatatan kunjungan dari media fisik ke sistem digital terpusat.

#### G-02 — Mempercepat Registrasi
Mengurangi waktu yang diperlukan tamu untuk mengisi buku tamu dan mengurangi antrean.

#### G-03 — Meningkatkan Kualitas Data
Memastikan data wajib terisi, format data konsisten, waktu kunjungan tercatat otomatis, dan histori mudah ditelusuri.

#### G-04 — Mempermudah Pencarian
Memungkinkan petugas menemukan data kunjungan dengan filter/telusur dalam hitungan detik, bukan mencari arsip fisik.

#### G-05 — Mengotomatisasi Laporan
Menghasilkan laporan kunjungan berdasarkan rentang tanggal dan filter tanpa rekap manual ulang.

#### G-06 — Meningkatkan Visibilitas Operasional
Memberikan dashboard untuk melihat ringkasan kunjungan hari ini dan tren dasar.

#### G-07 — Menjaga Keamanan Data
Menerapkan autentikasi admin, otorisasi, validasi, logging, backup, dan kontrol akses terhadap data kunjungan.

#### G-08 — Menjadi Fondasi Pengembangan Lanjutan
Menyediakan model data dan alur yang memungkinkan fitur QR/barcode, WhatsApp, survey, offline mode, dan integrasi sistem pada fase selanjutnya.

### 5.2 Non-Goals MVP

MVP tidak bertujuan untuk:

- menggantikan seluruh sistem keamanan gedung;
- melakukan verifikasi legal identitas;
- menjadi CRM masyarakat;
- menjadi sistem antrean layanan lengkap;
- mengotomatiskan semua komunikasi antarunit;
- menyediakan analitik prediktif;
- menangani semua instansi dalam satu platform multi-tenant.

---

## 6. Stakeholders dan User Personas

### 6.1 Persona P1 — Tamu/Pengunjung

**Tujuan:** mendaftarkan kunjungan dengan cepat, mengetahui bahwa registrasi berhasil, dan menuju unit yang benar.

**Karakteristik:**

- dapat berasal dari masyarakat umum, instansi lain, organisasi, perusahaan, atau pejabat;
- kemampuan digital bervariasi;
- dapat menggunakan ponsel sendiri atau perangkat kiosk/resepsionis;
- tidak seharusnya membutuhkan pelatihan.

**Kebutuhan utama:**

- form sederhana;
- label yang jelas;
- validasi langsung;
- konfirmasi sukses;
- tidak perlu akun untuk MVP.

### 6.2 Persona P2 — Petugas Front-Office/Resepsionis

**Tujuan:** memantau tamu yang masuk, memverifikasi/menangani kunjungan, menemukan histori, dan membantu tamu jika diperlukan.

**Kebutuhan utama:**

- dashboard sederhana;
- daftar kunjungan hari ini;
- pencarian cepat;
- filter status;
- detail kunjungan;
- perubahan status;
- UI yang efektif untuk penggunaan rutin.

### 6.3 Persona P3 — Administrator Sistem

**Tujuan:** mengelola akun petugas, unit/bidang, konfigurasi dasar, data kunjungan, dan laporan.

**Kebutuhan utama:**

- kontrol akses;
- data master;
- export laporan;
- audit aktivitas;
- kemampuan mengoreksi data tertentu dengan jejak perubahan.

### 6.4 Persona P4 — Pimpinan/Manajemen

**Tujuan:** memperoleh ringkasan kunjungan dan laporan untuk evaluasi layanan.

**Kebutuhan utama:**

- laporan periodik;
- statistik dasar;
- filter berdasarkan waktu/unit;
- data yang konsisten dan dapat dipertanggungjawabkan.

### 6.5 Persona P5 — Unit/Bidang Tujuan (Fase Lanjutan)

**Tujuan:** menerima informasi bahwa tamu telah tiba dan mengetahui konteks kunjungan.

Pada MVP, peran ini belum memerlukan akun khusus. Pada fase lanjutan, unit tujuan dapat menerima notifikasi atau memiliki dashboard terbatas.

---

## 7. User Journey Utama

### 7.1 Journey A — Registrasi Tamu Mandiri

1. Tamu membuka halaman registrasi.
2. Sistem menampilkan identitas instansi dan instruksi singkat.
3. Tamu mengisi field wajib.
4. Sistem melakukan validasi client-side dan server-side.
5. Tamu memilih unit/bidang tujuan.
6. Tamu menuliskan maksud/keperluan.
7. Tamu menyetujui pemberitahuan privasi singkat.
8. Tamu menekan tombol **Daftar Kunjungan**.
9. Sistem mencatat waktu server secara otomatis.
10. Sistem menghasilkan `visit_id`/nomor registrasi unik.
11. Sistem menampilkan konfirmasi berhasil.
12. Kunjungan muncul di dashboard petugas.

### 7.2 Journey B — Petugas Memproses Kunjungan

1. Petugas login.
2. Dashboard menampilkan daftar tamu hari ini.
3. Petugas membuka detail kunjungan.
4. Petugas memverifikasi informasi bila dibutuhkan.
5. Petugas mengubah status, misalnya `REGISTERED` → `ARRIVED` → `IN_SERVICE` → `COMPLETED`.
6. Semua perubahan status dicatat beserta waktu dan aktor.

### 7.3 Journey C — Mencari Riwayat

1. Petugas membuka menu **Riwayat Kunjungan**.
2. Petugas memasukkan nama/instansi/nomor kunjungan atau memilih filter.
3. Sistem menampilkan hasil yang sesuai.
4. Petugas dapat membuka detail tanpa mengubah data.

### 7.4 Journey D — Membuat Laporan

1. Admin membuka menu **Laporan**.
2. Admin menentukan rentang tanggal.
3. Admin dapat memfilter unit/bidang dan status.
4. Sistem menampilkan preview ringkasan.
5. Admin mengekspor ke format tabular (minimum CSV/XLSX; format final diputuskan di Architecture/implementation).
6. Sistem mencatat aktivitas ekspor di audit log.

---

## 8. Functional Requirements — MVP

> Prioritas menggunakan: **P0 = wajib untuk MVP**, **P1 = penting tetapi dapat menyusul bila scope harus dipersempit**, **P2 = enhancement**.

### FR-001 — Halaman Registrasi Tamu Publik

**Priority:** P0  
**Actor:** Tamu

Sistem harus menyediakan halaman registrasi yang dapat diakses tanpa login.

**Field minimum yang direkomendasikan:**

- Nama lengkap — wajib;
- Asal instansi/organisasi — wajib atau dapat diisi `Pribadi/Umum`;
- Nomor kontak — configurable; default opsional untuk meminimalkan data pribadi;
- Unit/bidang tujuan — wajib;
- Keperluan kunjungan — wajib;
- Catatan tambahan — opsional.

**Field tambahan configurable, bukan default wajib:**

- jenis kelamin;
- alamat;
- pekerjaan;
- nomor identitas;
- email;
- jumlah anggota rombongan.

**Acceptance Criteria:**

- halaman dapat digunakan tanpa akun;
- field wajib ditandai jelas;
- form tidak dapat dikirim jika field wajib kosong;
- input memiliki batas panjang dan sanitasi;
- double-submit tidak menghasilkan duplikasi tanpa peringatan/penanganan;
- setelah berhasil, sistem menampilkan nomor kunjungan unik.

### FR-002 — Pencatatan Timestamp Otomatis

**Priority:** P0  
**Actor:** Sistem

Sistem harus merekam waktu registrasi menggunakan waktu server, bukan input manual pengguna.

**Acceptance Criteria:**

- `registered_at` selalu terisi pada kunjungan yang berhasil;
- waktu tampil sesuai zona waktu organisasi;
- perubahan waktu perangkat pengguna tidak mengubah timestamp server.

### FR-003 — Nomor/ID Kunjungan Unik

**Priority:** P0

Setiap kunjungan harus memiliki identifier unik yang mudah dirujuk oleh petugas.

**Acceptance Criteria:**

- tidak ada dua kunjungan dengan ID yang sama;
- ID tidak menggunakan nomor urut database mentah sebagai satu-satunya public identifier;
- ID dapat dicari di halaman riwayat.

### FR-004 — Konfirmasi Registrasi

**Priority:** P0  
**Actor:** Tamu

Setelah registrasi berhasil, sistem menampilkan:

- status berhasil;
- nama tamu;
- tujuan/unit;
- waktu registrasi;
- nomor kunjungan;
- instruksi selanjutnya yang dapat dikonfigurasi.

**P1 enhancement:** tombol cetak/bukti registrasi.

### FR-005 — Authentication Admin/Petugas

**Priority:** P0  
**Actor:** Petugas/Admin

Sistem harus memiliki login untuk akses back-office.

**Acceptance Criteria:**

- username/email dan password divalidasi;
- kredensial salah tidak mengungkap apakah akun tertentu ada;
- session kedaluwarsa setelah periode tidak aktif yang ditentukan;
- pengguna dapat logout;
- halaman admin tidak dapat diakses tanpa autentikasi.

### FR-006 — Role-Based Access Control Minimum

**Priority:** P0

Role minimum:

- `ADMIN` — konfigurasi, pengguna, master data, semua kunjungan, laporan;
- `STAFF` — melihat/memproses kunjungan, pencarian, laporan terbatas sesuai kebijakan.

**Acceptance Criteria:**

- STAFF tidak dapat membuat/menghapus admin;
- fitur manajemen pengguna hanya tersedia untuk role berizin;
- otorisasi diperiksa di backend, bukan hanya menyembunyikan menu di frontend.

### FR-007 — Dashboard Operasional

**Priority:** P0

Dashboard petugas harus menampilkan minimum:

- jumlah registrasi hari ini;
- jumlah kunjungan per status;
- daftar kunjungan terbaru;
- shortcut ke pencarian/riwayat;
- indikator bila tidak ada data.

**P1:** grafik tren harian/mingguan.

### FR-008 — Manajemen Status Kunjungan

**Priority:** P0

Status dasar:

- `REGISTERED`;
- `ARRIVED`;
- `IN_SERVICE`;
- `COMPLETED`;
- `CANCELLED`.

Status dapat disederhanakan jika alur organisasi lebih pendek.

**Acceptance Criteria:**

- perubahan status menyimpan waktu perubahan;
- perubahan status menyimpan aktor;
- status tidak dapat diubah oleh pengguna tanpa hak akses;
- histori status tidak hilang saat status terbaru berubah.

### FR-009 — Daftar dan Detail Kunjungan

**Priority:** P0

Petugas harus dapat melihat daftar dan detail kunjungan.

**Acceptance Criteria:**

- daftar memiliki pagination atau mekanisme virtualisasi yang sesuai;
- kolom minimum: waktu, nama, instansi, unit tujuan, keperluan ringkas, status;
- detail menampilkan seluruh data yang diizinkan role pengguna;
- data sensitif tidak ditampilkan pada tabel jika tidak diperlukan.

### FR-010 — Search dan Filter Riwayat

**Priority:** P0

Filter minimum:

- rentang tanggal;
- nama;
- instansi/organisasi;
- unit/bidang tujuan;
- status;
- nomor kunjungan.

**Acceptance Criteria:**

- kombinasi filter dapat digunakan bersama;
- hasil sesuai query;
- reset filter tersedia;
- query umum tidak menyebabkan full-page crash pada volume data normal MVP.

### FR-011 — Laporan Kunjungan

**Priority:** P0

Admin harus dapat menghasilkan laporan berdasarkan periode dan filter.

**Isi minimum:**

- tanggal/waktu;
- nomor kunjungan;
- nama;
- instansi;
- unit tujuan;
- keperluan;
- status.

**Acceptance Criteria:**

- laporan dapat difilter berdasarkan periode;
- hasil laporan konsisten dengan hasil pencarian pada filter yang sama;
- sistem menampilkan jumlah total record;
- laporan dapat diekspor.

### FR-012 — Export Data

**Priority:** P0

Sistem harus mendukung minimal satu format tabular yang mudah dibuka di spreadsheet. Target yang disarankan: `XLSX` atau `CSV`.

**Acceptance Criteria:**

- export menghormati filter;
- file memiliki header kolom yang jelas;
- karakter Indonesia tidak rusak;
- hanya role berizin yang dapat melakukan export;
- aktivitas export dicatat.

### FR-013 — Master Data Unit/Bidang

**Priority:** P0

Admin dapat:

- membuat unit/bidang;
- mengubah nama;
- menonaktifkan unit tanpa menghapus histori;
- mengatur urutan tampilan.

**Acceptance Criteria:**

- unit nonaktif tidak muncul pada registrasi baru;
- histori lama tetap menampilkan unit yang pernah dipilih;
- unit yang sudah dipakai tidak dapat dihapus permanen secara sembarangan.

### FR-014 — User Management

**Priority:** P0

Admin dapat:

- membuat akun petugas;
- mengaktifkan/menonaktifkan akun;
- menetapkan role;
- mereset password melalui mekanisme aman;
- melihat waktu login terakhir jika tersedia.

### FR-015 — Audit Log Minimum

**Priority:** P0

Audit minimum mencatat:

- login berhasil/gagal secara agregat atau aman;
- perubahan data master;
- perubahan status kunjungan;
- perubahan data kunjungan oleh petugas;
- export laporan;
- perubahan akun/role.

**Acceptance Criteria:**

- log tidak dapat diedit pengguna biasa;
- log menyimpan waktu dan aktor;
- log tidak menyimpan password atau secret.

### FR-016 — Koreksi Data Kunjungan

**Priority:** P1

Admin dapat memperbaiki kesalahan data dengan alasan perubahan.

**Acceptance Criteria:**

- nilai lama dan baru tersimpan dalam audit trail atau revision history;
- edit tidak mengubah timestamp registrasi asli;
- penghapusan permanen tidak tersedia melalui UI normal.

### FR-017 — Branding dan Konfigurasi Organisasi

**Priority:** P1

Admin dapat mengatur:

- nama instansi;
- logo;
- alamat/kontak layanan;
- teks instruksi registrasi;
- nama unit/bidang;
- zona waktu;
- beberapa field form sebagai required/optional jika aman untuk dilakukan.

---

## 9. Fitur Fase Selanjutnya

### Phase 2 — Operational Enhancement

#### P2-F01 — QR/Barcode Check-In

Menghasilkan QR/barcode pada bukti registrasi dan memungkinkan scan saat tamu tiba.

**Nilai:** mempercepat check-in dan mengurangi input ulang.

#### P2-F02 — Pre-Registration

Tamu dapat mengisi kunjungan sebelum datang dan memperoleh kode registrasi.

#### P2-F03 — WhatsApp Notification

Ketika tamu tiba, sistem mengirim notifikasi ke pihak/unit yang dituju.

**Catatan:** membutuhkan provider resmi/API, pengelolaan consent, template pesan, nomor tujuan, retry, biaya, dan pengamanan secret.

#### P2-F04 — Notifikasi Internal

Alternatif atau pendamping WhatsApp: notifikasi di dashboard/email/web push.

#### P2-F05 — Survey Kepuasan

Tamu dapat memberi rating dan komentar setelah kunjungan selesai.

#### P2-F06 — Statistik Lanjutan

- tren per hari/minggu/bulan;
- distribusi per unit;
- jenis instansi asal;
- peak hour;
- repeat visitor;
- waktu tunggu jika status timestamps lengkap.

### Phase 3 — Reliability, Security, and Integration

#### P3-F01 — Offline/PWA Mode

Memungkinkan pencatatan sementara saat koneksi internet/intranet terganggu, lalu sinkronisasi ketika koneksi kembali.

#### P3-F02 — Two-Factor Authentication

2FA untuk admin dan role sensitif.

#### P3-F03 — Automated Backup and Restore Verification

Backup terjadwal, rotasi, dan pengujian restore berkala.

#### P3-F04 — Integrasi Sistem Keamanan Gedung

Integrasi dengan access control, visitor badge, atau sistem keamanan lain bila tersedia.

#### P3-F05 — SSO

SSO untuk akun pegawai menggunakan identity provider organisasi.

#### P3-F06 — Multi-Location

Satu platform mengelola beberapa kantor/lokasi dengan konfigurasi terpisah.

---

## 10. Non-Functional Requirements

### NFR-001 — Usability

- form utama harus dapat diselesaikan tanpa pelatihan;
- bahasa default Bahasa Indonesia;
- istilah teknis diminimalkan pada sisi tamu;
- tombol aksi utama harus jelas dan konsisten;
- error message menjelaskan cara memperbaiki input.

### NFR-002 — Responsive UI

Aplikasi harus usable pada:

- desktop/laptop front-office;
- tablet/kiosk;
- smartphone modern.

Tidak diperlukan pixel-perfect parity antarperangkat; yang wajib adalah fungsi tetap lengkap dan dapat digunakan.

### NFR-003 — Performance

Target awal MVP pada infrastruktur normal kantor:

- halaman utama/registrasi: p95 < 2,5 detik pada koneksi internal/wifi stabil;
- submit form valid: p95 < 2 detik sampai konfirmasi;
- pencarian data umum: p95 < 2 detik untuk dataset operasional MVP;
- export laporan standar: selesai < 30 detik untuk periode normal.

Target harus diuji ulang setelah volume data dan spesifikasi server diketahui.

### NFR-004 — Availability

Target awal:

- minimum 99,5% availability pada jam operasional untuk deployment production yang dikelola dengan benar;
- maintenance terjadwal diumumkan terlebih dahulu.

### NFR-005 — Reliability

- submit tidak boleh menghasilkan record setengah tersimpan;
- kegagalan network harus menghasilkan pesan yang jelas;
- operasi yang berpotensi diulang harus aman terhadap accidental duplicate jika memungkinkan;
- transaksi penting harus atomic.

### NFR-006 — Accessibility

Target minimum:

- label form terasosiasi dengan input;
- navigasi keyboard untuk fungsi utama;
- fokus keyboard terlihat;
- kontras teks memadai;
- error tidak hanya disampaikan dengan warna;
- ukuran target sentuh memadai untuk tablet/ponsel.

### NFR-007 — Maintainability

- konfigurasi organisasi dipisahkan dari business logic;
- field master tidak di-hard-code jika secara wajar dapat dikonfigurasi;
- modul harus memiliki batas tanggung jawab yang jelas;
- perubahan schema melalui migration;
- logging error terstruktur.

### NFR-008 — Auditability

Perubahan sensitif harus dapat ditelusuri ke aktor dan waktu kejadian.

### NFR-009 — Data Portability

Data kunjungan dapat diekspor ke format tabular standar sehingga tidak terkunci pada aplikasi.

---

## 11. Technical Requirements & Constraints

> Bagian ini mendefinisikan kebutuhan teknis tingkat produk. Keputusan detail framework, topology deployment, folder structure, API pattern, dan cloud akan difinalkan di `Architecture.md`.

### 11.1 Baseline Technology Recommendation

Berdasarkan pola implementasi pada jurnal, baseline yang masuk akal adalah:

- **Web application**;
- **Backend:** PHP dengan framework Laravel;
- **Database:** MySQL/MariaDB;
- **Frontend:** UI responsif berbasis server-rendered pages atau component layer yang kompatibel dengan Laravel;
- **Deployment:** server yang mendukung PHP + relational database;
- **Third-party API:** tidak wajib untuk MVP.

Keputusan ini belum final sampai `Architecture.md` disetujui.

### 11.2 Browser Support

Minimum target:

- Chrome versi modern;
- Edge versi modern;
- browser Chromium Android modern;
- Safari iOS modern bila registrasi menggunakan ponsel.

Internet Explorer tidak didukung.

### 11.3 Database Requirement

Database harus:

- relasional;
- memiliki foreign key pada relasi utama;
- mendukung transaction;
- mendukung indexing untuk field pencarian utama;
- menyimpan timestamp secara konsisten;
- memiliki migration/versioning schema.

Detail tabel akan ditetapkan pada `Schema.md`.

### 11.4 Network Constraint

Karena salah satu studi menemukan ketidakstabilan koneksi sebagai hambatan, MVP harus:

- menampilkan error network secara jelas;
- tidak menghapus input form hanya karena request gagal;
- mencegah duplicate submission sebisa mungkin;
- menyediakan retry yang aman;
- memiliki health check sederhana untuk admin/ops.

**Offline mode penuh** ditunda ke fase lanjutan karena membutuhkan strategi sinkronisasi dan conflict resolution.

### 11.5 Deployment Constraint

Aplikasi harus dapat dideploy setidaknya pada salah satu skenario:

1. server internal/intranet;
2. VPS/cloud server;
3. managed hosting yang memenuhi requirement framework.

Pemilihan akhir bergantung pada kebijakan instansi, akses publik/intranet, anggaran, dan kebutuhan backup.

### 11.6 Timezone

- organisasi memiliki timezone configurable;
- timestamp internal harus konsisten;
- UI menampilkan waktu lokal organisasi;
- laporan menggunakan timezone organisasi.

### 11.7 File Export

- file export tidak boleh disimpan permanen tanpa kebutuhan;
- generated file dapat bersifat ephemeral;
- filename harus aman dan mengandung periode laporan bila relevan.

### 11.8 Observability Minimum

Production harus memiliki:

- application error log;
- access/security event log;
- audit log business action;
- monitoring disk/database dasar;
- mekanisme untuk mendeteksi backup gagal.

---

## 12. Security & Privacy Requirements

### 12.1 Data Minimization

Karena buku tamu menyimpan data pribadi, MVP harus menggunakan prinsip data minimization.

**Default wajib:**

- nama;
- asal instansi/organisasi;
- tujuan/unit;
- keperluan;
- waktu registrasi.

Nomor telepon, alamat, jenis kelamin, identitas resmi, dan data lain hanya diwajibkan jika ada alasan operasional/kebijakan yang jelas.

### 12.2 Notice/Consent

Halaman registrasi harus menampilkan pemberitahuan singkat yang menjelaskan:

- data digunakan untuk administrasi kunjungan;
- siapa pengelolanya;
- tujuan penyimpanan;
- kontak jika terdapat pertanyaan mengenai data.

Jika kelak WhatsApp digunakan, consent/legitimate-use untuk nomor telepon harus didefinisikan lebih spesifik.

### 12.3 Authentication Security

- password tidak disimpan plaintext;
- gunakan hashing adaptif yang didukung framework;
- rate limit login;
- session cookie aman;
- CSRF protection;
- secure password reset;
- 2FA dapat ditambahkan fase berikutnya.

### 12.4 Authorization

- semua endpoint admin harus memeriksa permission di server;
- akses data sensitif mengikuti least privilege;
- export hanya tersedia untuk role tertentu.

### 12.5 Input Security

- server-side validation wajib;
- output encoding untuk mencegah XSS;
- query menggunakan ORM/prepared statement;
- limit file upload bila branding/logo tersedia;
- whitelist MIME/type untuk upload.

### 12.6 Transport Security

Production harus menggunakan HTTPS ketika diakses melalui jaringan yang memungkinkan TLS. Jika hanya intranet, TLS tetap direkomendasikan.

### 12.7 Backup

MVP production minimal memiliki:

- backup database terjadwal;
- retention policy;
- backup disimpan terpisah dari database utama;
- restore test terdokumentasi sebelum go-live;
- secret/credential backup ditangani secara terpisah dan aman.

### 12.8 Deletion and Retention

Retention period belum dapat ditetapkan tanpa kebijakan instansi. Oleh karena itu:

- data tidak boleh otomatis dihapus sampai kebijakan retention ditetapkan;
- penghapusan permanen harus restricted;
- jika retention diterapkan, proses harus terdokumentasi dan audit-able.

---

## 13. Data Requirements Tingkat Produk

### 13.1 Entity Utama

Minimal domain entity yang dibutuhkan:

- `User`;
- `Role`/`Permission`;
- `OrganizationSettings`;
- `Department`/`Unit`;
- `Visitor` atau snapshot identitas pada visit;
- `Visit`;
- `VisitStatusHistory`;
- `AuditLog`;
- `ReportExportLog` (dapat digabung dengan audit log).

Detail normalisasi diputuskan di `Schema.md`.

### 13.2 Prinsip Historis

Sistem harus menjaga konteks histori. Contoh:

- jika nama unit berubah, laporan kunjungan lama tetap dapat dipahami;
- perubahan data kunjungan harus memiliki jejak;
- unit yang pernah digunakan sebaiknya dinonaktifkan, bukan dihapus permanen.

### 13.3 Searchable Fields

Field yang perlu diprioritaskan untuk search/index:

- visit public ID;
- visit date/time;
- visitor name;
- organization/origin;
- destination department;
- status.

### 13.4 Data Volume Assumption

Belum ada data volume aktual dari pemilik proyek. Desain MVP diasumsikan mampu menangani **puluhan hingga ratusan ribu record kunjungan** tanpa perubahan arsitektur besar, selama indexing dan pagination diterapkan dengan benar.

Jika target sebenarnya jutaan record atau multi-instansi, arsitektur harus direvisi.

---

## 14. Reporting & Analytics Requirements

### 14.1 Dashboard MVP

Menampilkan minimum:

- total kunjungan hari ini;
- jumlah per status;
- jumlah per unit tujuan untuk periode pendek (P1 bila scope ketat);
- daftar kunjungan terbaru.

### 14.2 Laporan Periodik

Laporan harus mendukung:

- harian;
- mingguan via rentang tanggal;
- bulanan;
- custom date range.

### 14.3 Filter Laporan

- tanggal;
- unit;
- status;
- instansi asal.

### 14.4 Advanced Analytics — Fase Lanjutan

- trend visit;
- peak hour;
- top destination department;
- repeat visitors;
- survey score;
- average processing time;
- ratio completed/cancelled;
- breakdown visitor type.

---

## 15. Success Metrics (KPIs)

> KPI harus dibandingkan dengan baseline proses manual selama periode observasi sebelum go-live. Target di bawah adalah target awal, bukan klaim bahwa semua instansi otomatis akan mencapainya.

### KPI-01 — Registration Time Reduction

**Metric:** median waktu dari mulai pengisian sampai registrasi berhasil.

**Target MVP:** minimal **50% lebih cepat** dari baseline manual atau memenuhi target operasional yang disepakati.

**Rationale:** salah satu studi melaporkan pengurangan waktu pencatatan >50%; target proyek harus tetap divalidasi pada lingkungan aktual.

### KPI-02 — Digital Adoption Rate

**Metric:** persentase kunjungan eligible yang tercatat di SI-TAMU.

**Target:** ≥90% dalam 4 minggu setelah go-live, lalu ≥95% setelah proses stabil.

### KPI-03 — Data Completeness

**Metric:** persentase record dengan seluruh field wajib valid.

**Target:** ≥98%.

### KPI-04 — Search Efficiency

**Metric:** waktu petugas menemukan satu kunjungan berdasarkan nama/nomor/tanggal.

**Target:** median ≤10 detik pada UAT skenario standar.

### KPI-05 — Report Preparation Time

**Metric:** waktu admin menghasilkan laporan periode standar.

**Target:** ≤1 menit dari pemilihan filter sampai file siap, tidak termasuk analisis manual tambahan.

### KPI-06 — Functional Test Pass Rate

**Metric:** pass rate test case P0.

**Target:** 100% test case P0 pass sebelum production release.

### KPI-07 — UAT Core Task Completion

**Metric:** persentase petugas yang dapat menyelesaikan skenario inti tanpa bantuan developer.

**Target:** ≥90% setelah sesi onboarding/training singkat.

### KPI-08 — Submission Error Rate

**Metric:** persentase registrasi valid yang gagal karena error aplikasi.

**Target:** <1% per bulan setelah stabilisasi.

### KPI-09 — Availability

**Metric:** availability selama jam operasional.

**Target:** ≥99,5% untuk deployment yang dikelola production-grade.

### KPI-10 — Critical Security Incidents

**Metric:** jumlah insiden akses tidak sah yang terkonfirmasi akibat kelemahan aplikasi.

**Target:** 0.

### KPI-11 — Staff Satisfaction

**Metric:** survey internal setelah 2–4 minggu.

**Target:** ≥80% petugas menyatakan workflow digital lebih mudah dibanding pencatatan manual.

### KPI-12 — Manual Re-Entry Reduction

**Metric:** jumlah kejadian data harus diketik ulang untuk pelaporan.

**Target:** ≥90% penurunan dibanding proses manual.

---

## 16. MVP Release Criteria

MVP dianggap siap untuk pilot jika seluruh kondisi berikut terpenuhi:

### Functional

- seluruh P0 functional requirement selesai;
- seluruh P0 black-box/system test pass;
- tidak ada blocker atau critical bug terbuka;
- search, filter, dan export telah diuji dengan data realistis;
- role/permission telah diuji dari sisi server.

### Security

- password hashing aktif;
- CSRF dan server-side validation aktif;
- HTTPS tersedia untuk production sesuai deployment;
- backup pertama berhasil;
- restore test berhasil;
- secret tidak disimpan di repository.

### Operational

- admin account production disiapkan;
- master unit/bidang terisi;
- jam/zona waktu benar;
- branding dasar terpasang;
- petugas telah mendapatkan quick guide;
- owner backup/maintenance jelas.

### Acceptance

- UAT dilakukan oleh minimal perwakilan petugas front-office dan admin;
- skenario registrasi, proses status, pencarian, dan laporan diterima;
- feedback mayor telah diselesaikan atau disepakati masuk backlog.

---

## 17. Testing Requirements Tingkat Produk

### 17.1 Test Categories

MVP membutuhkan minimal:

- unit test untuk business rule penting;
- feature/integration test untuk endpoint dan permission;
- black-box test terhadap fungsi utama;
- UAT dengan petugas nyata;
- security smoke test;
- backup/restore test;
- responsive test pada perangkat utama.

### 17.2 Skenario Black-Box Minimum

1. registrasi valid;
2. registrasi field wajib kosong;
3. input terlalu panjang;
4. duplicate submit;
5. login valid;
6. login invalid;
7. user non-admin mencoba membuka user management;
8. admin membuat unit;
9. admin menonaktifkan unit;
10. unit nonaktif tidak muncul di form;
11. petugas mengubah status;
12. status history tercatat;
13. search berdasarkan nama;
14. search berdasarkan visit ID;
15. filter tanggal;
16. filter unit;
17. filter status;
18. export dengan filter;
19. export tanpa hak akses;
20. logout;
21. session expired;
22. backup database;
23. restore database ke environment test.

Jumlah test case final dapat jauh lebih banyak; daftar di atas hanya minimum acceptance coverage.

---

## 18. Rollout Strategy

### 18.1 Stage 1 — Internal Development

- data dummy;
- validasi requirement;
- review UI dengan calon petugas.

### 18.2 Stage 2 — Pilot

- digunakan pada satu meja/front-office;
- durasi disarankan 1–2 minggu;
- pencatatan manual dapat dipertahankan sementara sebagai fallback hanya selama pilot bila organisasi membutuhkannya;
- kumpulkan baseline waktu dan feedback.

### 18.3 Stage 3 — Controlled Go-Live

- SI-TAMU menjadi sistem utama;
- SOP backup/restore aktif;
- admin dan support owner ditetapkan;
- bug/feedback diprioritaskan berdasarkan severity.

### 18.4 Stage 4 — Optimization

- review KPI;
- tentukan fitur Phase 2 berdasarkan data nyata;
- pilih apakah QR/WhatsApp/survey/offline menjadi prioritas berikutnya.

---

## 19. Risks & Mitigations

| ID | Risiko | Dampak | Probabilitas | Mitigasi |
|---|---|---:|---:|---|
| R-01 | Koneksi jaringan tidak stabil | Tinggi | Sedang | Error handling, retry aman, input preservation; offline mode pada fase berikutnya |
| R-02 | Petugas sulit beradaptasi | Sedang | Sedang | UI sederhana, pilot, quick guide, training singkat |
| R-03 | Data pribadi dikumpulkan berlebihan | Tinggi | Sedang | Data minimization, configurable field, review kebijakan |
| R-04 | User berbagi akun | Tinggi | Sedang | Akun individual, audit log, kebijakan akses |
| R-05 | Export data disalahgunakan | Tinggi | Rendah–Sedang | Permission khusus, audit export, SOP |
| R-06 | Backup ada tetapi tidak bisa dipulihkan | Tinggi | Sedang | Scheduled restore test |
| R-07 | Scope creep karena banyak fitur jurnal | Tinggi | Tinggi | Tetapkan P0/P1/P2; WhatsApp/QR/offline bukan MVP |
| R-08 | Form terlalu panjang | Sedang | Sedang | Field minimum; field tambahan opsional/configurable |
| R-09 | Unit/bidang sering berubah | Sedang | Sedang | Master data configurable; soft-disable |
| R-10 | Duplikasi registrasi karena retry | Sedang | Sedang | Idempotency/dedup strategy dan feedback submit |
| R-11 | Laporan lambat saat data membesar | Sedang | Sedang | Indexing, pagination, bounded exports, background job bila diperlukan nanti |
| R-12 | Third-party WhatsApp gagal | Sedang | Sedang | Jangan jadikan dependency MVP; retry/logging pada fase integrasi |

---

## 20. Dependencies

### 20.1 Dependency Organisasi

Dibutuhkan dari pemilik proyek:

- nama instansi;
- logo;
- daftar unit/bidang;
- siapa yang menjadi ADMIN dan STAFF;
- field apa saja yang benar-benar wajib;
- retention policy data;
- apakah sistem publik via internet atau hanya intranet;
- kebijakan backup;
- kebutuhan format laporan;
- kebutuhan bukti registrasi.

### 20.2 Dependency Infrastruktur

- server/aplikasi hosting;
- database;
- domain/subdomain bila publik;
- TLS certificate bila publik;
- SMTP/email bila password reset menggunakan email;
- storage backup.

### 20.3 Dependency Third-Party — Bukan MVP

- WhatsApp Business API/provider;
- SSO/identity provider;
- access-control system;
- SMS/email notification provider.

---

## 21. Product Decisions yang Sengaja Diambil

### 21.1 Tamu Tidak Wajib Memiliki Akun pada MVP

Beberapa studi menggunakan akun tamu, sedangkan studi lain menggunakan formulir langsung. Untuk mengurangi friction dan antrean, PRD ini memilih **guest registration tanpa akun** sebagai default MVP.

Jika instansi memerlukan pre-registration, histori pribadi, atau identitas tamu terverifikasi, akun tamu dapat ditambahkan kemudian.

### 21.2 WhatsApp Bukan MVP

Walaupun notifikasi WhatsApp terbukti bermanfaat pada salah satu studi, fitur ini menambah dependency eksternal, pengelolaan nomor telepon, consent, template, biaya, dan risiko delivery. Oleh karena itu WhatsApp diletakkan pada Phase 2.

### 21.3 QR/Barcode Bukan Dependency MVP

QR/barcode berguna untuk mempercepat check-in, tetapi sistem dasar harus tetap bekerja tanpa kamera/scanner. Hal ini membuat MVP lebih sederhana dan robust.

### 21.4 Data Pribadi Dibuat Minimum

Jurnal-jurnal menunjukkan variasi field yang cukup besar. PRD ini tidak menjadikan semua field sebagai mandatory agar produk tidak mengumpulkan data yang tidak diperlukan.

### 21.5 Laravel + MySQL sebagai Baseline, Bukan Keputusan Final

Stack ini berulang kali digunakan dalam sumber dan cocok untuk sistem CRUD/reporting, namun keputusan final akan dibuat di `Architecture.md` berdasarkan deployment target, kemampuan tim, dan kebutuhan operasional.

---

## 22. Open Questions untuk Revisi PRD

Pertanyaan berikut **tidak menghalangi draft PRD**, tetapi perlu dijawab sebelum Architecture final:

1. Nama dan jenis instansi target sebenarnya apa?
2. Apakah aplikasi digunakan publik melalui internet atau hanya jaringan internal?
3. Apakah tamu mendaftar sendiri lewat ponsel, tablet kiosk, atau dibantu petugas?
4. Apakah nomor telepon wajib?
5. Apakah perlu nomor identitas resmi?
6. Apakah tamu harus memilih pegawai spesifik atau cukup unit/bidang?
7. Apakah kunjungan memerlukan approval sebelum tamu diterima?
8. Apakah status antrean diperlukan?
9. Apakah perlu mencetak bukti/visitor badge di MVP?
10. Format laporan wajib: XLSX, PDF, CSV, atau kombinasi?
11. Berapa lama data kunjungan harus disimpan?
12. Siapa yang boleh mengekspor seluruh data?
13. Apakah admin boleh mengedit data tamu setelah registrasi?
14. Apakah sistem harus mendukung beberapa lokasi kantor?
15. Apakah WhatsApp diinginkan segera setelah MVP?
16. Apakah QR/barcode harus menjadi bagian pilot pertama?
17. Berapa estimasi jumlah kunjungan per hari/bulan?
18. Siapa yang bertanggung jawab terhadap server dan backup?
19. Apakah ada kebijakan keamanan TI internal yang wajib diikuti?
20. Apakah perlu integrasi dengan sistem lain di masa dekat?

---

## 23. Traceability Matrix — Jurnal ke Requirement

| Temuan Domain | Requirement Produk | Fase |
|---|---|---|
| Pencatatan manual lambat | FR-001 registrasi digital | MVP |
| Sulit mencari data | FR-010 search/filter | MVP |
| Risiko kehilangan arsip | Database terpusat + backup | MVP |
| Rekap manual lambat | FR-011 laporan | MVP |
| Kebutuhan export | FR-012 export | MVP |
| Statistik pengunjung | FR-007 dashboard | MVP |
| Data tujuan/unit | FR-013 master unit | MVP |
| Tamu dan admin memiliki kebutuhan berbeda | FR-005/006 auth + RBAC | MVP |
| Status/konfirmasi kunjungan | FR-008 status | MVP |
| Pencatatan waktu otomatis | FR-002 timestamp | MVP |
| Bukti registrasi | FR-004 + print enhancement | MVP/P1 |
| Barcode/QR | P2-F01 | Phase 2 |
| Notifikasi WhatsApp | P2-F03 | Phase 2 |
| Survei kepuasan | P2-F05 | Phase 2 |
| Statistik lanjutan | P2-F06 | Phase 2 |
| Koneksi tidak stabil | network resilience + PWA/offline | MVP/Phase 3 |
| 2FA | P3-F02 | Phase 3 |
| Backup otomatis | security baseline + P3-F03 hardening | MVP/Phase 3 |
| Integrasi keamanan gedung | P3-F04 | Phase 3 |

---

## 24. Definition of Done — PRD

PRD ini dapat dianggap **approved** ketika pemilik proyek menyetujui atau merevisi setidaknya:

- scope MVP;
- aktor pengguna;
- alur registrasi;
- field wajib;
- status kunjungan;
- fitur laporan/export;
- batas MVP vs fase lanjutan;
- KPI awal;
- target deployment publik/intranet;
- asumsi data dan keamanan.

Setelah PRD disetujui, `Architecture.md` harus menggunakan PRD ini sebagai sumber requirement utama dan tidak boleh mengubah scope secara diam-diam. Perubahan scope harus kembali dicatat pada PRD atau change log proyek.

---

## 25. Change Log

| Version | Status | Ringkasan |
|---|---|---|
| 0.1 | Draft | Draft awal berdasarkan empat jurnal yang diunggah pengguna; menyusun scope, MVP, non-functional requirements, constraints, security, KPI, phase roadmap, dan risk register. |

