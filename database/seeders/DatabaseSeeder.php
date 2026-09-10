<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     * Hanya akun admin dan akun pembeli, tidak ada seeder produk tiket.
     */
    public function run(): void
    {
        // 1. Akun Admin
        User::firstOrCreate(
            ['email' => 'admin@kebunraya.id'],
            [
                'name' => 'Administrator Kebun Raya',
                'phone' => '081234567890',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // 2. Akun Pembeli (User)
        User::firstOrCreate(
            ['email' => 'pembeli@kebunraya.id'],
            [
                'name' => 'Pengunjung Kebun Raya',
                'phone' => '089876543210',
                'password' => Hash::make('password'),
                'role' => 'user',
            ]
        );
    }
}
