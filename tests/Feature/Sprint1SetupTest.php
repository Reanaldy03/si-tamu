<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class Sprint1SetupTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_expected_tables_are_migrated(): void
    {
        $expectedTables = [
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
            'password_reset_tokens',
            'sessions',
            'cache',
            'cache_locks',
            'jobs',
            'job_batches',
            'failed_jobs',
        ];

        foreach ($expectedTables as $table) {
            $this->assertTrue(Schema::hasTable($table), "Tabel {$table} harus ada di basis data.");
        }
    }

    public function test_database_seeder_populates_roles_permissions_and_settings(): void
    {
        $this->seed();

        // Roles check
        $this->assertDatabaseHas('roles', ['slug' => 'ADMIN', 'name' => 'Administrator', 'is_system' => 1]);
        $this->assertDatabaseHas('roles', ['slug' => 'STAFF', 'name' => 'Staff', 'is_system' => 1]);

        // Permissions count
        $this->assertSame(9, DB::table('permissions')->count());

        // Baseline permissions check
        $expectedPermissions = [
            'visits.view',
            'visits.update_status',
            'visits.correct',
            'reports.view',
            'reports.export',
            'departments.manage',
            'users.manage',
            'audit.view',
            'settings.manage',
        ];

        foreach ($expectedPermissions as $perm) {
            $this->assertDatabaseHas('permissions', ['slug' => $perm]);
        }

        // ADMIN mapping (all 9)
        $adminRole = DB::table('roles')->where('slug', 'ADMIN')->first();
        $adminPermsCount = DB::table('role_permissions')->where('role_id', $adminRole->id)->count();
        $this->assertSame(9, $adminPermsCount);

        // STAFF mapping (3 default)
        $staffRole = DB::table('roles')->where('slug', 'STAFF')->first();
        $staffPerms = DB::table('role_permissions')
            ->join('permissions', 'role_permissions.permission_id', '=', 'permissions.id')
            ->where('role_permissions.role_id', $staffRole->id)
            ->pluck('permissions.slug')
            ->all();

        $this->assertEqualsCanonicalizing([
            'visits.view',
            'visits.update_status',
            'reports.view',
        ], $staffPerms);

        // Organization Settings singleton
        $this->assertDatabaseHas('organization_settings', [
            'id' => 1,
            'timezone' => 'Asia/Jakarta',
        ]);
    }

    public function test_admin_create_command_creates_user_with_admin_role_and_audit(): void
    {
        $this->seed();

        $this->artisan('sitamu:admin:create', [
            '--name' => 'Super Administrator',
            '--email' => 'admin@sitamu.local',
            '--password' => 'Password123!',
        ])
            ->assertSuccessful();

        $user = DB::table('users')->where('email', 'admin@sitamu.local')->first();
        $this->assertNotNull($user);
        $this->assertSame('Super Administrator', $user->name);
        $this->assertSame(26, strlen($user->public_id));
        $this->assertTrue((bool) $user->is_active);
        $this->assertTrue(Hash::check('Password123!', $user->password));

        // Role assignment check
        $adminRole = DB::table('roles')->where('slug', 'ADMIN')->first();
        $this->assertDatabaseHas('user_roles', [
            'user_id' => $user->id,
            'role_id' => $adminRole->id,
        ]);

        // Audit log check
        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'user.bootstrap_admin',
            'actor_type' => 'SYSTEM',
            'target_type' => 'user',
            'target_id' => $user->id,
        ]);
    }

    public function test_admin_create_command_rejects_duplicate_email(): void
    {
        $this->seed();

        $this->artisan('sitamu:admin:create', [
            '--name' => 'Admin One',
            '--email' => 'duplicate@sitamu.local',
            '--password' => 'Password123!',
        ])->assertSuccessful();

        $this->artisan('sitamu:admin:create', [
            '--name' => 'Admin Two',
            '--email' => 'duplicate@sitamu.local',
            '--password' => 'Password123!',
        ])->assertFailed();
    }
}
