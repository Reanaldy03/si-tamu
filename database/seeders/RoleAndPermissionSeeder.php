<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        $roles = [
            [
                'slug' => 'ADMIN',
                'name' => 'Administrator',
                'description' => 'Full administrative access subject to permission policy.',
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug' => 'STAFF',
                'name' => 'Staff',
                'description' => 'Operational front-office access.',
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['slug' => $role['slug']],
                $role
            );
        }

        $permissions = [
            [
                'slug' => 'visits.view',
                'name' => 'View Visits',
                'description' => 'View visit list and detail.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug' => 'visits.update_status',
                'name' => 'Update Visit Status',
                'description' => 'Perform allowed visit status transitions.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug' => 'visits.correct',
                'name' => 'Correct Visit Data',
                'description' => 'Correct allowlisted visit data with audit trail.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug' => 'reports.view',
                'name' => 'View Reports',
                'description' => 'View report preview and aggregates.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug' => 'reports.export',
                'name' => 'Export Reports',
                'description' => 'Generate and download report exports.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug' => 'departments.manage',
                'name' => 'Manage Departments',
                'description' => 'Create, update, reorder, and deactivate departments.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug' => 'users.manage',
                'name' => 'Manage Users',
                'description' => 'Create, activate/deactivate users and manage roles.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug' => 'audit.view',
                'name' => 'View Audit Log',
                'description' => 'View authorized audit records.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug' => 'settings.manage',
                'name' => 'Manage Settings',
                'description' => 'Change organization and registration settings.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['slug' => $permission['slug']],
                $permission
            );
        }

        // Fetch IDs
        $adminRoleId = DB::table('roles')->where('slug', 'ADMIN')->value('id');
        $staffRoleId = DB::table('roles')->where('slug', 'STAFF')->value('id');

        $allPermissionIds = DB::table('permissions')->pluck('id')->all();
        $staffPermissionIds = DB::table('permissions')
            ->whereIn('slug', ['visits.view', 'visits.update_status', 'reports.view'])
            ->pluck('id')
            ->all();

        // Assign all permissions to ADMIN
        foreach ($allPermissionIds as $permissionId) {
            DB::table('role_permissions')->updateOrInsert(
                ['role_id' => $adminRoleId, 'permission_id' => $permissionId],
                ['role_id' => $adminRoleId, 'permission_id' => $permissionId]
            );
        }

        // Assign default operational permissions to STAFF
        foreach ($staffPermissionIds as $permissionId) {
            DB::table('role_permissions')->updateOrInsert(
                ['role_id' => $staffRoleId, 'permission_id' => $permissionId],
                ['role_id' => $staffRoleId, 'permission_id' => $permissionId]
            );
        }
    }
}
