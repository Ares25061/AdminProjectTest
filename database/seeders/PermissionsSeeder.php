<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\RolePermissions;
use App\UserPermissions;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (RolePermissions::cases() as $permission) {
            Permission::firstOrCreate(['name' => $permission], ['name' => $permission]);
        }
        foreach (UserPermissions::cases() as $permission) {
            Permission::firstOrCreate(['name' => $permission], ['name' => $permission]);
        }
    }
}
