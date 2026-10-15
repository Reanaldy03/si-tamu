# CLAUDE.md — SI-TAMU

Konteks kerja untuk Claude Code di repositori ini. **SI-TAMU** (Sistem Informasi Buku Tamu Digital) adalah aplikasi buku tamu institusional: **Laravel 12**, PHP 8.2+, Blade + **Tailwind CSS v4** + **Alpine.js**, **MySQL/MariaDB** (dev & produksi), SQLite `:memory:` (test). Arsitektur *Modular Monolith*: `Domain` → `Application` → `Infrastructure`/`Http`. Seluruh teks UI dan pesan validasi berbahasa Indonesia.

## Perintah

| Perintah | Fungsi |
| --- | --- |
| `composer run dev` | Server + queue listener + log (Pail) + Vite sekaligus |
| `php artisan serve` / `npm run dev` | Jalankan server / Vite secara terpisah |
| `npm run build` | Build aset produksi — **wajib** setelah mengubah Blade/CSS/JS |
| `php artisan test --compact` | Seluruh test suite (154 test — SQLite in-memory) |
| `php artisan test --filter=BlackBoxAcceptanceTest` | 23 skenario acceptance end-to-end |
| `php artisan test tests/Feature/Feedback/VisitFeedbackTest.php` | Satu file test spesifik |
| `vendor/bin/pint --dirty --format agent` | Format kode PHP — jalankan sebelum menyelesaikan perubahan |
| `php artisan sitamu:admin:create` | Buat akun administrator pertama (interaktif) |
| `php artisan sitamu:exports:cleanup` | Bersihkan ekspor laporan kedaluwarsa (scheduler per jam) |
| `php artisan migrate --seed` | Migrasi + seeder inti (roles, permission, pengaturan organisasi) |

Setup dari nol: `composer install && npm install && cp .env.example .env && php artisan key:generate && php artisan migrate --seed && php artisan storage:link && npm run build`

## Arsitektur

```
app/
├── Domain/               # Aturan bisnis murni, tanpa dependensi framework
│   ├── Visits/           #   VisitStatus, VisitStatusTransitionRule (state machine)
│   ├── Access/           #   RoleCode (ADMIN, STAFF)
│   ├── Reporting/        #   ExportStatus (PENDING…EXPIRED)
│   └── Shared/Contracts/ #   PublicIdGenerator (ULID 26 karakter)
├── Application/          # Use-case: Actions, Queries, DTOs, Services, Writers
│   ├── Visits/           #   RegisterVisitAction, ChangeVisitStatusAction, CheckInVisitViaQrAction, CorrectVisitDataAction, VisitQrTokenService
│   ├── Reporting/        #   ExportVisitsAction, PdfReportWriter, XlsxReportWriter, FormulaSanitizer
│   ├── Audit/            #   RecordAuditEventAction
│   └── Access/ Users/ Departments/ Settings/
├── Infrastructure/       # UlidPublicIdGenerator, Letterhead (kop surat), Exports, Storage, Time
├── Http/                 # Controllers (Admin/Public/Auth), FormRequests, Middleware
├── Models/               # Eloquent: User, Role, Permission, Department, Visit, VisitStatusHistory, VisitFeedback, AuditLog, ReportExport, OrganizationSetting
├── Policies/             # Otorisasi berbasis permission (User::hasPermission)
└── Console/Commands/     # sitamu:admin:create, sitamu:exports:cleanup
```

**Alur status kunjungan** (satu arah): `REGISTERED → ARRIVED → IN_SERVICE → COMPLETED`, atau `CANCELLED` dari status mana pun sebelum terminal. Divalidasi `VisitStatusTransitionRule` + `VisitStatus::allowedNextStatuses()`; `COMPLETED`/`CANCELLED` bersifat terminal.

**Route** (`routes/web.php`): publik `public.*` (registrasi `/`, sukses, cetak tiket, survei `/visit/{publicId}/feedback`) dan back-office `admin.*` (middleware `auth` + `active.user`).

## File Kunci

- `bootstrap/app.php` — middleware global `AddSecurityHeaders` & `AddRequestContext`; alias `active.user`; redirect guest→`login`, user→`admin.dashboard`.
- `app/Application/Visits/Services/VisitQrTokenService.php` — token QR = HMAC-SHA256(public_id, APP_KEY); DB menyimpan hash SHA-256 (`visits.qr_token`, unique); payload JSON `{app, v, public_id, token}`; parser menerima JSON, `SI-TAMU:id:token`, atau ULID polos.
- `app/Infrastructure/Printing/Letterhead.php` — kop surat bersama untuk PDF & tiket; `faces()` untuk Dompdf, `faceDataUri()` untuk browser.
- `resources/css/app.css` — token desain Tailwind v4 (`@theme`); satu-satunya sumber palet (jangan pakai hue mentah).
- `resources/js/app.js` — Alpine + html5-qrcode + jsQR + Chart.js (global `window.*`) + `playBeep()`.
- `tests/bootstrap.php` — mirror `$_ENV`→`$_SERVER` untuk isolasi test (lihat Gotchas).
- `phpunit.xml` — env test dipaksa `force="true"`; SQLite `:memory:`.

## Konvensi

- PHP 8.2: constructor promotion, return type eksplisit, selalu kurung kurawal; ikuti pola file tetangga.
- Alur: Controller tipis → FormRequest (validasi) → Action (dibungkus `DB::transaction`) → Model.
- Enum backed dengan `label()` berbahasa Indonesia.
- Route selalu diberi nama; gunakan `route()`.
- Waktu disimpan UTC; tampilkan via zona organisasi (WIB/WITA/WIT).
- Permission (9 slug): `visits.view`, `visits.update_status`, `visits.correct`, `reports.view`, `reports.export`, `departments.manage`, `users.manage`, `audit.view`, `settings.manage` — ADMIN semua, STAFF hanya `visits.view`, `visits.update_status`, `reports.view`.

## Testing

- Selalu `php artisan test --compact`; jangan bikin skrip verifikasi manual.
- `RefreshDatabase` + seed eksplisit (`$this->seed(RoleAndPermissionSeeder::class)`); hanya ada `UserFactory` — model lain dibuat via `Model::create()`.
- `Tests\TestCase` memanggil `withoutVite()`; nama test deskriptif (`test_01_registrasi_valid`).

## Gotchas

1. **Kamera QR butuh secure context** — jika diakses lewat IP LAN pada HTTP biasa, browser memblokir kamera; halaman scanner punya tombol switch origin `127.0.0.1`. Produksi wajib HTTPS.
2. **Font kop tidak ada di repo** — disalin dari OS (`C:/Windows/Fonts` atau msttcorefonts) saat render pertama ke `storage/app/private/fonts/`; fallback Helvetica/Times tanpa error. Jangan commit font.
3. **Ekspor laporan ephemeral** — retensi 24 jam; cleanup per jam; unduhan kedaluwarsa → HTTP 410; CSV hanya untuk baris historis (permintaan baru PDF/XLSX).
4. **`public/build` gitignored** — UI tidak berubah atau muncul "Unable to locate file in Vite manifest" → `npm run build`.
5. **Jangan hapus `tests/bootstrap.php`** — shell workstation ini mengekspor isi `.env` ke environment; bootstrap menyalin `$_ENV` (yang dipaksa `phpunit.xml`) ke `$_SERVER` agar konfigurasi test menang. Tanpa ini: CSRF 419 & test berisiko menyentuh DB MySQL nyata.
6. **DB lokal MySQL (`sitamu`)**; `.env.example` default SQLite; test SQLite `:memory:`.
7. **`X-Request-ID` divalidasi ≤64 karakter** (fallback UUID) — kolom `audit_logs.request_id` hanya 64 char.
8. **Feedback** hanya untuk kunjungan `COMPLETED`, satu per kunjungan (unique), rating 1–5.
9. **File Boost gitignored** (`AGENTS.md`, `.agents/`, `boost.json`, `opencode.json`) — regenerate dengan `php artisan boost:install`.
10. **Dokumen internal hanya lokal** (`PRD.md`, `Architecture.md`, `Design.md`, `Schema.md`, `Rules.md`, `PROGRESS.md`, `PROMPTS.md`) — tidak dipublikasikan; repo publik hanya memuat `README.md` + `CLAUDE.md` sebagai markdown.
11. **Registrasi publik dilindungi** honeypot `website_hp` + token idempotensi `submission_token` + throttle 15 req/menit — jangan hapus field tersembunyi di form registrasi.

## Alur Kerja

- Sebelum menyelesaikan perubahan: `vendor/bin/pint --dirty --format agent` + `php artisan test --compact`; setelah mengubah Blade/CSS/JS: `npm run build`.
- Branch `main` = versi publish (`github.com/Reanaldy03/si-tamu`); `master` lokal menyimpan riwayat pengembangan penuh.
- Jangan commit: `.env`, font, ekspor, `database.sqlite` (semua sudah di-ignore).
