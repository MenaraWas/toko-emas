<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;


class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $permissions = [
           'user.view',
            'user.create',
            'user.update',
            'user.delete',

            'role.view',
            'role.create',
            'role.update',
            'role.delete',

            'cabang.view',
            'cabang.create',
            'cabang.update',
            'cabang.delete',
            // Tambahkan permission lain nanti
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

         $owner = Role::firstOrCreate(['name' => 'owner']);
         // Assign all permissions to Owner
         $owner->syncPermissions(Permission::pluck('name'));


    }
}
