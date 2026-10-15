<?php

namespace Tests\Feature;

use App\Domain\Access\Enums\RoleCode;
use App\Domain\Reporting\Enums\ExportStatus;
use App\Domain\Visits\Enums\RegistrationSource;
use App\Domain\Visits\Enums\VisitStatus;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\OrganizationSetting;
use App\Models\ReportExport;
use App\Models\Role;
use App\Models\User;
use App\Models\Visit;
use App\Models\VisitStatusHistory;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DomainModelRelationsAndCastsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_user_model_relations_casts_and_helpers(): void
    {
        $user = User::create([
            'public_id' => (string) Str::ulid(),
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'password' => 'secret123',
            'is_active' => true,
        ]);

        $adminRole = Role::where('slug', 'ADMIN')->first();
        $user->roles()->attach($adminRole->id, ['assigned_at' => now()]);

        $user->refresh();

        $this->assertTrue($user->is_active);
        $this->assertTrue($user->hasRole(RoleCode::ADMIN));
        $this->assertTrue($user->isAdmin());
        $this->assertFalse($user->isStaff());
        $this->assertSame(RoleCode::ADMIN, $user->primary_role_code);
        $this->assertTrue($user->hasPermission('visits.view'));
        $this->assertTrue($user->hasPermission('settings.manage'));
        $this->assertFalse($user->hasPermission('non_existent_permission'));
    }

    public function test_department_model_active_scope_and_relations(): void
    {
        $activeDept = Department::create([
            'public_id' => (string) Str::ulid(),
            'code' => 'UMUM',
            'name' => 'Bagian Umum',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $inactiveDept = Department::create([
            'public_id' => (string) Str::ulid(),
            'code' => 'ARSIP',
            'name' => 'Bagian Arsip Nonaktif',
            'sort_order' => 2,
            'is_active' => false,
        ]);

        $activeList = Department::active()->get();

        $this->assertTrue($activeList->contains('id', $activeDept->id));
        $this->assertFalse($activeList->contains('id', $inactiveDept->id));
    }

    public function test_visit_and_history_models_casts_and_relations(): void
    {
        $dept = Department::create([
            'public_id' => (string) Str::ulid(),
            'code' => 'PELAYANAN',
            'name' => 'Bagian Pelayanan',
            'is_active' => true,
        ]);

        $now = CarbonImmutable::now();

        $visit = Visit::create([
            'public_id' => (string) Str::ulid(),
            'submission_token_hash' => hash('sha256', 'test_token'),
            'visitor_name' => 'Ahmad Dahlan',
            'origin_organization' => 'PT Mitra Sejahtera',
            'contact_phone' => '081234567890',
            'visitor_identity_type' => 'KTP',
            'visitor_identity_encrypted' => '3201012345670001',
            'party_size' => 2,
            'department_id' => $dept->id,
            'department_name_snapshot' => $dept->name,
            'purpose' => 'Koordinasi Layanan Publik',
            'current_status' => VisitStatus::REGISTERED,
            'registration_source' => RegistrationSource::PUBLIC,
            'registered_at' => $now,
            'status_updated_at' => $now,
        ]);

        $history = VisitStatusHistory::create([
            'visit_id' => $visit->id,
            'from_status' => null,
            'to_status' => VisitStatus::REGISTERED,
            'source' => 'PUBLIC',
            'changed_at' => $now,
        ]);

        $visit->refresh();

        $this->assertInstanceOf(VisitStatus::class, $visit->current_status);
        $this->assertSame(VisitStatus::REGISTERED, $visit->current_status);
        $this->assertInstanceOf(RegistrationSource::class, $visit->registration_source);
        $this->assertSame(RegistrationSource::PUBLIC, $visit->registration_source);
        $this->assertSame(2, $visit->party_size);
        // Encrypted cast assertion
        $this->assertSame('3201012345670001', $visit->visitor_identity_encrypted);

        // Relations check
        $this->assertSame($dept->id, $visit->department->id);
        $this->assertCount(1, $visit->statusHistories);
        $this->assertSame($history->id, $visit->latestStatusHistory->id);
        $this->assertSame($visit->id, $history->visit->id);
        $this->assertNull($history->from_status);
        $this->assertSame(VisitStatus::REGISTERED, $history->to_status);
    }

    public function test_audit_log_model_casts_and_actor(): void
    {
        $user = User::create([
            'public_id' => (string) Str::ulid(),
            'name' => 'Petugas Audit',
            'email' => 'audit@example.com',
            'password' => 'secret123',
            'is_active' => true,
        ]);

        $log = AuditLog::create([
            'event_type' => 'visit.status_changed',
            'actor_type' => 'USER',
            'actor_user_id' => $user->id,
            'target_type' => 'visit',
            'target_id' => 10,
            'metadata' => ['ip' => '127.0.0.1'],
            'before_state' => ['status' => 'REGISTERED'],
            'after_state' => ['status' => 'ARRIVED'],
            'occurred_at' => now(),
        ]);

        $log->refresh();

        $this->assertIsArray($log->metadata);
        $this->assertSame('127.0.0.1', $log->metadata['ip']);
        $this->assertSame(['status' => 'REGISTERED'], $log->before_state);
        $this->assertSame(['status' => 'ARRIVED'], $log->after_state);
        $this->assertSame($user->id, $log->actor->id);
    }

    public function test_report_export_model_casts_and_user_relation(): void
    {
        $user = User::create([
            'public_id' => (string) Str::ulid(),
            'name' => 'Petugas Laporan',
            'email' => 'laporan@example.com',
            'password' => 'secret123',
            'is_active' => true,
        ]);

        $export = ReportExport::create([
            'public_id' => (string) Str::ulid(),
            'requested_by_user_id' => $user->id,
            'format' => 'PDF',
            'status' => ExportStatus::PENDING,
            'filters' => ['start_date' => '2026-09-01', 'end_date' => '2026-09-09'],
            'download_count' => 0,
        ]);

        $export->refresh();

        $this->assertInstanceOf(ExportStatus::class, $export->status);
        $this->assertSame(ExportStatus::PENDING, $export->status);
        $this->assertIsArray($export->filters);
        $this->assertSame('2026-09-01', $export->filters['start_date']);
        $this->assertSame($user->id, $export->requestedBy->id);
    }

    public function test_organization_setting_singleton(): void
    {
        $setting = OrganizationSetting::current();

        $this->assertNotNull($setting);
        $this->assertSame(1, $setting->id);
        $this->assertSame('Sekretariat Dewan Perwakilan Rakyat Daerah', $setting->organization_name);
        $this->assertSame('Asia/Jakarta', $setting->timezone);
        $this->assertIsArray($setting->registration_form_config);
        $this->assertArrayHasKey('contact_phone', $setting->registration_form_config);
    }
}
