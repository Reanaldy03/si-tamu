# PROGRESS.md — SI-TAMU Project Ledger & State Tracker

> **File ini wajib diperbarui setiap kali satu sub-tugas/sprint selesai dikerjakan oleh AI.**  
> Dokumen ini berfungsi sebagai jangkar konteks (context anchor) agar AI tidak lupa status dan detail proyek.

---

## 1. Project Metadata
- **Project Name:** SI-TAMU (Sistem Informasi Buku Tamu Digital Berbasis Web)
- **Architecture Style:** Modular Monolith (Laravel 11, PHP 8.3+, MySQL 8.x, Blade + Tailwind CSS + Alpine.js)
- **Primary Actors:** Guest (Public, No Account), Staff (Front-Office), Admin (Back-Office)
- **Current Sprint:** SPRINT-09 (Quality Assurance, Security Polish & Production Checklist) — COMPLETED
- **Last Updated:** 2026-09-09

---

## 2. Sprint Execution Checklist

### Sprint 1: Setup & Database Migrations
- [x] Setup app directory structure (`app/Application`, `app/Domain`, `app/Infrastructure`)
- [x] Database Migrations:
  - [x] `users`, `roles`, `permissions`, `user_roles`, `role_permissions`
  - [x] `departments`, `organization_settings`
  - [x] `visits`, `visit_status_history`, `audit_logs`, `report_exports`
  - [x] Framework tables (`sessions`, `cache`, `jobs`, `failed_jobs`, `password_reset_tokens`)
- [x] Database Seeder (Roles: ADMIN, STAFF; Permissions: 9 baseline permissions; Organization Settings singleton)
- [x] Artisan bootstrap command (`php artisan sitamu:admin:create`)

### Sprint 2: Domain Layer & Business Rules
- [x] Backed Enums (`VisitStatus`, `RoleCode`, `RegistrationSource`, `ExportStatus`)
- [x] Domain Rules (`VisitStatusTransitionRule` state machine)
- [x] ULID Public ID Generator Contract & Implementation (`CHAR(26)`)
- [x] Eloquent Models with Relations, Enums, and Casts (`User`, `Role`, `Permission`, `Department`, `Visit`, `VisitStatusHistory`, `AuditLog`, `ReportExport`, `OrganizationSetting`)
- [x] Unit Tests for State Machine & ULID generation

### Sprint 3: Application Actions & Transaction Engine
- [x] `RegisterVisitAction` (Atomic Transaction: Idempotency token hash + Visit + StatusHistory + AuditLog)
- [x] `ChangeVisitStatusAction` (Atomic Transaction: `SELECT ... FOR UPDATE` + Transition Rule + History + AuditLog)
- [x] `RecordAuditEventAction` (Append-only sanitized business audit)
- [x] `CorrectVisitDataAction` (Admin-only data correction with sanitized before/after state diff)
- [x] Feature Tests for Transaction Boundaries & Rollback Integrity

### Sprint 4: Authentication, Policies & Security Middlewares
- [x] Back-office Session Authentication (`AuthController`: Login, Logout, Rate Limiting, Session Regeneration)
- [x] Granular Authorization Policies (`VisitPolicy`, `DepartmentPolicy`, `UserPolicy`, `ReportPolicy`, `OrganizationSettingPolicy`)
- [x] Custom Middlewares (`EnsureUserIsActive`, `AddSecurityHeaders`, `AddRequestContext`)
- [x] Authorization Feature Tests (Verifikasi STAFF ditolak 403 untuk akses Admin)

### Sprint 5: Public Guest Registration Flow
- [x] `GuestRegistrationController` & `StoreGuestVisitRequest` (Validation & Sanitization)
- [x] Layout `layouts/public.blade.php` (Clean, mobile-first, no external CDN dependencies)
- [x] Registration View `public/registration/create.blade.php` (Single-column, active departments, idempotency token, Alpine.js submit disabled)
- [x] Confirmation View `public/registration/success.blade.php` (ULID public reference, destination, timestamp, reset button)
- [x] Feature Tests for Public Registration, Validation Errors & Duplicate Submission Prevention

### Sprint 6: Back-Office Operations (Dashboard, Visits List & Detail)
- [x] Shell Navigation `layouts/admin.blade.php` (Desktop sidebar, mobile drawer, topbar, role-aware navigation)
- [x] Operational Dashboard (`DashboardController`: 4 Metric Cards + Latest Visits)
- [x] Visits List (`VisitController@index`: URL query filters, date range, pagination, responsive cards)
- [x] Visit Detail (`VisitController@show`: Guest info, dynamic next status button, status history timeline)
- [x] Status Change Controller & Modals (`VisitStatusController@update`: Modal konfirmasi pembatalan)
- [x] Feature Tests for Full Front-Office Workflow

### Sprint 7: Master Data, User Management, Audit & Settings
- [x] Department Management (`DepartmentController`: CRUD + Soft-disable pattern)
- [x] User Management (`UserController`: CRUD + Active toggle + Self-protection rule)
- [x] Audit Log Viewer (`AuditController`: Read-only, filterable by actor/date/event)
- [x] Organization Settings (`OrganizationSettingsController`: Identity, branding logo upload, cache invalidation)
- [x] Feature Tests for Master Data & Audit Immutability

### Sprint 8: Reporting Engine & Ephemeral Export
- [x] Report Filter & Summary Preview (`ReportController`: Synchronous preview matching search filter)
- [x] Ephemeral Export Engine (`ExportVisitsAction`: Streamed CSV / XLSX adapter, memory-efficient chunking, formula injection sanitization)
- [x] Export Lifecycle Management (`report_exports` metadata tracking)
- [x] Scheduled Export Cleanup Job (`php artisan sitamu:exports:cleanup`)
- [x] Feature Tests for Export Lifecycle & Integrity

### Sprint 9: Quality Assurance, Security Polish & Production Checklist
- [x] 23 Black-Box Test Scenarios (PRD.md FR-001 - FR-017)
- [x] Security smoke test (XSS, SQL Injection prevention, CSRF verification)
- [x] Code formatting via Laravel Pint (`./vendor/bin/pint --test`)
- [x] Vite production assets build verification (`npm run build`)
- [x] Deployment guide & README.md finalize

### Phase 2 Extension: QR Code Check-In, Cetak Tiket & Survei IKM (DPRD Kota Binjai)
- [x] Database Migrations:
  - [x] `add_qr_token_to_visits_table` (`qr_token CHAR(64)`, `checked_in_at DATETIME(6)`)
  - [x] `create_visit_feedback_table` (`rating`, `feedback_text`, `submitted_at`)
- [x] Cryptographic QR Token Engine (`VisitQrTokenService` HMAC-SHA256 with `app.key`)
- [x] Pure Vector SVG Generator (`simplesoftwareio/simple-qrcode`)
- [x] Public Confirmation with High-Res QR Code (`public.registration.success`)
- [x] Official Printable Visitor Pass with Kop Sekretariat DPRD Kota Binjai (`ticket-print.blade.php`)
- [x] Back-Office Camera Scanner with Local `html5-qrcode` & Web Audio API chime (`admin/visits/scanner.blade.php`)
- [x] Atomic Check-In Transaction Engine (`CheckInVisitViaQrAction` with `SELECT ... FOR UPDATE` & audit log)
- [x] Official 8 Units Seeder (`DepartmentSeeder`: UMUM, PERSIDANGAN, WASGAR, PIMPINAN, KOMISI-A, KOMISI-B, KOMISI-C, FRAKSI)
- [x] Official Branding & Identity (`OrganizationSettingsSeeder`: Jl. Veteran No. 1 Binjai)
- [x] Public Guest Satisfaction Survey (`GuestFeedbackController` with 5-star interactive rating)
- [x] Back-Office IKM Analytics & Feedback Review (`FeedbackController@index`)
- [x] Interactive Chart.js Analytics on Operational Dashboard (Tren 7 Hari, Proporsi Asal Tamu, Distribusi Komisi/Bagian)
- [x] Feature Tests (`VisitQrCheckInTest`, `GuestFeedbackTest`)

---

## 3. Active Context Snapshot (Diisi Oleh AI per Sesi)
- **Completed Files:**
  - All Sprint 1 - 9 files
  - `database/migrations/2026_01_01_000420_add_qr_token_to_visits_table.php`
  - `database/migrations/2026_01_01_000430_create_visit_feedback_table.php`
  - `database/seeders/DepartmentSeeder.php`
  - `database/seeders/OrganizationSettingsSeeder.php`
  - `app/Models/VisitFeedback.php`
  - `app/Application/Visits/Services/VisitQrTokenService.php`
  - `app/Application/Visits/Actions/CheckInVisitViaQrAction.php`
  - `app/Http/Controllers/Admin/VisitCheckInController.php`
  - `app/Http/Controllers/Admin/FeedbackController.php`
  - `app/Http/Controllers/Public/GuestFeedbackController.php`
  - `resources/views/public/registration/ticket-print.blade.php`
  - `resources/views/public/feedback/create.blade.php`
  - `resources/views/admin/visits/scanner.blade.php`
  - `resources/views/admin/feedback/index.blade.php`
  - `tests/Feature/Visits/VisitQrCheckInTest.php`
  - `tests/Feature/Public/GuestFeedbackTest.php`
- **Last Verified Test:** `php artisan test` (138 passed, 630 assertions OK), `php vendor/bin/pint --test` (passed), `npm run build` (passed)
- **Active Task:** SISTEM 100% SELESAI (DPRD KOTA BINJAI READY)
- **Blockers / Open Questions:** None
