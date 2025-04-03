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
        // Veritabanını sıfırla (opsiyonel)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('model_has_roles')->truncate();
        DB::table('roles')->truncate();
        DB::table('users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Admin rolünü oluştur
        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'api'
        ]);

        // Temel izinleri oluştur
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
                'guard_name' => 'api'
            ]);
        }

        // Tüm permission'ları ata
        $adminRole->syncPermissions(Permission::all());

        // Admin kullanıcısını oluştur
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@hr.com',
            'password' => Hash::make('password123'),
            'is_active' => true
        ])->assignRole($adminRole);
    }
}
