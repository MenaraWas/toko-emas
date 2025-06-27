<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //

        //Ambil semua permission
        $allPermissions = DB::table('permissions')->pluck('id');

        //Owner Role :: Get All Permission
        foreach($allPermissions as $pid)
        {
            DB::table('role_permission')->insert([
                'role_id' => 1,
                'permission_id' => $pid,
            ]);
        }

        //Super Admin Role : all Permission except Critical and branching
        foreach($allPermissions as $pid)
        {
            if(in_array($pid, [1,9])) continue;
            DB::table('role_permission')->insert([
                'role_id' => 2,
                'permission_id' => $pid,
            ]);
        }

        //Admin Branch
        $adminBranchPermission = [5,6,7,8];
        foreach($adminBranchPermission as $pid){
            DB::table('role_permission')->insert([
                'role_id' => 3,
                'permission_id' => $pid,
            ]);
        }

        //Kasir Role
        DB::table('role_permission')->insert([
            'role_id' => 4,
            'permission_id' => 7,
        ]);

        //Admin Gudang
        $adminGudangPermissions = [5,6,8];
        foreach ($adminGudangPermissions as $pid) {
            DB::table('role_permission')->insert([
                'role_id' => 5,
                'permission_id' => $pid,
            ]);
        }
    }
}
