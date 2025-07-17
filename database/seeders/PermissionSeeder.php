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
            'product.view',
            'product.create',
            'product.update',
            'product.delete',
            'product.approve',
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
