<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'manage-products',
            'manage-stock',
            'manage-stock-adjustments',
            'manage-warehouses',
            'manage-suppliers',
            'manage-departments',
            'manage-calculators',
            'view-reports',
            'manage-users',
            'manage-orders',
            'manage-quotes',
            'manage-pos',
            'apply-pos-discount',
            'manage-license',
            'manage-settings',
            'manage-roles',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $admin->syncPermissions(Permission::where('guard_name', 'web')->get());

        $manager = Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'web']);
        $manager->syncPermissions([
            'manage-products',
            'manage-stock',
            'manage-stock-adjustments',
            'manage-warehouses',
            'manage-suppliers',
            'manage-departments',
            'manage-calculators',
            'view-reports',
            'manage-orders',
            'manage-quotes',
            'manage-pos',
            'apply-pos-discount',
        ]);

        $warehouseStaff = Role::firstOrCreate(['name' => 'Warehouse Staff', 'guard_name' => 'web']);
        $warehouseStaff->syncPermissions([
            'manage-stock',
            'view-reports',
            'manage-orders',
            'manage-pos',
        ]);

        $viewer = Role::firstOrCreate(['name' => 'Viewer', 'guard_name' => 'web']);
        $viewer->syncPermissions([
            'view-reports',
        ]);

        $adminUser = User::firstOrCreate(
            ['email' => 'scanthy4@gmail.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $adminUser->syncRoles([$admin]);
    }
}
