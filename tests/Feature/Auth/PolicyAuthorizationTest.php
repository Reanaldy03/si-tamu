<?php

namespace Tests\Feature\Auth;

use App\Models\Department;
use App\Models\OrganizationSetting;
use App\Models\Role;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Tests\TestCase;

class PolicyAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected User $staffUser;

    protected Visit $visit;

    protected Department $department;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $adminRole = Role::where('slug', 'ADMIN')->first();
        $staffRole = Role::where('slug', 'STAFF')->first();

        $this->adminUser = User::create([
            'public_id' => (string) Str::ulid(),
            'name' => 'Admin User',
            'email' => 'admin@sitamu.local',
            'password' => 'secret123',
            'is_active' => true,
        ]);
        $this->adminUser->roles()->attach($adminRole->id, ['assigned_at' => now()]);

        $this->staffUser = User::create([
            'public_id' => (string) Str::ulid(),
            'name' => 'Staff User',
            'email' => 'staff@sitamu.local',
            'password' => 'secret123',
            'is_active' => true,
        ]);
        $this->staffUser->roles()->attach($staffRole->id, ['assigned_at' => now()]);

        $this->department = Department::create([
            'public_id' => (string) Str::ulid(),
            'code' => 'UMUM',
            'name' => 'Bagian Umum',
            'is_active' => true,
        ]);

        $this->visit = Visit::create([
            'public_id' => (string) Str::ulid(),
            'visitor_name' => 'Tamu Percobaan',
            'origin_organization' => 'Instansi Tamu',
            'department_id' => $this->department->id,
            'department_name_snapshot' => $this->department->name,
            'purpose' => 'Keperluan pengujian policy',
            'current_status' => 'REGISTERED',
            'registered_at' => now(),
            'status_updated_at' => now(),
        ]);
    }

    public function test_admin_has_full_permissions(): void
    {
        $this->actingAs($this->adminUser);

        // Visits
        $this->assertTrue(Gate::allows('viewAny', Visit::class));
        $this->assertTrue(Gate::allows('view', $this->visit));
        $this->assertTrue(Gate::allows('updateStatus', $this->visit));
        $this->assertTrue(Gate::allows('correct', $this->visit));

        // Departments & Users
        $this->assertTrue(Gate::allows('manage', Department::class));
        $this->assertTrue(Gate::allows('manage', User::class));

        // Reports, Settings, Audit
        $this->assertTrue(Gate::allows('reports.view'));
        $this->assertTrue(Gate::allows('reports.export'));
        $this->assertTrue(Gate::allows('manage', OrganizationSetting::class));
        $this->assertTrue(Gate::allows('audit.view'));
    }

    public function test_staff_has_operational_permissions_and_is_denied_admin_privileges(): void
    {
        $this->actingAs($this->staffUser);

        // Staff CAN view visits & update status
        $this->assertTrue(Gate::allows('viewAny', Visit::class));
        $this->assertTrue(Gate::allows('view', $this->visit));
        $this->assertTrue(Gate::allows('updateStatus', $this->visit));

        // Staff CAN view reports
        $this->assertTrue(Gate::allows('reports.view'));

        // Staff CANNOT correct visit data
        $this->assertFalse(Gate::allows('correct', $this->visit));

        // Staff CANNOT manage departments or users
        $this->assertFalse(Gate::allows('manage', Department::class));
        $this->assertFalse(Gate::allows('manage', User::class));

        // Staff CANNOT export reports, manage settings, or view audit logs
        $this->assertFalse(Gate::allows('reports.export'));
        $this->assertFalse(Gate::allows('manage', OrganizationSetting::class));
        $this->assertFalse(Gate::allows('audit.view'));
    }
}
