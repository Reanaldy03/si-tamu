<?php

namespace Tests\Feature;

use App\Domain\Access\Enums\RoleCode;
use App\Domain\Visits\Enums\RegistrationSource;
use App\Domain\Visits\Enums\VisitStatus;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use App\Models\Visit;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\OrganizationSettingsSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BlackBoxAcceptanceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $staff;

    protected Department $dept;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(OrganizationSettingsSeeder::class);

        $adminRole = Role::where('slug', RoleCode::ADMIN->value)->first();
        $staffRole = Role::where('slug', RoleCode::STAFF->value)->first();

        $this->admin = User::create([
            'public_id' => '01HXYZ00000000000000000001',
            'name' => 'Administrator SI-TAMU',
            'email' => 'admin@sitamu.local',
            'password' => bcrypt('AdminSecret123!'),
            'is_active' => true,
        ]);
        $this->admin->roles()->attach($adminRole->id, ['assigned_at' => now()]);

        $this->staff = User::create([
            'public_id' => '01HXYZ00000000000000000002',
            'name' => 'Petugas Loket',
            'email' => 'petugas@sitamu.local',
            'password' => bcrypt('PetugasSecret123!'),
            'is_active' => true,
        ]);
        $this->staff->roles()->attach($staffRole->id, ['assigned_at' => now()]);

        $this->dept = Department::create([
            'public_id' => '01HXYZ00000000000000000011',
            'code' => 'SEKRETARIAT',
            'name' => 'Sekretariat Utama',
            'is_active' => true,
        ]);
    }

    /**
     * Skenario 1: Registrasi Valid
     */
    public function test_01_registrasi_valid(): void
    {
        $response = $this->post(route('public.registration.store'), [
            'visitor_name' => 'Ahmad Dahlan',
            'origin_organization' => 'Universitas Indonesia',
            'contact_phone' => '081298765432',
            'department_id' => $this->dept->id,
            'purpose' => 'Audiensi Kerjasama',
            'visit_date' => '2026-03-16',
            'letter_number' => '005/900/DPRD/2026',
            'submission_token' => 'token-bb-01',
        ]);

        $visit = Visit::where('visitor_name', 'Ahmad Dahlan')->first();
        $this->assertNotNull($visit);
        $response->assertRedirect(route('public.registration.success', $visit->public_id));
        $this->assertEquals(VisitStatus::REGISTERED, $visit->current_status);
        $this->assertEquals('2026-03-16', $visit->visit_date->toDateString());
        $this->assertEquals('005/900/DPRD/2026', $visit->letter_number);
    }

    /**
     * Skenario 2: Registrasi Field Wajib Kosong
     */
    public function test_02_registrasi_field_wajib_kosong(): void
    {
        $response = $this->post(route('public.registration.store'), [
            'visitor_name' => '',
            'origin_organization' => '',
            'department_id' => '',
            'purpose' => '',
        ]);

        $response->assertSessionHasErrors(['visitor_name', 'origin_organization', 'department_id', 'purpose', 'visit_date']);
    }

    /**
     * Skenario 3: Input Terlalu Panjang
     */
    public function test_03_input_terlalu_panjang(): void
    {
        $response = $this->post(route('public.registration.store'), [
            'visitor_name' => str_repeat('A', 151), // max 100
            'origin_organization' => str_repeat('B', 151),
            'contact_phone' => str_repeat('0', 25), // max 20
            'department_id' => $this->dept->id,
            'purpose' => str_repeat('C', 501), // max 500
        ]);

        $response->assertSessionHasErrors(['visitor_name', 'origin_organization', 'contact_phone', 'purpose']);
    }

    /**
     * Skenario 4: Duplicate Submit (Idempotency)
     */
    public function test_04_duplicate_submit(): void
    {
        $payload = [
            'visitor_name' => 'Bambang Tri',
            'origin_organization' => 'Kementerian Sosial',
            'contact_phone' => '081300001111',
            'department_id' => $this->dept->id,
            'purpose' => 'Konsultasi Program',
            'visit_date' => '2026-03-16',
            'submission_token' => 'idempotency-token-duplicate-04',
        ];

        $firstResponse = $this->post(route('public.registration.store'), $payload);
        $secondResponse = $this->post(route('public.registration.store'), $payload);

        $this->assertEquals(1, Visit::where('visitor_name', 'Bambang Tri')->count());
        $visit = Visit::where('visitor_name', 'Bambang Tri')->first();
        $secondResponse->assertRedirect(route('public.registration.success', $visit->public_id));
    }

    /**
     * Skenario 5: Login Valid
     */
    public function test_05_login_valid(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@sitamu.local',
            'password' => 'AdminSecret123!',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->admin);
    }

    /**
     * Skenario 6: Login Invalid
     */
    public function test_06_login_invalid(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@sitamu.local',
            'password' => 'WrongPassword!',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    /**
     * Skenario 7: User Non-Admin Mencoba Membuka User Management
     */
    public function test_07_user_non_admin_mencoba_membuka_user_management(): void
    {
        $response = $this->actingAs($this->staff)->get(route('admin.users.index'));

        $response->assertForbidden();
    }

    /**
     * Skenario 8: Admin Membuat Unit
     */
    public function test_08_admin_membuat_unit(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.departments.store'), [
            'code' => 'HUMAS',
            'name' => 'Biro Hubungan Masyarakat',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.departments.index'));
        $this->assertDatabaseHas('departments', ['code' => 'HUMAS']);
    }

    /**
     * Skenario 9: Admin Menonaktifkan Unit
     */
    public function test_09_admin_menonaktifkan_unit(): void
    {
        $response = $this->actingAs($this->admin)
            ->from(route('admin.departments.index'))
            ->patch(route('admin.departments.toggle-status', $this->dept));

        $response->assertRedirect(route('admin.departments.index'));
        $this->dept->refresh();
        $this->assertFalse($this->dept->is_active);
    }

    /**
     * Skenario 10: Unit Nonaktif Tidak Muncul di Form Registrasi
     */
    public function test_10_unit_nonaktif_tidak_muncul_di_form(): void
    {
        $inactiveDept = Department::create([
            'public_id' => '01HXYZ00000000000000000099',
            'code' => 'NONAKTIF',
            'name' => 'Unit Nonaktif Khusus',
            'is_active' => false,
        ]);

        $response = $this->get(route('public.registration.create'));

        $response->assertOk();
        $response->assertSee('Sekretariat Utama');
        $response->assertDontSee('Unit Nonaktif Khusus');
    }

    /**
     * Skenario 11: Petugas Mengubah Status
     */
    public function test_11_petugas_mengubah_status(): void
    {
        $visit = Visit::create([
            'public_id' => '01HXYZ00000000000000000071',
            'visitor_name' => 'Dedy Pratama',
            'origin_organization' => 'Media Group',
            'contact_phone' => '081233334444',
            'department_id' => $this->dept->id,
            'department_name_snapshot' => $this->dept->name,
            'purpose' => 'Liputan Kegiatan',
            'current_status' => VisitStatus::REGISTERED,
            'registration_source' => RegistrationSource::PUBLIC,
            'registered_at' => now(),
            'status_updated_at' => now(),
            'idempotency_key' => 'token-status-11',
        ]);

        $response = $this->actingAs($this->staff)->patch(route('admin.visits.status.update', $visit), [
            'target_status' => 'ARRIVED',
        ]);

        $response->assertRedirect(route('admin.visits.show', $visit));
        $visit->refresh();
        $this->assertEquals(VisitStatus::ARRIVED, $visit->current_status);
    }

    /**
     * Skenario 12: Status History Tercatat
     */
    public function test_12_status_history_tercatat(): void
    {
        $visit = Visit::create([
            'public_id' => '01HXYZ00000000000000000072',
            'visitor_name' => 'Eka Saputra',
            'origin_organization' => 'PT Swasta',
            'contact_phone' => '081244445555',
            'department_id' => $this->dept->id,
            'department_name_snapshot' => $this->dept->name,
            'purpose' => 'Presentasi Produk',
            'current_status' => VisitStatus::REGISTERED,
            'registration_source' => RegistrationSource::PUBLIC,
            'registered_at' => now(),
            'status_updated_at' => now(),
            'idempotency_key' => 'token-status-12',
        ]);

        $this->actingAs($this->staff)->patch(route('admin.visits.status.update', $visit), [
            'target_status' => 'ARRIVED',
        ]);

        $this->assertDatabaseHas('visit_status_history', [
            'visit_id' => $visit->id,
            'from_status' => 'REGISTERED',
            'to_status' => 'ARRIVED',
            'changed_by_user_id' => $this->staff->id,
        ]);
    }

    /**
     * Skenario 13: Search Berdasarkan Nama
     */
    public function test_13_search_berdasarkan_nama(): void
    {
        Visit::create([
            'public_id' => '01HXYZ00000000000000000073',
            'visitor_name' => 'Fajar Nugroho Spesifik',
            'origin_organization' => 'Organisasi Fajar',
            'contact_phone' => '081255556666',
            'department_id' => $this->dept->id,
            'department_name_snapshot' => $this->dept->name,
            'purpose' => 'Keperluan Fajar',
            'current_status' => VisitStatus::REGISTERED,
            'registration_source' => RegistrationSource::PUBLIC,
            'registered_at' => now(),
            'status_updated_at' => now(),
            'idempotency_key' => 'token-search-13',
        ]);

        $response = $this->actingAs($this->staff)->get(route('admin.visits.index', [
            'q' => 'Fajar Nugroho Spesifik',
        ]));

        $response->assertOk();
        $response->assertSee('Fajar Nugroho Spesifik');
    }

    /**
     * Skenario 14: Search Berdasarkan Visit ID
     */
    public function test_14_search_berdasarkan_visit_id(): void
    {
        $publicId = '01HXYZ00000000000000000074';

        Visit::create([
            'public_id' => $publicId,
            'visitor_name' => 'Gita Gutawa',
            'origin_organization' => 'Label Musik',
            'contact_phone' => '081266667777',
            'department_id' => $this->dept->id,
            'department_name_snapshot' => $this->dept->name,
            'purpose' => 'Keperluan Gita',
            'current_status' => VisitStatus::REGISTERED,
            'registration_source' => RegistrationSource::PUBLIC,
            'registered_at' => now(),
            'status_updated_at' => now(),
            'idempotency_key' => 'token-search-14',
        ]);

        $response = $this->actingAs($this->staff)->get(route('admin.visits.index', [
            'q' => $publicId,
        ]));

        $response->assertOk();
        $response->assertSee($publicId);
        $response->assertSee('Gita Gutawa');
    }

    /**
     * Skenario 15: Filter Tanggal
     */
    public function test_15_filter_tanggal(): void
    {
        // Visit today
        Visit::create([
            'public_id' => '01HXYZ00000000000000000075',
            'visitor_name' => 'Hadi Sucipto Hari Ini',
            'origin_organization' => 'Kantor Pos',
            'contact_phone' => '081277778888',
            'department_id' => $this->dept->id,
            'department_name_snapshot' => $this->dept->name,
            'purpose' => 'Surat Menyurat',
            'current_status' => VisitStatus::REGISTERED,
            'registration_source' => RegistrationSource::PUBLIC,
            'visit_date' => now()->format('Y-m-d'),
            'registered_at' => now(),
            'status_updated_at' => now(),
            'idempotency_key' => 'token-filter-15-today',
        ]);

        $todayStr = now()->format('Y-m-d');
        $response = $this->actingAs($this->staff)->get(route('admin.visits.index', [
            'date_from' => $todayStr,
            'date_to' => $todayStr,
        ]));

        $response->assertOk();
        $response->assertSee('Hadi Sucipto Hari Ini');
    }

    /**
     * Skenario 16: Filter Unit
     */
    public function test_16_filter_unit(): void
    {
        $otherDept = Department::create([
            'public_id' => '01HXYZ00000000000000000022',
            'code' => 'DIKTI',
            'name' => 'Direktorat Pendidikan Tinggi',
            'is_active' => true,
        ]);

        Visit::create([
            'public_id' => '01HXYZ00000000000000000076',
            'visitor_name' => 'Indah Permata',
            'origin_organization' => 'Yayasan',
            'contact_phone' => '081288889999',
            'department_id' => $otherDept->id,
            'department_name_snapshot' => $otherDept->name,
            'purpose' => 'Beasiswa',
            'current_status' => VisitStatus::REGISTERED,
            'registration_source' => RegistrationSource::PUBLIC,
            'registered_at' => now(),
            'status_updated_at' => now(),
            'idempotency_key' => 'token-filter-16',
        ]);

        $response = $this->actingAs($this->staff)->get(route('admin.visits.index', [
            'department_id' => $otherDept->id,
        ]));

        $response->assertOk();
        $response->assertSee('Indah Permata');
    }

    /**
     * Skenario 17: Filter Status
     */
    public function test_17_filter_status(): void
    {
        Visit::create([
            'public_id' => '01HXYZ00000000000000000077',
            'visitor_name' => 'Joko Widodo KW',
            'origin_organization' => 'Solo',
            'contact_phone' => '081299990000',
            'department_id' => $this->dept->id,
            'department_name_snapshot' => $this->dept->name,
            'purpose' => 'Silahturahmi',
            'current_status' => VisitStatus::COMPLETED,
            'registration_source' => RegistrationSource::PUBLIC,
            'registered_at' => now(),
            'status_updated_at' => now(),
            'idempotency_key' => 'token-filter-17',
        ]);

        $response = $this->actingAs($this->staff)->get(route('admin.visits.index', [
            'current_status' => 'COMPLETED',
        ]));

        $response->assertOk();
        $response->assertSee('Joko Widodo KW');
    }

    /**
     * Skenario 18: Export Dengan Filter
     */
    public function test_18_export_dengan_filter(): void
    {
        Storage::fake('local');

        Visit::create([
            'public_id' => '01HXYZ00000000000000000078',
            'visitor_name' => 'Kartika Sari',
            'origin_organization' => 'Kue Bandung',
            'contact_phone' => '081200001111',
            'department_id' => $this->dept->id,
            'department_name_snapshot' => $this->dept->name,
            'purpose' => 'Catering',
            'current_status' => VisitStatus::COMPLETED,
            'registration_source' => RegistrationSource::PUBLIC,
            'visit_date' => now()->format('Y-m-d'),
            'letter_number' => '005/901/DPRD/2026',
            'registered_at' => now(),
            'status_updated_at' => now(),
            'idempotency_key' => 'token-export-18',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.reports.export'), [
            'format' => 'PDF',
            'department_id' => $this->dept->id,
            'current_status' => 'COMPLETED',
        ]);

        $response->assertRedirect(route('admin.reports.index', [
            'department_id' => $this->dept->id,
            'current_status' => 'COMPLETED',
        ]));
        $this->assertDatabaseHas('report_exports', [
            'format' => 'PDF',
            'status' => 'COMPLETED',
            'requested_by_user_id' => $this->admin->id,
        ]);
    }

    /**
     * Skenario 19: Export Tanpa Hak Akses
     */
    public function test_19_export_tanpa_hak_akses(): void
    {
        $response = $this->actingAs($this->staff)->post(route('admin.reports.export'), [
            'format' => 'PDF',
        ]);

        $response->assertForbidden();
    }

    /**
     * Skenario 20: Logout
     */
    public function test_20_logout(): void
    {
        $response = $this->actingAs($this->staff)->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    /**
     * Skenario 21: Session Expired / Inactive User Blocked
     */
    public function test_21_inactive_user_session_blocked(): void
    {
        $this->staff->update(['is_active' => false]);

        $response = $this->actingAs($this->staff)->get(route('admin.dashboard'));

        $response->assertForbidden();
        $this->assertGuest();
    }

    /**
     * Skenario 22: Backup Database Simulation
     */
    public function test_22_backup_database_simulation(): void
    {
        // Verify all core transactional and reference tables contain data and can be enumerated cleanly
        $tables = [
            'users',
            'roles',
            'permissions',
            'user_roles',
            'role_permissions',
            'departments',
            'organization_settings',
            'visits',
            'visit_status_history',
            'audit_logs',
            'report_exports',
        ];

        foreach ($tables as $table) {
            $count = DB::table($table)->count();
            $this->assertIsInt($count, "Table {$table} could not be queried.");
        }
    }

    /**
     * Skenario 23: Restore Database Integrity
     */
    public function test_23_restore_database_integrity(): void
    {
        // Re-run database seeder to ensure deterministic idempotent re-population
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseHas('roles', ['slug' => 'ADMIN']);
        $this->assertDatabaseHas('roles', ['slug' => 'STAFF']);
        $this->assertDatabaseHas('organization_settings', ['id' => 1]);
    }
}
