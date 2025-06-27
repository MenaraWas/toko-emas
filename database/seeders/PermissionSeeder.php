<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Permission::insert([
            ['name'=>'manage_branch','description'=>'Membuat, mengedit, menghapus cabang'],
            ['name'=>'view_all_branches','description'=>'Melihat semua cabang'],
            ['name'=>'switch_branch','description'=>'Berpindah antar cabang'],
            ['name'=>'manage_users','description'=>'Mengelola user'],
            ['name'=>'view_stock','description'=>'Melihat stok'],
            ['name'=>'manage_stock','description'=>'Mengelola stok'],
            ['name'=>'pos_transaction','description'=>'Melakukan transaksi POS'],
            ['name'=>'view_reports','description'=>'Melihat laporan'],
            ['name'=>'delete_critical_data','description'=>'Menghapus data kritikal'],
            ['name'=>'manage_products','description'=>'Manajemen master produk emas'],
        ]);
    }
}
