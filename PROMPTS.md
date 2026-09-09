# PROMPTS.md — Panduan & Prompt Mutakhir AI Development SI-TAMU

Dokumen ini berisi rangkaian prompt sistematis untuk memandu AI (seperti Claude Code, Cursor, Windsurf, ChatGPT, atau GitHub Copilot) membangun aplikasi **SI-TAMU** dari nol hingga siap produksi secara konsisten tanpa kehilangan konteks.

---

## DAFTAR ISI
1. [Prinsip Kerja & State Anchor Protocol](#1-prinsip-kerja--state-anchor-protocol)
2. [Master System Prompt (Inisialisasi Project)](#2-master-system-prompt-inisialisasi-project)
3. [Prompt Re-Anchoring (Pemulihan Konteks Sesi Baru)](#3-prompt-re-anchoring-pemulihan-konteks-sesi-baru)
4. [Rangkaian Prompt Eksekusi Sprint 1 - 9](#4-rangkaian-prompt-eksekusi-sprint-1---9)
5. [Format Handoff & Laporan Progres](#5-format-handoff--laporan-progres)

---

## 1. Prinsip Kerja & State Anchor Protocol

Agar AI tidak mengalami *context drift* (kehilangan fokus arsitektur, lupa struktur tabel, atau mengarang fitur sendiri), terapkan 3 aturan baku:
1. **Source of Truth Terkunci**: AI wajib merujuk 5 dokumen inti (`PRD.md`, `Architecture.md`, `Design.md`, `Schema.md`, `Rules.md`).
2. **State Ledger Wajib**: AI wajib membaca dan mengupdate file `PROGRESS.md` di akhir setiap sprint.
3. **Eksekusi Bertahap (Sprint-by-Sprint)**: Jangan meminta AI membuat seluruh aplikasi dalam satu prompt. Jalankan sprint satu per satu.

---

## 2. Master System Prompt (Inisialisasi Project)

> Salin prompt berikut ke Custom Instructions, System Prompt, atau pesan pertama Anda:

```markdown
Anda adalah Senior Software Architect & Lead Laravel Engineer yang bertugas membangun sistem SI-TAMU (Sistem Informasi Buku Tamu Digital Berbasis Web) secara end-to-end hingga siap produksi.

### 1. SUMBER KEBENARAN TERTINGGI (SOURCE OF TRUTH)
Anda terikat penuh pada 5 dokumen spesifikasi proyek:
1. PRD.md: Scope MVP, aktor (Guest, Staff, Admin), state machine, target KPI, dan batasan non-goals.
2. Architecture.md: Pola Modular Monolith Laravel, layer Presentation-Application-Domain-Infrastructure, Nginx/PHP-FPM/MySQL, ULID identifier, database queue/cache/session.
3. Design.md: Blade SSR + Tailwind CSS + Alpine.js, design token, alur formulir publik 1 halaman, UI back-office, status visual, mobile-first responsive.
4. Schema.md: DDL presisi MySQL 8.x, foreign key RESTRICT/SET NULL, check constraints, composite index, zero hard-delete for visits/departments.
5. Rules.md: Coding standards PSR-12, strict types, action-based use case, tidak ada business logic di Blade/Controller, no "AI slop" visual.

### 2. PRINSIP IMPLEMENTASI KETAT
- DILARANG berhalusinasi atau menambahkan package di luar baseline tanpa izin eksplisit (Laravel Pint, Tailwind, Alpine.js).
- DILARANG mengimplementasikan fitur Phase 2/3 pada fase MVP (TIDAK ADA WhatsApp API, QR/Barcode scanner, Offline/PWA, Multi-tenant, atau 2FA pada tahap ini).
- Controller WAJIB tipis: hanya orkestrasi HTTP FormRequest, memanggil Action/Query di layer Application, dan mengembalikan View/Redirect.
- Seluruh mutasi status kunjungan (visits) dan registrasi WAJIB berada dalam Database Transaction yang mencakup penulisan visit_status_history dan audit_logs.
- Gunakan ULID 26 karakter untuk seluruh public_id. ID internal autoincrement tidak boleh bocor ke URL publik atau endpoint.
- Tulis kode lengkap, fungsional, dan dapat diuji. DILARANG menggunakan placeholder seperti '// TODO: implement later' atau memotong implementasi metode inti.

### 3. PROTOKOL KONSISTENSI & STATE TRACKING
Di root direktori proyek, Anda WAJIB membaca dan memperbarui file PROGRESS.md. Setiap kali Anda menyelesaikan sebuah modul/tugas, Anda WAJIB melaporkan format Handoff berikut:
- Status Milestone Saat Ini: [Sprint/Task ID]
- File yang dibuat/diubah: [Daftar file spesifik]
- Status Testing: [Command test yang dijalankan beserta hasil nyata]
- Konteks untuk Langkah Berikutnya: [Task ID spesifik yang siap dikerjakan selanjutnya]
- Open Issues/Blocker: [Jika ada keputusan arsitektural yang tertunda]
```

---

## 3. Prompt Re-Anchoring (Pemulihan Konteks Sesi Baru)

> Gunakan prompt ini ketika berpindah window obrolan, sesi terputus, atau AI tampak mulai lupa konteks proyek:

```markdown
KONTINUITAS PROYEK SI-TAMU:
Baca file PRD.md, Architecture.md, Schema.md, Design.md, Rules.md, dan PROGRESS.md di root workspace.

Tugas Anda saat ini:
1. Analisis PROGRESS.md untuk mengonfirmasi task mana yang sudah selesai dan task apa yang menjadi prioritas selanjutnya.
2. Berikan ringkasan singkat status arsitektur proyek saat ini dalam format Handoff.
3. Tunggu konfirmasi saya sebelum menulis kode baru. Jangan melompat ke sprint lain sebelum task aktif diverifikasi.
```

---

## 4. Rangkaian Prompt Eksekusi Sprint 1 - 9

### SPRINT 1: Setup Struktur & Database Migrations
```markdown
EKSEKUSI SPRINT 1: SETUP STRUKTUR & DATABASE MIGRATIONS

Berdasarkan Architecture.md dan Schema.md:
1. Konfigurasikan struktur folder modular di app/:
   - app/Application (Actions, DTOs, Queries per modul: Access, Visits, Departments, Users, Reporting, Audit, Settings)
   - app/Domain (Enums, Rules, Events, Exceptions, Shared Contracts)
   - app/Infrastructure (Identifiers, Exports, Time, Storage)
2. Buat file migration Laravel terurut untuk seluruh tabel yang didefinisikan pada Schema.md Bagian 20 & 24:
   - users, roles, permissions, user_roles, role_permissions
   - departments, organization_settings
   - visits, visit_status_history, audit_logs, report_exports
   - sessions, cache, jobs, failed_jobs, password_reset_tokens
3. Terapkan tipe data, foreign keys (RESTRICT/SET NULL/CASCADE), CHECK constraints, dan composite indexes secara presisi sesuai DDL Schema.md.
4. Buat DatabaseSeeder yang mengisi data awal:
   - Role 'ADMIN' dan 'STAFF'
   - Permission catalog lengkap (9 permissions)
   - Pivot mapping permission ke roles
   - Singleton organization_settings (id=1, timezone 'Asia/Jakarta')
5. Buat Artisan Console Command `php artisan sitamu:admin:create` untuk bootstrap user admin awal tanpa password default hard-coded.

Perbarui file PROGRESS.md setelah kode dan migration selesai dibuat.
```

### SPRINT 2: Domain Layer & Business Rules
```markdown
EKSEKUSI SPRINT 2: DOMAIN LAYER & BUSINESS RULES

Berdasarkan Domain Layer pada Architecture.md dan Rules.md:
1. Buat Backed Enums di app/Domain:
   - VisitStatus (REGISTERED, ARRIVED, IN_SERVICE, COMPLETED, CANCELLED)
   - RoleCode (ADMIN, STAFF)
   - RegistrationSource (PUBLIC, STAFF, IMPORT)
   - ExportStatus (PENDING, PROCESSING, COMPLETED, FAILED, EXPIRED)
2. Buat Domain Rule `VisitStatusTransitionRule`:
   - Validasi state machine transisi status sesuai Schema.md Bagian 6.9:
     * REGISTERED -> ARRIVED / CANCELLED
     * ARRIVED -> IN_SERVICE / CANCELLED
     * IN_SERVICE -> COMPLETED / CANCELLED
     * Terminal state: COMPLETED dan CANCELLED tidak boleh bertransisi lagi
   - Lempar exception `InvalidVisitStatusTransition` jika transisi ilegal.
3. Buat kontrak dan implementasi ULID generator di `app/Domain/Shared/Contracts/PublicIdGenerator.php` dan `app/Infrastructure/Identifiers/UlidPublicIdGenerator.php`.
4. Buat Eloquent Models di `app/Models/` lengkap dengan relasi, type casts (immutable_datetime, JSON array, Enums), and fillable attributes sesuai Schema.md Bagian 25.
5. Buat Unit Test untuk memverifikasi transisi status dan generator ULID.

Perbarui file PROGRESS.md setelah verifikasi berhasil.
```

### SPRINT 3: Application Actions & Transaction Engine
```markdown
EKSEKUSI SPRINT 3: APPLICATION ACTIONS & TRANSACTION BOUNDARY

Berdasarkan Architecture.md Bagian 7 dan 12:
Bangun use case orchestration murni (Action classes) di `app/Application/`:
1. `RegisterVisitAction`:
   - Menerima DTO `RegisterVisitData`
   - Hitung SHA-256 hash dari submission token idempotency
   - Jalankan dalam DB Transaction:
     a. Cek redundansi idempotency hash
     b. Generate ULID public ID
     c. Insert record `visits` (snapshot data tamu + department_name_snapshot)
     d. Insert initial `visit_status_history` (from_status: null, to_status: REGISTERED)
     e. Insert event `audit_logs` (visit.registered)
2. `ChangeVisitStatusAction`:
   - Menerima DTO `ChangeVisitStatusData` (visit_id, target_status, actor_id, reason, request_id)
   - Jalankan dalam DB Transaction dengan `SELECT ... FOR UPDATE` pada row visit
   - Validasi via `VisitStatusTransitionRule`
   - Update `visits.current_status` dan `status_updated_at`
   - Insert ke `visit_status_history`
   - Insert ke `audit_logs` (visit.status_changed)
3. `RecordAuditEventAction`:
   - Helper reusable untuk append record `audit_logs` dengan sanitasi metadata (strip password, token, raw credentials).
4. Buat Feature Tests yang menguji rollback atomik: pastikan jika salah satu query gagal di tengah jalan, tidak ada data parsial tersisa.

Perbarui file PROGRESS.md setelah seluruh test Action lulus.
```

### SPRINT 4: Authentication, Policies & Security Middlewares
```markdown
EKSEKUSI SPRINT 4: AUTHENTICATION, POLICIES & MIDDLEWARE

Berdasarkan Architecture.md Bagian 11 & 17 serta Rules.md:
1. Implementasikan session authentication untuk back-office (Staff dan Admin):
   - `AuthController` (GET login, POST login, POST logout)
   - Proteksi rate limiting agresif pada endpoint login
   - Error message generik ("Email/username atau password tidak sesuai")
   - Session invalidation & regeneration saat login/logout
2. Bangun Authorization Policies:
   - `VisitPolicy`: view, updateStatus, correct
   - `DepartmentPolicy`: manage (Admin only)
   - `UserPolicy`: manage (Admin only, cegah admin menonaktifkan dirinya sendiri)
   - `ReportPolicy`: view, export (Admin; Staff dibatasi sesuai policy)
   - `OrganizationSettingPolicy`: manage (Admin only)
3. Daftarkan Middleware:
   - `EnsureUserIsActive`: tendang sesi jika `is_active == 0`
   - `AddSecurityHeaders`: nosniff, frame-ancestors, CSP bertahap
   - `AddRequestContext`: inject correlation ID / request ID untuk logging & audit
4. Buat Feature Tests otorisasi: pastikan role STAFF otomatis mendapat HTTP 403 Forbidden ketika mencoba mengakses route User Management, Department CRUD, dan Audit Logs.

Perbarui file PROGRESS.md setelah seluruh rute terproteksi.
```

### SPRINT 5: Public Guest Registration Flow
```markdown
EKSEKUSI SPRINT 5: PUBLIC GUEST REGISTRATION FLOW

Berdasarkan Design.md Bagian 9 & 10 dan Architecture.md Bagian 12:
1. Controller & Form Request:
   - `GuestRegistrationController` (create, store, success)
   - `StoreGuestVisitRequest`: validasi server-side wajib (nama, instansi, department aktif, keperluan, honeypot/rate limit, sanitasi string).
2. Tampilan Blade SSR:
   - `resources/views/layouts/public.blade.php`: Clean, mobile-first, zero marketing clutter, no external CDN dependencies.
   - `resources/views/public/registration/create.blade.php`:
     * Single-column form terpusat
     * Logo instansi dari settings
     * Hidden submission token untuk idempotency
     * Select unit/departemen aktif
     * State visual interaktif: disabled on submit via Alpine.js, pesan validasi inline ramah pengguna, retensi `old()` input jika terjadi kegagalan.
   - `resources/views/public/registration/success.blade.php`:
     * Tampilan nomor kunjungan (public ULID), nama tamu, unit tujuan, waktu registrasi (format lokal organisasi).
     * Tombol "Salin Nomor" & Tombol reset untuk kembali ke form baru (kiosk-friendly).
3. Buat Feature Tests: verifikasi pengiriman sukses, validasi field kosong, dan verifikasi bahwa retry dengan token identik tidak menghasilkan entri ganda.

Perbarui file PROGRESS.md.
```

### SPRINT 6: Back-Office Operations (Dashboard, Visits List & Detail)
```markdown
EKSEKUSI SPRINT 6: BACK-OFFICE DASHBOARD, VISITS LIST & DETAIL

Berdasarkan Design.md Bagian 7, 12, 13, 14:
1. Layout Admin:
   - `resources/views/layouts/admin.blade.php`: Shell sidebar desktop, mobile off-canvas drawer, top bar profil, role-aware menu visibility.
2. Dashboard (`DashboardController`):
   - Metric cards: Total Hari Ini, Terdaftar/Menunggu, Sedang Dilayani, Selesai.
   - Tabel Kunjungan Terbaru (maksimal 10 record terbaru hari ini).
3. Daftar Riwayat Kunjungan (`VisitController@index`):
   - Server-side search & filtering bar (Keyword nama/instansi/ULID, tanggal rentang, unit tujuan, status).
   - Filter state dipertahankan via URL query string.
   - Server-side pagination (20 row/halaman).
   - Responsive table ke card view untuk layar kecil.
   - Empty state informatif ("Tidak ada kunjungan yang cocok").
4. Detail Kunjungan & Status Management (`VisitController@show`, `VisitStatusController@update`):
   - Info lengkap tamu, unit, waktu registrasi (WIB/lokal).
   - Status badge semantik (warna + label teks eksplisit).
   - Vertical Status Timeline berdasarkan `visit_status_history`.
   - Tombol Next Valid Status Transition dinamis (contoh: "Tandai Tiba", "Mulai Dilayani", "Selesaikan").
   - Modal konfirmasi destruktif khusus status "Batalkan Kunjungan".
5. Buat Feature Tests simulasi workflow: Petugas login -> buka dashboard -> klik kunjungan -> ubah status menjadi ARRIVED -> periksa timeline bertambah.

Perbarui file PROGRESS.md.
```

### SPRINT 7: Master Data, User Management, Audit Logs & Settings
```markdown
EKSEKUSI SPRINT 7: MASTER DATA, USER MANAGEMENT, AUDIT & SETTINGS

Berdasarkan PRD.md (FR-013, FR-014, FR-015, FR-017) dan Design.md:
1. Department Management (`DepartmentController`):
   - CRUD unit/bidang.
   - Pola Soft-Disable: tombol "Nonaktifkan" alih-alih hard delete jika unit sudah memiliki relasi data visit. Unit nonaktif dilarang muncul di dropdown registrasi publik.
2. User Management (`UserController`):
   - Form pembuatan akun staff/admin baru.
   - Toggle aktif/nonaktif akun.
   - Proteksi invariant: larang penonaktifan akun admin terakhir.
3. Audit Log Viewer (`AuditController`):
   - Read-only table aktivitas audit (Event, Aktor, Target, Waktu, Metadata diff yang aman).
   - Filter berdasarkan rentang waktu, aktor, dan event type.
4. Organization Settings (`OrganizationSettingsController`):
   - Pengaturan nama instansi, alamat, nomor kontak, teks instruksi registrasi.
   - Upload logo dengan validasi ketat MIME type (png/jpg/webp) & pembatasan ukuran.
   - Cache invalidation otomatis saat setting diperbarui.

Perbarui file PROGRESS.md.
```

### SPRINT 8: Reporting Engine, Query Builder & Ephemeral Export
```markdown
EKSEKUSI SPRINT 8: REPORTING & EPHEMERAL EXPORT ENGINE

Berdasarkan Architecture.md Bagian 14 & 16 dan Schema.md Bagian 6.11:
1. Reporting Engine (`ReportController`):
   - Filter identik dengan halaman riwayat: rentang tanggal (half-open range UTC conversion), unit, status.
   - Menampilkan agregat ringkasan dan preview data paginated.
2. Export Engine (`ExportController` & `ExportVisitsAction`):
   - Validasi otorisasi via `ReportPolicy`.
   - Ekspor format CSV (streamed response) dan XLSX adapter.
   - Penulisan baris data menggunakan chunking/lazy collection untuk efisiensi memory.
   - Sanitasi sel spreadsheet untuk mencegah formula injection (escape karakter `=`, `+`, `-`, `@`).
   - Buat record di `report_exports` untuk mencatat lifecycle file & parameter filter JSON.
   - Catat audit event `report.export_requested`.
3. Scheduled Export Cleanup Job:
   - Buat command `php artisan sitamu:exports:cleanup` untuk menghapus file export temporer yang telah kedaluwarsa.
4. Feature Tests: validasi bahwa filter tanggal laporan konsisten dengan data yang diekspor dan staf tanpa hak akses ditolak.

Perbarui file PROGRESS.md.
```

### SPRINT 9: QA Lengkap & Production Checklist
```markdown
EKSEKUSI SPRINT 9: QA LENGKAP & PRODUCTION CHECKLIST

Berdasarkan PRD.md Bagian 17 dan Rules.md:
1. Jalankan dan lengkapi test suite otomatis:
   - 23 skenario Black-Box sesuai PRD.md Bagian 17.2.
   - Database constraint test (duplicate public_id rejected, foreign key integrity test).
   - XSS & Security smoke test pada form input publik.
2. Lakukan code formatting audit menggunakan Laravel Pint (PSR-12 compliance).
3. Jalankan `npm run build` dan pastikan tidak ada dependency aset eksternal via CDN.
4. Buat file `README.md` komprehensif berisi instruksi instalasi lokal, migrasi database, seeding, artisan bootstrap command, dan konfigurasi supervisor worker untuk queue.

Finalisasi file PROGRESS.md menjadi 100% complete.
```

---

## 5. Format Handoff & Laporan Progres

AI wajib menyertakan laporan akhir dalam format ini setelah setiap sprint selesai:

```text
============================================================
SI-TAMU DEVELOPMENT HANDOFF
============================================================
Sprint / Milestone: [Contoh: SPRINT-03 - Application Actions]
Files Created / Modified:
  - app/Application/Visits/Actions/RegisterVisitAction.php
  - app/Application/Visits/Actions/ChangeVisitStatusAction.php
  - tests/Feature/Visits/VisitTransactionTest.php
  - PROGRESS.md
Behavior Verified:
  - Atomic transaction rollback verified on DB failure
  - Idempotency hash duplicate check verified
Tests Executed:
  - php artisan test --filter=VisitTransactionTest (OK - 4 tests passed)
Next Sprint Ready: SPRINT-04 (Authentication, Policies & Middlewares)
Blockers / Open Questions: None
============================================================
```
