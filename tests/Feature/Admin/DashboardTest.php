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

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $staff;

    protected Department $department;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(OrganizationSettingsSeeder::class);

        $this->department = Department::create([
            'public_id' => '01HXYZ00000000000000000001',
            'code' => 'SEKRETARIAT',
            'name' => 'Sekretariat Utama',
            'is_active' => true,
        ]);

        $this->staff = User::create([
            'public_id' => '01HXYZ00000000000000000010',
            'name' => 'Staff Front-Office',
            'email' => 'staff@sitamu.local',
            'password' => bcrypt('Password123!'),
            'is_active' => true,
        ]);
        $staffRole = Role::where('slug', RoleCode::STAFF->value)->first();
        $this->staff->roles()->attach($staffRole->id, ['assigned_at' => now()]);
    }

    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_staff_can_view_dashboard_with_metrics(): void
    {
        // 1. Visit registered today
        Visit::create([
            'public_id' => '01HXYZ00000000000000000021',
            'visitor_name' => 'Tamu Pertama',
            'origin_organization' => 'Instansi A',
            'party_size' => 1,
            'department_id' => $this->department->id,
            'department_name_snapshot' => $this->department->name,
            'purpose' => 'Keperluan 1',
            'current_status' => VisitStatus::REGISTERED,
            'registration_source' => RegistrationSource::PUBLIC,
            'registered_at' => now(),
            'status_updated_at' => now(),
        ]);

        // 2. Visit in service today
        Visit::create([
            'public_id' => '01HXYZ00000000000000000022',
            'visitor_name' => 'Tamu Kedua',
            'origin_organization' => 'Instansi B',
            'party_size' => 2,
            'department_id' => $this->department->id,
            'department_name_snapshot' => $this->department->name,
            'purpose' => 'Keperluan 2',
            'current_status' => VisitStatus::IN_SERVICE,
            'registration_source' => RegistrationSource::PUBLIC,
            'registered_at' => now(),
            'status_updated_at' => now(),
        ]);

        // 3. Visit completed today
        Visit::create([
            'public_id' => '01HXYZ00000000000000000023',
            'visitor_name' => 'Tamu Ketiga',
            'origin_organization' => 'Instansi C',
            'party_size' => 1,
            'department_id' => $this->department->id,
            'department_name_snapshot' => $this->department->name,
            'purpose' => 'Keperluan 3',
            'current_status' => VisitStatus::COMPLETED,
            'registration_source' => RegistrationSource::PUBLIC,
            'registered_at' => now(),
            'status_updated_at' => now(),
        ]);

        // 4. Visit yesterday (should not count in today metrics)
        Visit::create([
            'public_id' => '01HXYZ00000000000000000024',
            'visitor_name' => 'Tamu Kemarin',
            'origin_organization' => 'Instansi D',
            'party_size' => 1,
            'department_id' => $this->department->id,
            'department_name_snapshot' => $this->department->name,
            'purpose' => 'Keperluan 4',
            'current_status' => VisitStatus::COMPLETED,
            'registration_source' => RegistrationSource::PUBLIC,
            'registered_at' => now()->subDays(2),
            'status_updated_at' => now()->subDays(2),
        ]);

        $response = $this->actingAs($this->staff)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Ringkasan Hari Ini');
        $response->assertSee('Tamu Pertama');
        $response->assertSee('Tamu Kedua');
        $response->assertSee('Tamu Ketiga');

        // The metric row stays scoped to today, so yesterday's visit must not
        // be counted even though it now appears in the "latest visits" panel.
        $this->assertSame(3, $response->viewData('totalToday'));
    }

    public function test_dashboard_displays_empty_state_when_no_visits_today(): void
    {
        $response = $this->actingAs($this->staff)->get(route('admin.dashboard'));

        $response->assertOk();

        // Nothing is waiting and nothing is registered today.
        $this->assertSame(0, $response->viewData('totalToday'));
        $this->assertSame(0, $response->viewData('waitingCount'));
        $response->assertSee('Antrian bersih');
    }

    public function test_dashboard_supports_flexible_trend_periods(): void
    {
        $response7d = $this->actingAs($this->staff)->get(route('admin.dashboard', ['period' => '7d']));
        $response7d->assertOk();
        $response7d->assertSee('Tren Kunjungan Mingguan');

        $response30d = $this->actingAs($this->staff)->get(route('admin.dashboard', ['period' => '30d']));
        $response30d->assertOk();
        $response30d->assertSee('Tren Kunjungan Bulanan');

        $response12m = $this->actingAs($this->staff)->get(route('admin.dashboard', ['period' => '12m']));
        $response12m->assertOk();
        $response12m->assertSee('Tren Kunjungan Tahunan');

        $responseYear = $this->actingAs($this->staff)->get(route('admin.dashboard', ['period' => 'this_year']));
        $responseYear->assertOk();
        $responseYear->assertSee('Tren Kunjungan Tahun');
    }
}
