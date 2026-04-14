<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Subjek;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Buat Subjek Dulu
        $subjek = Subjek::create([
            'nama_subjek' => 'Teknologi Informasi',
            'created_date' => now()
        ]);

        // Akun ADMIN
        User::create([
            'nama' => 'Muhamad Kurtubi',
            'username' => 'kurtubi',
            'nip' => '19900101',
            'password' => Hash::make('password123'),
            'role' => 'Admin', // Sesuai Enum
            'id_subjek' => $subjek->id_subjek,
            'created_date' => now()
        ]);

        // Akun OPERATOR
        User::create([
            'nama' => 'Fajar Operator',
            'username' => 'fajar',
            'nip' => '19900102',
            'password' => Hash::make('password123'),
            'role' => 'Operator', // Sesuai Enum
            'id_subjek' => $subjek->id_subjek,
            'created_date' => now()
        ]);

        // Akun VIEWER (Perhatikan: Harus 'Viewer' sesuai migrasi)
        User::create([
            'nama' => 'User Viewer',
            'username' => 'viewer',
            'nip' => '19900103',
            'password' => Hash::make('password123'),
            'role' => 'Viewer', // TADI SALAH DI SINI (Sebelumnya 'View')
            'id_subjek' => $subjek->id_subjek,
            'created_date' => now()
        ]);
    }
}
