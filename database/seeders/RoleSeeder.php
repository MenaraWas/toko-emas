<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Role::insert([
            ['name' => 'Owner', 'description' => 'Pemilik utama sistem'],
            ['name' => 'SuperAdmin', 'description' => 'Manajemen Pusat'],
            ['name' => 'Admin Branch', 'description' => 'Admin Cabang'],
            ['name' => 'Kasir', 'description' => 'Operator POS'],
            ['name' => 'Admin Gudang', 'description' => 'Manajemen Stok Gudang'],
        ]);
    }
}
