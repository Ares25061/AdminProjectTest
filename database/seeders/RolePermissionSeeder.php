<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Roles;
use App\UserPermissions;
use App\RolePermissions;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userPermissions = UserPermissions::values();
        $rolePermissions = RolePermissions::values();
        $permissions = [];
        foreach ($userPermissions as $userPermission) {
            $permissions[] = Permission::create(['name' => $userPermission]);
        }
        foreach ($rolePermissions as $rolePermission) {
            $permissions[] = Permission::create(['name' => $rolePermission]);
        }
        $adminRole = Role::create(['name' => Roles::ADMIN->value]);
        $adminRole->permissions()->sync($permissions);
        $moderRole = Role::create(['name' => Roles::MODER->value]);
        $moderPermissions = Permission::whereIn('name',UserPermissions::moderPermissions())->get();
        $moderRole->permissions()->sync($moderPermissions);
        dump($moderRole->load('permissions'));
        Role::create(['name' => Roles::USER->value]);

    }
}
