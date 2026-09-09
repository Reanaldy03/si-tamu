<?php

namespace Tests\Feature\Admin;

use App\Domain\Access\Enums\RoleCode;
use App\Domain\Visits\Enums\RegistrationSource;
use App\Domain\Visits\Enums\VisitStatus;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use App\Models\Visit;
use Database\Seeders\OrganizationSettingsSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $staff;

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
    }

    public function test_admin_can_view_departments_list(): void
    {
        Department::create([
            'public_id' => '01HXYZ00000000000000000011',
            'code' => 'SEKRETARIAT',
            'name' => 'Sekretariat Utama',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.departments.index'));
        $response->assertOk();
        $response->assertSee('Sekretariat Utama');
    }

    public function test_staff_cannot_create_or_modify_departments(): void
    {
        $response = $this->actingAs($this->staff)->get(route('admin.departments.create'));
        $response->assertForbidden();

        $storeResponse = $this->actingAs($this->staff)->post(route('admin.departments.store'), [
            'code' => 'TEST',
            'name' => 'Test Unit',
            'is_active' => 1,
        ]);
        $storeResponse->assertForbidden();
    }

    public function test_admin_can_create_department_with_audit_log(): void
    {
        $payload = [
            'code' => 'KEUANGAN',
            'name' => 'Bagian Keuangan & Anggaran',
            'description' => 'Pengelolaan kas dan anggaran belanja',
            'sort_order' => 2,
            'is_active' => 1,
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.departments.store'), $payload);

        $response->assertRedirect(route('admin.departments.index'));
        $this->assertDatabaseHas('departments', [
            'code' => 'KEUANGAN',
            'name' => 'Bagian Keuangan & Anggaran',
        ]);

        $department = Department::where('code', 'KEUANGAN')->first();
        $this->assertNotNull($department->public_id);

        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'department.created',
            'actor_user_id' => $this->admin->id,
            'target_id' => $department->id,
        ]);
    }

    public function test_admin_can_update_department(): void
    {
        $dept = Department::create([
            'public_id' => '01HXYZ00000000000000000011',
            'code' => 'UMUM',
            'name' => 'Bagian Umum Lama',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.departments.update', $dept), [
            'code' => 'UMUM',
            'name' => 'Bagian Umum & Protokol Baru',
            'description' => 'Deskripsi diperbarui',
            'sort_order' => 5,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.departments.index'));
        $this->assertEquals('Bagian Umum & Protokol Baru', $dept->fresh()->name);
        $this->assertEquals(5, $dept->fresh()->sort_order);

        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'department.updated',
            'target_id' => $dept->id,
        ]);
    }

    public function test_admin_can_toggle_department_active_status(): void
    {
        $dept = Department::create([
            'public_id' => '01HXYZ00000000000000000011',
            'code' => 'TIK',
            'name' => 'Pusat Komputer',
            'is_active' => true,
        ]);

        // Toggle from active to inactive
        $res = $this->actingAs($this->admin)->patch(route('admin.departments.toggle-status', $dept));
        $res->assertRedirect();
        $this->assertFalse($dept->fresh()->is_active);

        // Toggle back to active
        $this->actingAs($this->admin)->patch(route('admin.departments.toggle-status', $dept));
        $this->assertTrue($dept->fresh()->is_active);
    }

    public function test_department_deletion_is_prevented_if_visits_exist(): void
    {
        $dept = Department::create([
            'public_id' => '01HXYZ00000000000000000011',
            'code' => 'REKTORAT',
            'name' => 'Kantor Rektorat',
            'is_active' => true,
        ]);

        Visit::create([
            'public_id' => '01HXYZ00000000000000000021',
            'visitor_name' => 'Tamu Khusus',
            'origin_organization' => 'Kementerian',
            'department_id' => $dept->id,
            'department_name_snapshot' => $dept->name,
            'purpose' => 'Kunjungan Kerja',
            'current_status' => VisitStatus::REGISTERED,
            'registration_source' => RegistrationSource::PUBLIC,
            'registered_at' => now(),
            'status_updated_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.departments.destroy', $dept));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('departments', ['id' => $dept->id]);
    }

    public function test_department_deletion_succeeds_if_no_visits_exist(): void
    {
        $dept = Department::create([
            'public_id' => '01HXYZ00000000000000000011',
            'code' => 'UNUSED',
            'name' => 'Departemen Kosong',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.departments.destroy', $dept));

        $response->assertRedirect(route('admin.departments.index'));
        $this->assertDatabaseMissing('departments', ['id' => $dept->id]);
    }
}
