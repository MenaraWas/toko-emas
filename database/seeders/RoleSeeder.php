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
        // Role untuk pusat
        Role::firstOrCreate(['name' => 'pusat.owner']);
        Role::firstOrCreate(['name' => 'pusat.admin_pusat']);
        Role::firstOrCreate(['name' => 'pusat.finance']);

        // Role untuk cabang
        Role::firstOrCreate(['name' => 'cabang.admin_cabang']);
        Role::firstOrCreate(['name' => 'cabang.kasir']);
    }
}
