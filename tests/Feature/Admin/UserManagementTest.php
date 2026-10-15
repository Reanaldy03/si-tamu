<?php

namespace Tests\Feature\Admin;

use App\Domain\Access\Enums\RoleCode;
use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\OrganizationSettingsSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserManagementTest extends TestCase
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
            'name' => 'Admin Sistem',
            'email' => 'admin@sitamu.local',
            'password' => bcrypt('AdminPass123!'),
            'is_active' => true,
        ]);
        $this->admin->roles()->attach($adminRole->id, ['assigned_at' => now()]);

        $this->staff = User::create([
            'public_id' => '01HXYZ00000000000000000002',
            'name' => 'Petugas Front Office',
            'email' => 'staff@sitamu.local',
            'password' => bcrypt('StaffPass123!'),
            'is_active' => true,
        ]);
        $this->staff->roles()->attach($staffRole->id, ['assigned_at' => now()]);
    }

    public function test_admin_can_view_users_list(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));
        $response->assertOk();
        $response->assertSee('Admin Sistem');
        $response->assertSee('Petugas Front Office');
    }

    public function test_staff_cannot_access_user_management(): void
    {
        $response = $this->actingAs($this->staff)->get(route('admin.users.index'));
        $response->assertForbidden();

        $createResponse = $this->actingAs($this->staff)->get(route('admin.users.create'));
        $createResponse->assertForbidden();
    }

    public function test_admin_can_create_new_user(): void
    {
        $payload = [
            'name' => 'Petugas Baru',
            'email' => 'baru@sitamu.local',
            'password' => 'Password123!',
            'role' => RoleCode::STAFF->value,
            'is_active' => 1,
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), $payload);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'name' => 'Petugas Baru',
            'email' => 'baru@sitamu.local',
        ]);

        $newUser = User::where('email', 'baru@sitamu.local')->first();
        $this->assertTrue($newUser->isStaff());

        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'user.created',
            'target_id' => $newUser->id,
        ]);
    }

    public function test_admin_cannot_deactivate_own_account(): void
    {
        $response = $this->actingAs($this->admin)->from(route('admin.users.edit', $this->admin))
            ->put(route('admin.users.update', $this->admin), [
                'name' => 'Admin Sistem',
                'email' => 'admin@sitamu.local',
                'role' => RoleCode::ADMIN->value,
                'is_active' => 0, // Attempt deactivating self
            ]);

        $response->assertRedirect(route('admin.users.edit', $this->admin));
        $response->assertSessionHasErrors(['is_active']);
        $this->assertTrue($this->admin->fresh()->is_active);

        // Also test toggle endpoint
        $toggleResponse = $this->actingAs($this->admin)->patch(route('admin.users.toggle-status', $this->admin));
        $toggleResponse->assertSessionHas('error');
        $this->assertTrue($this->admin->fresh()->is_active);
    }

    public function test_admin_cannot_demote_own_role(): void
    {
        $response = $this->actingAs($this->admin)->from(route('admin.users.edit', $this->admin))
            ->put(route('admin.users.update', $this->admin), [
                'name' => 'Admin Sistem',
                'email' => 'admin@sitamu.local',
                'role' => RoleCode::STAFF->value, // Attempt demoting self
                'is_active' => 1,
            ]);

        $response->assertRedirect(route('admin.users.edit', $this->admin));
        $response->assertSessionHasErrors(['role']);
        $this->assertTrue($this->admin->fresh()->isAdmin());
    }

    public function test_user_update_audit_records_real_role(): void
    {
        $secondAdmin = User::create([
            'public_id' => '01HXYZ00000000000000000009',
            'name' => 'Admin Kedua',
            'email' => 'admin2@sitamu.local',
            'password' => bcrypt('AdminPass456!'),
            'is_active' => true,
        ]);
        $secondAdmin->roles()->attach(Role::where('slug', RoleCode::ADMIN->value)->first()->id, ['assigned_at' => now()]);

        $this->actingAs($this->admin)
            ->put(route('admin.users.update', $secondAdmin), [
                'name' => 'Admin Kedua',
                'email' => 'admin2@sitamu.local',
                'role' => RoleCode::ADMIN->value,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.users.index'));

        $log = AuditLog::where('event_type', 'user.updated')->latest('id')->first();

        $this->assertNotNull($log);
        $this->assertSame('ADMIN', $log->before_state['role']);
        $this->assertSame('ADMIN', $log->after_state['role']);
    }

    public function test_password_reset_invalidates_remember_token_and_sessions(): void
    {
        $this->staff->setRememberToken('old-remember-token-value');
        $this->staff->save();

        DB::table('sessions')->insert([
            'id' => 'staff-session-0001',
            'user_id' => $this->staff->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'phpunit',
            'payload' => 'payload',
            'last_activity' => time(),
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.users.update', $this->staff), [
                'name' => 'Petugas Front Office',
                'email' => 'staff@sitamu.local',
                'role' => RoleCode::STAFF->value,
                'is_active' => 1,
                'password' => 'NewPassword456!',
            ]);

        $response->assertRedirect(route('admin.users.index'));

        $freshStaff = $this->staff->fresh();
        $this->assertNotNull($freshStaff->password_changed_at);
        $this->assertNotSame('old-remember-token-value', $freshStaff->remember_token);
        $this->assertDatabaseMissing('sessions', ['id' => 'staff-session-0001']);
    }

    public function test_admin_can_toggle_active_status_of_other_user(): void
    {
        $this->assertTrue($this->staff->is_active);

        // Deactivate staff
        $this->actingAs($this->admin)->patch(route('admin.users.toggle-status', $this->staff));
        $this->assertFalse($this->staff->fresh()->is_active);

        // Reactivate staff
        $this->actingAs($this->admin)->patch(route('admin.users.toggle-status', $this->staff));
        $this->assertTrue($this->staff->fresh()->is_active);
    }
}
