<?php

namespace Tests\Feature\Admin;

use App\Domain\Access\Enums\RoleCode;
use App\Domain\Visits\Enums\RegistrationSource;
use App\Domain\Visits\Enums\VisitStatus;
use App\Domain\Visits\Exceptions\InvalidVisitStatusTransitionException;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use App\Models\Visit;
use Database\Seeders\OrganizationSettingsSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisitManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $staff;

    protected Department $deptA;

    protected Department $deptB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(OrganizationSettingsSeeder::class);

        $this->deptA = Department::create([
            'public_id' => '01HXYZ00000000000000000001',
            'code' => 'SEKRETARIAT',
            'name' => 'Sekretariat Utama',
            'is_active' => true,
        ]);

        $this->deptB = Department::create([
            'public_id' => '01HXYZ00000000000000000002',
            'code' => 'KEUANGAN',
            'name' => 'Bagian Keuangan',
            'is_active' => true,
        ]);

        $this->staff = User::create([
            'public_id' => '01HXYZ00000000000000000010',
            'name' => 'Siti Nurhaliza (Staff)',
            'email' => 'siti@sitamu.local',
            'password' => bcrypt('Password123!'),
            'is_active' => true,
        ]);
        $staffRole = Role::where('slug', RoleCode::STAFF->value)->first();
        $this->staff->roles()->attach($staffRole->id, ['assigned_at' => now()]);
    }

    public function test_staff_can_view_visits_list(): void
    {
        Visit::create([
            'public_id' => '01HXYZ00000000000000000021',
            'visitor_name' => 'Budi Santoso',
            'origin_organization' => 'PT Maju Terus',
            'department_id' => $this->deptA->id,
            'department_name_snapshot' => $this->deptA->name,
            'purpose' => 'Audiensi Direksi',
            'current_status' => VisitStatus::REGISTERED,
            'registration_source' => RegistrationSource::PUBLIC,
            'registered_at' => now(),
            'status_updated_at' => now(),
        ]);

        $response = $this->actingAs($this->staff)->get(route('admin.visits.index'));

        $response->assertOk();
        $response->assertSee('Budi Santoso');
        $response->assertSee('PT Maju Terus');
        $response->assertSee('Sekretariat Utama');
    }

    public function test_staff_can_search_visits_by_keyword(): void
    {
        Visit::create([
            'public_id' => '01HXYZ00000000000000000021',
            'visitor_name' => 'Budi Santoso',
            'origin_organization' => 'PT Harapan Jaya',
            'department_id' => $this->deptA->id,
            'department_name_snapshot' => $this->deptA->name,
            'purpose' => 'Konsultasi',
            'current_status' => VisitStatus::REGISTERED,
            'registration_source' => RegistrationSource::PUBLIC,
            'registered_at' => now(),
            'status_updated_at' => now(),
        ]);

        Visit::create([
            'public_id' => '01HXYZ00000000000000000022',
            'visitor_name' => 'Dewi Persik',
            'origin_organization' => 'CV Sumber Makmur',
            'department_id' => $this->deptB->id,
            'department_name_snapshot' => $this->deptB->name,
            'purpose' => 'Pengadaan',
            'current_status' => VisitStatus::REGISTERED,
            'registration_source' => RegistrationSource::PUBLIC,
            'registered_at' => now(),
            'status_updated_at' => now(),
        ]);

        $response = $this->actingAs($this->staff)->get(route('admin.visits.index', ['q' => 'Budi']));
        $response->assertOk();
        $response->assertSee('Budi Santoso');
        $response->assertDontSee('Dewi Persik');
    }

    public function test_staff_can_filter_visits_by_department_and_status(): void
    {
        Visit::create([
            'public_id' => '01HXYZ00000000000000000021',
            'visitor_name' => 'Tamu Dept A',
            'origin_organization' => 'Org A',
            'department_id' => $this->deptA->id,
            'department_name_snapshot' => $this->deptA->name,
            'purpose' => 'Tujuan A',
            'current_status' => VisitStatus::ARRIVED,
            'registration_source' => RegistrationSource::PUBLIC,
            'registered_at' => now(),
            'status_updated_at' => now(),
        ]);

        Visit::create([
            'public_id' => '01HXYZ00000000000000000022',
            'visitor_name' => 'Tamu Dept B',
            'origin_organization' => 'Org B',
            'department_id' => $this->deptB->id,
            'department_name_snapshot' => $this->deptB->name,
            'purpose' => 'Tujuan B',
            'current_status' => VisitStatus::REGISTERED,
            'registration_source' => RegistrationSource::PUBLIC,
            'registered_at' => now(),
            'status_updated_at' => now(),
        ]);

        $response = $this->actingAs($this->staff)->get(route('admin.visits.index', [
            'department_id' => $this->deptA->id,
            'current_status' => VisitStatus::ARRIVED->value,
        ]));

        $response->assertOk();
        $response->assertSee('Tamu Dept A');
        $response->assertDontSee('Tamu Dept B');
    }

    public function test_staff_can_view_visit_detail(): void
    {
        $visit = Visit::create([
            'public_id' => '01HXYZ00000000000000000021',
            'visitor_name' => 'Budi Santoso',
            'origin_organization' => 'PT Maju Terus',
            'department_id' => $this->deptA->id,
            'department_name_snapshot' => $this->deptA->name,
            'purpose' => 'Audiensi Kerja Sama',
            'current_status' => VisitStatus::REGISTERED,
            'registration_source' => RegistrationSource::PUBLIC,
            'party_size' => 2,
            'registered_at' => now(),
            'status_updated_at' => now(),
        ]);

        $response = $this->actingAs($this->staff)->get(route('admin.visits.show', $visit));

        $response->assertOk();
        $response->assertSee('Budi Santoso');
        $response->assertSee('Audiensi Kerja Sama');
        $response->assertSee('Tandai Tiba');
        $response->assertSee('Batalkan Kunjungan');
    }

    public function test_staff_can_update_visit_status_sequentially(): void
    {
        $visit = Visit::create([
            'public_id' => '01HXYZ00000000000000000021',
            'visitor_name' => 'Budi Santoso',
            'origin_organization' => 'PT Maju Terus',
            'department_id' => $this->deptA->id,
            'department_name_snapshot' => $this->deptA->name,
            'purpose' => 'Audiensi Kerja Sama',
            'current_status' => VisitStatus::REGISTERED,
            'registration_source' => RegistrationSource::PUBLIC,
            'registered_at' => now(),
            'status_updated_at' => now(),
        ]);

        // 1. Transition: REGISTERED -> ARRIVED
        $res1 = $this->actingAs($this->staff)->patch(route('admin.visits.status.update', $visit), [
            'target_status' => VisitStatus::ARRIVED->value,
        ]);
        $res1->assertRedirect(route('admin.visits.show', $visit));
        $this->assertEquals(VisitStatus::ARRIVED, $visit->fresh()->current_status);

        // 2. Transition: ARRIVED -> IN_SERVICE
        $res2 = $this->actingAs($this->staff)->patch(route('admin.visits.status.update', $visit), [
            'target_status' => VisitStatus::IN_SERVICE->value,
        ]);
        $res2->assertRedirect(route('admin.visits.show', $visit));
        $this->assertEquals(VisitStatus::IN_SERVICE, $visit->fresh()->current_status);

        // 3. Transition: IN_SERVICE -> COMPLETED
        $res3 = $this->actingAs($this->staff)->patch(route('admin.visits.status.update', $visit), [
            'target_status' => VisitStatus::COMPLETED->value,
        ]);
        $res3->assertRedirect(route('admin.visits.show', $visit));
        $this->assertEquals(VisitStatus::COMPLETED, $visit->fresh()->current_status);

        // Check history count (3 status history records created)
        $this->assertEquals(3, $visit->statusHistories()->count());
    }

    public function test_staff_can_cancel_visit_with_mandatory_reason(): void
    {
        $visit = Visit::create([
            'public_id' => '01HXYZ00000000000000000021',
            'visitor_name' => 'Budi Santoso',
            'origin_organization' => 'PT Maju Terus',
            'department_id' => $this->deptA->id,
            'department_name_snapshot' => $this->deptA->name,
            'purpose' => 'Audiensi Kerja Sama',
            'current_status' => VisitStatus::REGISTERED,
            'registration_source' => RegistrationSource::PUBLIC,
            'registered_at' => now(),
            'status_updated_at' => now(),
        ]);

        // Attempt without reason must fail validation
        $failRes = $this->actingAs($this->staff)->from(route('admin.visits.show', $visit))
            ->patch(route('admin.visits.status.update', $visit), [
                'target_status' => VisitStatus::CANCELLED->value,
                'reason' => '',
            ]);
        $failRes->assertRedirect(route('admin.visits.show', $visit));
        $failRes->assertSessionHasErrors(['reason']);
        $this->assertEquals(VisitStatus::REGISTERED, $visit->fresh()->current_status);

        // Attempt with valid reason succeeds
        $successRes = $this->actingAs($this->staff)
            ->patch(route('admin.visits.status.update', $visit), [
                'target_status' => VisitStatus::CANCELLED->value,
                'reason' => 'Tamu membatalkan kedatangan karena hujan deras.',
            ]);
        $successRes->assertRedirect(route('admin.visits.show', $visit));
        $this->assertEquals(VisitStatus::CANCELLED, $visit->fresh()->current_status);
    }

    public function test_illegal_status_transition_throws_exception_or_rolls_back(): void
    {
        $visit = Visit::create([
            'public_id' => '01HXYZ00000000000000000021',
            'visitor_name' => 'Budi Santoso',
            'origin_organization' => 'PT Maju Terus',
            'department_id' => $this->deptA->id,
            'department_name_snapshot' => $this->deptA->name,
            'purpose' => 'Audiensi Kerja Sama',
            'current_status' => VisitStatus::REGISTERED,
            'registration_source' => RegistrationSource::PUBLIC,
            'registered_at' => now(),
            'status_updated_at' => now(),
        ]);

        // Attempt direct jump from REGISTERED to COMPLETED (violates state machine)
        $this->expectException(InvalidVisitStatusTransitionException::class);

        $this->withoutExceptionHandling()
            ->actingAs($this->staff)
            ->patch(route('admin.visits.status.update', $visit), [
                'target_status' => VisitStatus::COMPLETED->value,
            ]);
    }
}
