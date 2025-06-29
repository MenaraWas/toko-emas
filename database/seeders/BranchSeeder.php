<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Branch::create([
            'name' => 'Toko Emas Pusat',
            'address' => 'Jl. Contoh No.1, Jakarta',
            'phone' => '021-12345678',
        ]);
    }
}
