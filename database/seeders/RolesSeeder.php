<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Roles;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (Roles::cases() as $role) {
        Role::firstOrCreate(['name' => $role], ['name' => $role]);
        }
    }
}
