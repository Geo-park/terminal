<?php

namespace Database\Seeders;

use App\Models\Computer;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Akun Mahasiswa (Untuk Login Subagja)
        User::updateOrCreate(
            ['nim' => '22081010045'],
            [
                'name' => 'M. Subagja',
                'email' => 'subagja@uinbanten.ac.id',
                'jurusan' => 'Teknik Informatika',
                'password' => Hash::make('password'),
                'role' => 'mahasiswa',
                'is_active' => true,
            ]
        );

        // 2. Akun Mahasiswa Lain (Untuk Contoh Booking Bersama)
        $otherStudent = User::updateOrCreate(
            ['nim' => '22081010099'],
            [
                'name' => 'Siti Rahmawati',
                'email' => 'siti@uinbanten.ac.id',
                'jurusan' => 'Teknik Informatika',
                'password' => Hash::make('password'),
                'role' => 'mahasiswa',
                'is_active' => true,
                'is_unlimited_session' => false,
            ]
        );

        // 3. Akun Mahasiswa Khusus VIP (Sesi Unlimited / Tanpa Batas Kuota Harian)
        User::updateOrCreate(
            ['nim' => '22081010888'],
            [
                'name' => 'Budi Santoso (VIP Unlimited)',
                'email' => 'unlimited@uinbanten.ac.id',
                'jurusan' => 'Teknik Informatika',
                'password' => Hash::make('password'),
                'role' => 'mahasiswa',
                'is_active' => true,
                'is_unlimited_session' => true,
            ]
        );

        // 4. Akun Kepala Lab (General)
        User::updateOrCreate(
            ['role' => 'kepala_lab'],
            [
                'nim' => 'kepala_lab',
                'name' => 'Kepala Laboratorium',
                'email' => 'kepalalab@uinbanten.ac.id',
                'jurusan' => 'Laboratorium Sains Data & AI',
                'password' => Hash::make('password'),
                'role' => 'kepala_lab',
                'is_active' => true,
            ]
        );

        // 3. 4 Komputer Laboratorium (BR-001)
        $computers = [
            [
                'computer_code' => 'PC-01',
                'computer_name' => 'Workstation AI & Deep Learning 01',
                'specs' => 'Intel Core i7-13700K • 32GB RAM • RTX 4070 12GB • NVMe 1TB',
                'location' => 'Lab Data Sains - Meja 1',
                'status' => 'ONLINE_LOCKED',
                'is_active' => true,
            ],
            [
                'computer_code' => 'PC-02',
                'computer_name' => 'Workstation Data Analytics 02',
                'specs' => 'Intel Core i7-13700 • 32GB RAM • RTX 3060 12GB • NVMe 1TB',
                'location' => 'Lab Data Sains - Meja 2',
                'status' => 'ONLINE_LOCKED',
                'is_active' => true,
            ],
            [
                'computer_code' => 'PC-03',
                'computer_name' => 'Workstation Big Data & ML 03',
                'specs' => 'AMD Ryzen 7 7700X • 32GB RAM • RTX 3060 Ti 8GB • NVMe 1TB',
                'location' => 'Lab Data Sains - Meja 3',
                'status' => 'ONLINE_LOCKED',
                'is_active' => true,
            ],
            [
                'computer_code' => 'PC-04',
                'computer_name' => 'Workstation Modeling & Stats 04',
                'specs' => 'Intel Core i5-13500 • 16GB RAM • RTX 3050 8GB • NVMe 512GB',
                'location' => 'Lab Data Sains - Meja 4',
                'status' => 'ONLINE_LOCKED',
                'is_active' => true,
            ],
        ];

        foreach ($computers as $computer) {
            Computer::updateOrCreate(
                ['computer_code' => $computer['computer_code']],
                $computer
            );
        }
    }
}
