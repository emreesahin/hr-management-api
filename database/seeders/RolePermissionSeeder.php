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
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'admin']);
        $hrRole = Role::firstOrCreate(['name' => 'hr', 'guard_name' => 'hr']);
        $employeeRole = Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'employee']);
        $candidateRole = Role::firstOrCreate(['name' => 'candidate', 'guard_name' => 'candidate']);

        // ADMIN izinleri
        $adminPermissions = [
            'view users',
            'create users',
            'edit users',
            'delete users',
            'view roles',
            'assign roles',
        ];

        foreach ($adminPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'admin']);
        }
        $adminRole->syncPermissions($adminPermissions);

        // HR izinleri
        $hrPermissions = [
            'view users',
            'create users',
            'edit users',
        ];

        foreach ($hrPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'hr']);
        }
        $hrRole->syncPermissions($hrPermissions);

        // Employee ve Candidate izinleri gerekiyorsa benzer şekilde aşağıya eklenebilir
    }
}
