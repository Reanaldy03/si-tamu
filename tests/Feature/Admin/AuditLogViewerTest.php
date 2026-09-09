<?php

namespace Tests\Feature\Admin;

use App\Domain\Access\Enums\RoleCode;
use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\OrganizationSettingsSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogViewerTest extends TestCase
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

    public function test_admin_can_view_audits_list(): void
    {
        AuditLog::create([
            'event_type' => 'visit.registered',
            'actor_type' => 'PUBLIC',
            'actor_user_id' => null,
            'target_type' => 'visits',
            'target_id' => 10,
            'target_public_id' => '01JVISIT000000000000000001',
            'request_id' => 'req-public-001',
            'ip_hash' => hash('sha256', '127.0.0.1'),
            'metadata' => ['platform' => 'Desktop'],
            'before_state' => null,
            'after_state' => ['status' => 'PENDING'],
            'occurred_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.audits.index'));

        $response->assertOk();
        $response->assertSee('Audit Log Aktivitas');
        $response->assertSee('visit.registered');
        $response->assertSee('01JVISIT000000000000000001');
    }

    public function test_staff_cannot_view_audits_list(): void
    {
        $response = $this->actingAs($this->staff)->get(route('admin.audits.index'));

        $response->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('admin.audits.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_view_audit_detail(): void
    {
        $audit = AuditLog::create([
            'event_type' => 'department.created',
            'actor_type' => 'USER',
            'actor_user_id' => $this->admin->id,
            'target_type' => 'departments',
            'target_id' => 5,
            'target_public_id' => '01JDEPT000000000000000001',
            'request_id' => 'req-admin-002',
            'ip_hash' => hash('sha256', '127.0.0.1'),
            'metadata' => ['action' => 'manual_create'],
            'before_state' => null,
            'after_state' => ['name' => 'Biro Umum', 'code' => 'BIRO-UMUM'],
            'occurred_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.audits.show', $audit));

        $response->assertOk();
        $response->assertSee('Detail Audit #'.$audit->id);
        $response->assertSee('department.created');
        $response->assertSee('req-admin-002');
        $response->assertSee('BIRO-UMUM');
    }

    public function test_staff_cannot_view_audit_detail(): void
    {
        $audit = AuditLog::create([
            'event_type' => 'department.created',
            'actor_type' => 'USER',
            'actor_user_id' => $this->admin->id,
            'target_type' => 'departments',
            'target_id' => 5,
            'target_public_id' => '01JDEPT000000000000000001',
            'request_id' => 'req-admin-002',
            'ip_hash' => hash('sha256', '127.0.0.1'),
            'metadata' => null,
            'before_state' => null,
            'after_state' => null,
            'occurred_at' => now(),
        ]);

        $response = $this->actingAs($this->staff)->get(route('admin.audits.show', $audit));

        $response->assertForbidden();
    }

    public function test_admin_can_filter_audits_by_event_type_and_query(): void
    {
        AuditLog::create([
            'event_type' => 'department.created',
            'actor_type' => 'USER',
            'actor_user_id' => $this->admin->id,
            'target_type' => 'departments',
            'target_id' => 1,
            'target_public_id' => 'TARGET-DEPT-99',
            'request_id' => 'req-1',
            'ip_hash' => hash('sha256', '127.0.0.1'),
            'occurred_at' => now()->subHours(2),
        ]);

        AuditLog::create([
            'event_type' => 'user.updated',
            'actor_type' => 'USER',
            'actor_user_id' => $this->admin->id,
            'target_type' => 'users',
            'target_id' => 2,
            'target_public_id' => 'TARGET-USER-88',
            'request_id' => 'req-2',
            'ip_hash' => hash('sha256', '127.0.0.1'),
            'occurred_at' => now()->subHour(),
        ]);

        // Filter by event_type
        $filterEventResponse = $this->actingAs($this->admin)->get(route('admin.audits.index', [
            'event_type' => 'department.created',
        ]));
        $filterEventResponse->assertOk();
        $filterEventResponse->assertSee('TARGET-DEPT-99');
        $filterEventResponse->assertDontSee('TARGET-USER-88');

        // Filter by keyword q
        $filterQueryResponse = $this->actingAs($this->admin)->get(route('admin.audits.index', [
            'q' => 'TARGET-USER-88',
        ]));
        $filterQueryResponse->assertOk();
        $filterQueryResponse->assertSee('TARGET-USER-88');
        $filterQueryResponse->assertDontSee('TARGET-DEPT-99');
    }

    public function test_audit_records_are_strictly_read_only_and_immutable(): void
    {
        $audit = AuditLog::create([
            'event_type' => 'department.created',
            'actor_type' => 'USER',
            'actor_user_id' => $this->admin->id,
            'target_type' => 'departments',
            'target_id' => 1,
            'target_public_id' => 'TARGET-01',
            'request_id' => 'req-3',
            'ip_hash' => hash('sha256', '127.0.0.1'),
            'occurred_at' => now(),
        ]);

        // Attempt POST to audit collection
        $postResponse = $this->actingAs($this->admin)->post('/admin/audits', [
            'event_type' => 'malicious.event',
        ]);
        $this->assertContains($postResponse->status(), [404, 405]);

        // Attempt PUT to audit record
        $putResponse = $this->actingAs($this->admin)->put("/admin/audits/{$audit->id}", [
            'event_type' => 'tampered.event',
        ]);
        $this->assertContains($putResponse->status(), [404, 405]);

        // Attempt DELETE to audit record
        $deleteResponse = $this->actingAs($this->admin)->delete("/admin/audits/{$audit->id}");
        $this->assertContains($deleteResponse->status(), [404, 405]);

        $this->assertDatabaseHas('audit_logs', ['id' => $audit->id]);
    }
}
