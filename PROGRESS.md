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

---

## 3. Active Context Snapshot (Diisi Oleh AI per Sesi)
- **Completed Files:**
  - `README.md`
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
  - `app/Policies/AuditLogPolicy.php`
  - `resources/views/auth/login.blade.php`
  - `app/Http/Requests/Public/StoreGuestVisitRequest.php`
  - `app/Http/Controllers/Public/GuestRegistrationController.php`
  - `resources/views/layouts/public.blade.php`
  - `resources/views/public/registration/create.blade.php`
  - `resources/views/public/registration/success.blade.php`
  - `resources/js/app.js`
  - `app/Http/Controllers/Controller.php`
  - `app/Http/Requests/Admin/VisitSearchRequest.php`
  - `app/Http/Requests/Admin/UpdateVisitStatusRequest.php`
  - `app/Http/Controllers/Admin/DashboardController.php`
  - `app/Http/Controllers/Admin/VisitController.php`
  - `app/Http/Controllers/Admin/VisitStatusController.php`
  - `resources/views/layouts/admin.blade.php`
  - `resources/views/admin/dashboard.blade.php`
  - `resources/views/admin/visits/index.blade.php`
  - `resources/views/admin/visits/show.blade.php`
  - `app/Http/Requests/Admin/StoreDepartmentRequest.php`
  - `app/Http/Requests/Admin/UpdateDepartmentRequest.php`
  - `app/Http/Requests/Admin/StoreUserRequest.php`
  - `app/Http/Requests/Admin/UpdateUserRequest.php`
  - `app/Http/Requests/Admin/UpdateOrganizationSettingsRequest.php`
  - `app/Http/Controllers/Admin/DepartmentController.php`
  - `app/Http/Controllers/Admin/UserController.php`
  - `app/Http/Controllers/Admin/AuditController.php`
  - `app/Http/Controllers/Admin/OrganizationSettingsController.php`
  - `resources/views/admin/departments/index.blade.php`
  - `resources/views/admin/departments/create.blade.php`
  - `resources/views/admin/departments/edit.blade.php`
  - `resources/views/admin/users/index.blade.php`
  - `resources/views/admin/users/create.blade.php`
  - `resources/views/admin/users/edit.blade.php`
  - `resources/views/admin/audits/index.blade.php`
  - `resources/views/admin/audits/show.blade.php`
  - `resources/views/admin/settings/edit.blade.php`
  - `app/Application/Reporting/DTOs/ReportFilterDTO.php`
  - `app/Application/Reporting/Queries/VisitReportQuery.php`
  - `app/Application/Reporting/Services/FormulaSanitizer.php`
  - `app/Application/Reporting/Writers/CsvReportWriter.php`
  - `app/Application/Reporting/Writers/XlsxReportWriter.php`
  - `app/Application/Reporting/Actions/ExportVisitsAction.php`
  - `app/Http/Requests/Admin/ReportFilterRequest.php`
  - `app/Http/Requests/Admin/ExportReportRequest.php`
  - `app/Http/Controllers/Admin/ReportController.php`
  - `app/Console/Commands/CleanupExpiredExportsCommand.php`
  - `resources/views/admin/reports/index.blade.php`
  - `routes/web.php`
  - `routes/console.php`
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
  - `tests/Feature/Public/GuestRegistrationTest.php`
  - `tests/Feature/Admin/DashboardTest.php`
  - `tests/Feature/Admin/VisitManagementTest.php`
  - `tests/Feature/Admin/DepartmentManagementTest.php`
  - `tests/Feature/Admin/UserManagementTest.php`
  - `tests/Feature/Admin/AuditLogViewerTest.php`
  - `tests/Feature/Admin/OrganizationSettingsTest.php`
  - `tests/Feature/Admin/ReportAndExportTest.php`
  - `tests/Feature/SecuritySmokeTest.php`
  - `tests/Feature/BlackBoxAcceptanceTest.php`
  - `tests/Feature/ExampleTest.php`
- **Last Verified Test:** `php artisan test` (126 passed, 573 assertions OK), `php vendor/bin/pint --test` (passed), `npm run build` (passed)
- **Active Task:** ALL SPRINTS COMPLETED (Ready for Pilot & Production Deployment)
- **Blockers / Open Questions:** None
