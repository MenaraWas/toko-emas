<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleAssignableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('role_assignable')->insert([
            // Owner boleh assign semua
            ['role_id' => 1, 'assignable_role_id' => 2],
            ['role_id' => 1, 'assignable_role_id' => 3],
            ['role_id' => 1, 'assignable_role_id' => 4],

            // SuperAdmin boleh assign Admin Branch dan Kasir
            ['role_id' => 3, 'assignable_role_id' => 2],
            ['role_id' => 3, 'assignable_role_id' => 4],

            // Admin Branch boleh assign Kasir
            ['role_id' => 2, 'assignable_role_id' => 4],
        ]);
    }
}
