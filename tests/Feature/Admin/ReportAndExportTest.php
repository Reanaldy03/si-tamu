<?php

namespace Tests\Feature\Admin;

use App\Domain\Access\Enums\RoleCode;
use App\Domain\Reporting\Enums\ExportStatus;
use App\Domain\Visits\Enums\RegistrationSource;
use App\Domain\Visits\Enums\VisitStatus;
use App\Models\Department;
use App\Models\ReportExport;
use App\Models\Role;
use App\Models\User;
use App\Models\Visit;
use Database\Seeders\OrganizationSettingsSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class ReportAndExportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $staff;

    protected Department $deptA;

    protected Department $deptB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(OrganizationSettingsSeeder::class);

        $adminRole = Role::where('slug', RoleCode::ADMIN->value)->first();
        $staffRole = Role::where('slug', RoleCode::STAFF->value)->first();

        $this->admin = User::create([
            'public_id' => '01HXYZ00000000000000000001',
            'name' => 'Admin Utama',
            'email' => 'admin@sitamu.local',
            'password' => bcrypt('AdminPass123!'),
            'is_active' => true,
        ]);
        $this->admin->roles()->attach($adminRole->id, ['assigned_at' => now()]);

        $this->staff = User::create([
            'public_id' => '01HXYZ00000000000000000002',
            'name' => 'Staff Front-Office',
            'email' => 'staff@sitamu.local',
            'password' => bcrypt('StaffPass123!'),
            'is_active' => true,
        ]);
        $this->staff->roles()->attach($staffRole->id, ['assigned_at' => now()]);

        $this->deptA = Department::create([
            'public_id' => '01HXYZ00000000000000000011',
            'code' => 'TIK',
            'name' => 'Divisi Teknologi Informasi',
            'is_active' => true,
        ]);

        $this->deptB = Department::create([
            'public_id' => '01HXYZ00000000000000000012',
            'code' => 'KEUANGAN',
            'name' => 'Bagian Keuangan',
            'is_active' => true,
        ]);
    }

    public function test_staff_can_view_reports_page_and_preview(): void
    {
        Visit::create([
            'public_id' => '01HXYZ00000000000000000091',
            'visitor_name' => 'Budi Santoso',
            'origin_organization' => 'PT Telekomunikasi',
            'contact_phone' => '081234567890',
            'department_id' => $this->deptA->id,
            'department_name_snapshot' => $this->deptA->name,
            'purpose' => 'Koordinasi Server',
            'current_status' => VisitStatus::COMPLETED,
            'registration_source' => RegistrationSource::PUBLIC,
            'registered_at' => now(),
            'status_updated_at' => now(),
            'idempotency_key' => 'idem-report-1',
        ]);

        $response = $this->actingAs($this->staff)->get(route('admin.reports.index'));

        $response->assertOk();
        $response->assertSee('Laporan & Rekapitulasi Kunjungan');
        $response->assertSee('Budi Santoso');
        $response->assertSee('PT Telekomunikasi');
        $response->assertSee('Total Kunjungan');
    }

    public function test_staff_cannot_export_reports(): void
    {
        $response = $this->actingAs($this->staff)->post(route('admin.reports.export'), [
            'format' => 'CSV',
        ]);

        $response->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('admin.reports.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_filter_reports_by_department_and_status(): void
    {
        // Visit in Dept A, COMPLETED
        Visit::create([
            'public_id' => '01HXYZ00000000000000000092',
            'visitor_name' => 'Agus Pratama',
            'origin_organization' => 'Dinas Pendidikan',
            'contact_phone' => '081111111111',
            'department_id' => $this->deptA->id,
            'department_name_snapshot' => $this->deptA->name,
            'purpose' => 'Konsultasi',
            'current_status' => VisitStatus::COMPLETED,
            'registration_source' => RegistrationSource::PUBLIC,
            'registered_at' => now(),
            'status_updated_at' => now(),
            'idempotency_key' => 'idem-report-2',
        ]);

        // Visit in Dept B, WAITING
        Visit::create([
            'public_id' => '01HXYZ00000000000000000093',
            'visitor_name' => 'Citra Dewi',
            'origin_organization' => 'Bank Mandiri',
            'contact_phone' => '082222222222',
            'department_id' => $this->deptB->id,
            'department_name_snapshot' => $this->deptB->name,
            'purpose' => 'Pengantaran Berkas',
            'current_status' => VisitStatus::ARRIVED,
            'registration_source' => RegistrationSource::PUBLIC,
            'registered_at' => now(),
            'status_updated_at' => now(),
            'idempotency_key' => 'idem-report-3',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.reports.index', [
            'department_id' => $this->deptA->id,
            'current_status' => 'COMPLETED',
        ]));

        $response->assertOk();
        $response->assertSee('Agus Pratama');
        $response->assertDontSee('Citra Dewi');
    }

    public function test_admin_can_export_csv_with_proper_bom_and_sanitization(): void
    {
        Storage::fake('local');

        // Insert visit with potentially malicious spreadsheet formulas
        Visit::create([
            'public_id' => '01HXYZ00000000000000000094',
            'visitor_name' => '=1+2',
            'origin_organization' => '@dangerOrg',
            'contact_phone' => '083333333333',
            'department_id' => $this->deptA->id,
            'department_name_snapshot' => $this->deptA->name,
            'purpose' => '-maliciousAction',
            'current_status' => VisitStatus::COMPLETED,
            'registration_source' => RegistrationSource::STAFF,
            'registered_at' => now(),
            'status_updated_at' => now(),
            'idempotency_key' => 'idem-report-4',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.reports.export'), [
            'format' => 'CSV',
        ]);

        $response->assertRedirect(route('admin.reports.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('report_exports', [
            'format' => 'CSV',
            'status' => 'COMPLETED',
            'row_count' => 1,
            'requested_by_user_id' => $this->admin->id,
        ]);

        $export = ReportExport::where('format', 'CSV')->latest('id')->first();
        $this->assertNotNull($export);
        Storage::disk('local')->assertExists($export->storage_path);

        $content = Storage::disk('local')->get($export->storage_path);

        // Verify UTF-8 BOM
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);

        // Verify formula injection sanitization (prefix with single quote)
        $this->assertStringContainsString("'=1+2", $content);
        $this->assertStringContainsString("'@dangerOrg", $content);
        $this->assertStringContainsString("'-maliciousAction", $content);

        // Verify audit log
        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'report.export_requested',
            'actor_user_id' => $this->admin->id,
            'target_type' => 'report_exports',
            'target_id' => $export->id,
        ]);
    }

    public function test_admin_can_export_xlsx_file(): void
    {
        Storage::fake('local');

        Visit::create([
            'public_id' => '01HXYZ00000000000000000095',
            'visitor_name' => 'Doni Kusuma',
            'origin_organization' => 'PT Maju Terus',
            'contact_phone' => '084444444444',
            'department_id' => $this->deptB->id,
            'department_name_snapshot' => $this->deptB->name,
            'purpose' => 'Rapat Umum',
            'current_status' => VisitStatus::COMPLETED,
            'registration_source' => RegistrationSource::PUBLIC,
            'registered_at' => now(),
            'status_updated_at' => now(),
            'idempotency_key' => 'idem-report-5',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.reports.export'), [
            'format' => 'XLSX',
        ]);

        $response->assertRedirect(route('admin.reports.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('report_exports', [
            'format' => 'XLSX',
            'status' => 'COMPLETED',
            'row_count' => 1,
        ]);

        $export = ReportExport::where('format', 'XLSX')->latest('id')->first();
        $this->assertNotNull($export);
        Storage::disk('local')->assertExists($export->storage_path);

        // Verify it is a valid Zip Archive
        $zip = new ZipArchive;
        $tempPath = Storage::disk('local')->path($export->storage_path);
        $openResult = $zip->open($tempPath);
        $this->assertTrue($openResult === true);
        $this->assertNotFalse($zip->locateName('xl/worksheets/sheet1.xml'));
        $zip->close();
    }

    public function test_authenticated_admin_can_download_export_file(): void
    {
        Storage::fake('local');

        $filePath = 'exports/2026/09/export_test.csv';
        Storage::disk('local')->put($filePath, 'test content');

        $export = ReportExport::create([
            'public_id' => '01HXYZ00000000000000000099',
            'requested_by_user_id' => $this->admin->id,
            'format' => 'CSV',
            'status' => ExportStatus::COMPLETED,
            'filters' => [],
            'row_count' => 10,
            'storage_disk' => 'local',
            'storage_path' => $filePath,
            'file_name' => 'laporan-test.csv',
            'expires_at' => now()->addHours(24),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.reports.download', $export));

        $response->assertOk();

        $export->refresh();
        $this->assertEquals(1, $export->download_count);
        $this->assertNotNull($export->last_downloaded_at);
    }

    public function test_staff_cannot_download_export_file(): void
    {
        $export = ReportExport::create([
            'public_id' => '01HXYZ00000000000000000098',
            'requested_by_user_id' => $this->admin->id,
            'format' => 'CSV',
            'status' => ExportStatus::COMPLETED,
            'filters' => [],
            'row_count' => 10,
            'storage_disk' => 'local',
            'storage_path' => 'exports/path.csv',
            'file_name' => 'laporan.csv',
            'expires_at' => now()->addHours(24),
        ]);

        $response = $this->actingAs($this->staff)->get(route('admin.reports.download', $export));

        $response->assertForbidden();
    }

    public function test_download_fails_if_export_is_expired(): void
    {
        $export = ReportExport::create([
            'public_id' => '01HXYZ00000000000000000097',
            'requested_by_user_id' => $this->admin->id,
            'format' => 'CSV',
            'status' => ExportStatus::COMPLETED,
            'filters' => [],
            'row_count' => 10,
            'storage_disk' => 'local',
            'storage_path' => 'exports/expired.csv',
            'file_name' => 'laporan-expired.csv',
            'expires_at' => now()->subMinute(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.reports.download', $export));

        $response->assertStatus(410);
    }

    public function test_cleanup_command_deletes_expired_files_and_updates_status(): void
    {
        Storage::fake('local');

        $activePath = 'exports/2026/09/active.csv';
        $expiredPath = 'exports/2026/09/expired.csv';

        Storage::disk('local')->put($activePath, 'active');
        Storage::disk('local')->put($expiredPath, 'expired');

        $activeExport = ReportExport::create([
            'public_id' => '01HXYZ00000000000000000081',
            'requested_by_user_id' => $this->admin->id,
            'format' => 'CSV',
            'status' => ExportStatus::COMPLETED,
            'filters' => [],
            'storage_disk' => 'local',
            'storage_path' => $activePath,
            'file_name' => 'active.csv',
            'expires_at' => now()->addHours(12),
        ]);

        $expiredExport = ReportExport::create([
            'public_id' => '01HXYZ00000000000000000082',
            'requested_by_user_id' => $this->admin->id,
            'format' => 'CSV',
            'status' => ExportStatus::COMPLETED,
            'filters' => [],
            'storage_disk' => 'local',
            'storage_path' => $expiredPath,
            'file_name' => 'expired.csv',
            'expires_at' => now()->subHours(2),
        ]);

        $this->artisan('sitamu:exports:cleanup')
            ->expectsOutputToContain('Berhasil membersihkan 1 berkas ekspor kedaluwarsa.')
            ->assertSuccessful();

        // Expired export file deleted and status set to EXPIRED
        Storage::disk('local')->assertMissing($expiredPath);
        $expiredExport->refresh();
        $this->assertEquals(ExportStatus::EXPIRED, $expiredExport->status);

        // Active export remains COMPLETED and file intact
        Storage::disk('local')->assertExists($activePath);
        $activeExport->refresh();
        $this->assertEquals(ExportStatus::COMPLETED, $activeExport->status);
    }
}
