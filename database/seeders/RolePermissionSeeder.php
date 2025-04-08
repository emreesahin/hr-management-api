<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Roller
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'api']);
        $hrRole = Role::firstOrCreate(['name' => 'hr', 'guard_name' => 'api']);
        $employeeRole = Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'api']);

        // İzinler
        $permissions = [
            'view users',
            'create users',
            'edit users',
            'delete users',
            'view roles',
            'assign roles',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'api']);
        }

        // Admin rolüne tüm izinleri ver
        $adminRole->syncPermissions(Permission::all());

        // HR rolüne bazı izinler ver
        $hrRole->syncPermissions([
            'view users',
            'create users',
            'edit users',
        ]);
    }
}
