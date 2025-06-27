<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        User::create([
            'name' => 'Owner',
            'email' => 'owner@tokomas.com',
            'password' => Hash::make('owner123'), // Ganti dengan password lain kalau mau
            'role_id' => 1, // Owner
            'branch_id' => 1, // Toko Emas Pusat
            'status' => 'active',
        ]);
    }
}
