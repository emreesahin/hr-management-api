<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
{
    // Veritabanını sıfırla
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    DB::table('model_has_roles')->truncate();
    DB::table('roles')->truncate();
    DB::table('permissions')->truncate(); // <-- önemli
    DB::table('users')->truncate();
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');

    // Admin rolünü oluştur (guard_name 'api' ile aynı olmalı)
    $adminRole = Role::firstOrCreate([
        'name' => 'admin',
        'guard_name' => 'admin'
    ]);

    // İzinleri oluştur (aynı guard_name ile)
    $permissions = [
        'view departments',
        'create departments',
        'edit departments',
        'delete departments',
        'assign employees'
    ];

    foreach ($permissions as $permission) {
        Permission::firstOrCreate([
            'name' => $permission,
            'guard_name' => 'admin'
        ]);
    }

    // Rol ile izinleri eşleştir
    $adminRole->syncPermissions($permissions);

    // Admin kullanıcısını oluştur
    $adminUser = User::create([
        'name' => 'Admin User',
        'email' => 'admin@hr.com',
        'password' => Hash::make('password123'),
        'is_active' => true
    ]);

    // Admin rolünü ata
    $admin->assignRole(Role::findByName('admin', 'admin'));

}

}
