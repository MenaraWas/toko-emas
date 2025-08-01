<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;


class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $roleOwner = Role::where('name', 'owner')->first();

        if ($roleOwner) {
            $permissions = Permission::pluck('name');
            $roleOwner->syncPermissions($permissions);
        }
    }
}
