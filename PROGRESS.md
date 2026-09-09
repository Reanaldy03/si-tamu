# PROGRESS.md — SI-TAMU Project Ledger & State Tracker

> **File ini wajib diperbarui setiap kali satu sub-tugas/sprint selesai dikerjakan oleh AI.**  
> Dokumen ini berfungsi sebagai jangkar konteks (context anchor) agar AI tidak lupa status dan detail proyek.

---

## 1. Project Metadata
- **Project Name:** SI-TAMU (Sistem Informasi Buku Tamu Digital Berbasis Web)
- **Architecture Style:** Modular Monolith (Laravel 11, PHP 8.3+, MySQL 8.x, Blade + Tailwind CSS + Alpine.js)
- **Primary Actors:** Guest (Public, No Account), Staff (Front-Office), Admin (Back-Office)
- **Current Sprint:** SPRINT-04 (Authentication, Policies & Security Middlewares)
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
- [ ] `GuestRegistrationController` & `StoreGuestVisitRequest` (Validation & Sanitization)
- [ ] Layout `layouts/public.blade.php` (Clean, mobile-first, no external CDN dependencies)
- [ ] Registration View `public/registration/create.blade.php` (Single-column, active departments, idempotency token, Alpine.js submit disabled)
- [ ] Confirmation View `public/registration/success.blade.php` (ULID public reference, destination, timestamp, reset button)
- [ ] Feature Tests for Public Registration, Validation Errors & Duplicate Submission Prevention

### Sprint 6: Back-Office Operations (Dashboard, Visits List & Detail)
- [ ] Shell Navigation `layouts/admin.blade.php` (Desktop sidebar, mobile drawer, topbar, role-aware navigation)
- [ ] Operational Dashboard (`DashboardController`: 4 Metric Cards + Latest Visits)
- [ ] Visits List (`VisitController@index`: URL query filters, date range, pagination, responsive cards)
- [ ] Visit Detail (`VisitController@show`: Guest info, dynamic next status button, status history timeline)
- [ ] Status Change Controller & Modals (`VisitStatusController@update`: Modal konfirmasi pembatalan)
- [ ] Feature Tests for Full Front-Office Workflow

### Sprint 7: Master Data, User Management, Audit & Settings
- [ ] Department Management (`DepartmentController`: CRUD + Soft-disable pattern)
- [ ] User Management (`UserController`: CRUD + Active toggle + Self-protection rule)
- [ ] Audit Log Viewer (`AuditController`: Read-only, filterable by actor/date/event)
- [ ] Organization Settings (`OrganizationSettingsController`: Identity, branding logo upload, cache invalidation)
- [ ] Feature Tests for Master Data & Audit Immutability

### Sprint 8: Reporting Engine & Ephemeral Export
- [ ] Report Filter & Summary Preview (`ReportController`: Synchronous preview matching search filter)
- [ ] Ephemeral Export Engine (`ExportVisitsAction`: Streamed CSV / XLSX adapter, memory-efficient chunking, formula injection sanitization)
- [ ] Export Lifecycle Management (`report_exports` metadata tracking)
- [ ] Scheduled Export Cleanup Job (`php artisan sitamu:exports:cleanup`)
- [ ] Feature Tests for Export Lifecycle & Integrity

### Sprint 9: Quality Assurance, Security Polish & Production Checklist
- [ ] 23 Black-Box Test Scenarios (PRD.md FR-001 - FR-017)
- [ ] Security smoke test (XSS, SQL Injection prevention, CSRF verification)
- [ ] Code formatting via Laravel Pint (`./vendor/bin/pint --test`)
- [ ] Vite production assets build verification (`npm run build`)
- [ ] Deployment guide & README.md finalize

---

## 3. Active Context Snapshot (Diisi Oleh AI per Sesi)
- **Completed Files:**
  - `database/migrations/0001_01_01_000000_create_users_table.php`
  - `database/migrations/0001_01_01_000010_create_password_reset_tokens_table.php`
  - `database/migrations/0001_01_01_000020_create_sessions_table.php`
  - `database/migrations/2026_01_01_000100_create_roles_table.php`
  - `database/migrations/2026_01_01_000110_create_permissions_table.php`
  - `database/migrations/2026_01_01_000120_create_user_roles_table.php`
  - `database/migrations/2026_01_01_000130_create_role_permissions_table.php`
  - `database/migrations/2026_01_01_000200_create_departments_table.php`
  - `database/migrations/2026_01_01_000300_create_organization_settings_table.php`
  - `database/migrations/2026_01_01_000400_create_visits_table.php`
  - `database/migrations/2026_01_01_000410_create_visit_status_history_table.php`
  - `database/migrations/2026_01_01_000500_create_audit_logs_table.php`
  - `database/migrations/2026_01_01_000600_create_report_exports_table.php`
  - `database/seeders/RoleAndPermissionSeeder.php`
  - `database/seeders/OrganizationSettingsSeeder.php`
  - `database/seeders/DatabaseSeeder.php`
  - `app/Console/Commands/CreateAdminCommand.php`
  - `app/Domain/Visits/Enums/VisitStatus.php`
  - `app/Domain/Access/Enums/RoleCode.php`
  - `app/Domain/Visits/Enums/RegistrationSource.php`
  - `app/Domain/Reporting/Enums/ExportStatus.php`
  - `app/Domain/Visits/Rules/VisitStatusTransitionRule.php`
  - `app/Domain/Visits/Exceptions/InvalidVisitStatusTransitionException.php`
  - `app/Domain/Shared/Contracts/PublicIdGenerator.php`
  - `app/Infrastructure/Identifiers/UlidPublicIdGenerator.php`
  - `app/Models/User.php`
  - `app/Models/Role.php`
  - `app/Models/Permission.php`
  - `app/Models/Department.php`
  - `app/Models/Visit.php`
  - `app/Models/VisitStatusHistory.php`
  - `app/Models/AuditLog.php`
  - `app/Models/ReportExport.php`
  - `app/Models/OrganizationSetting.php`
  - `app/Application/Visits/DTOs/RegisterVisitDTO.php`
  - `app/Application/Visits/DTOs/ChangeVisitStatusDTO.php`
  - `app/Application/Visits/DTOs/CorrectVisitDataDTO.php`
  - `app/Application/Audit/Actions/RecordAuditEventAction.php`
  - `app/Application/Visits/Actions/RegisterVisitAction.php`
  - `app/Application/Visits/Actions/ChangeVisitStatusAction.php`
  - `app/Application/Visits/Actions/CorrectVisitDataAction.php`
  - `app/Http/Controllers/Auth/AuthController.php`
  - `app/Http/Requests/Auth/LoginRequest.php`
  - `app/Http/Middleware/EnsureUserIsActive.php`
  - `app/Http/Middleware/AddSecurityHeaders.php`
  - `app/Http/Middleware/AddRequestContext.php`
  - `app/Policies/VisitPolicy.php`
  - `app/Policies/DepartmentPolicy.php`
  - `app/Policies/UserPolicy.php`
  - `app/Policies/ReportPolicy.php`
  - `app/Policies/OrganizationSettingPolicy.php`
  - `resources/views/auth/login.blade.php`
  - `routes/web.php`
  - `bootstrap/app.php`
  - `tests/TestCase.php`
  - `tests/Feature/Sprint1SetupTest.php`
  - `tests/Unit/Domain/VisitStatusTransitionRuleTest.php`
  - `tests/Unit/Infrastructure/UlidPublicIdGeneratorTest.php`
  - `tests/Feature/DomainModelRelationsAndCastsTest.php`
  - `tests/Feature/Visits/RegisterVisitActionTest.php`
  - `tests/Feature/Visits/ChangeVisitStatusActionTest.php`
  - `tests/Feature/Visits/CorrectVisitDataActionTest.php`
  - `tests/Feature/Audit/RecordAuditEventActionTest.php`
  - `tests/Feature/Auth/AuthenticationTest.php`
  - `tests/Feature/Auth/PolicyAuthorizationTest.php`
- **Last Verified Test:** `php artisan test` (44 passed, 244 assertions OK), `php vendor/bin/pint --test` (passed)
- **Active Task:** SPRINT-05 (Public Guest Registration Flow)
- **Blockers / Open Questions:** None
