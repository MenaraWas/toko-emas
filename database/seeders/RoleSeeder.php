<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Role::create(['name' => 'owner']);
        Role::create(['name' => 'admin_pusat']);
        Role::create(['name' => 'admin_cabang']);
        Role::create(['name' => 'kasir']);
        Role::create(['name' => 'gudang']);
        Role::create(['name' => 'finance']);
    }
}
