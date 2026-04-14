<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
   public function run(): void
{
    // 1. Buat Subjek Contoh
    $subjek = \App\Models\Subjek::create([
        'nama_subjek' => 'Teknologi Informasi',
        'created_date' => now()
    ]);

    // 2. Buat User Admin (Password: admin123)
    \App\Models\User::create([
        'nama' => 'Muhamad Kurtubi',
        'username' => 'admin',
        'nip' => '12345678',
        'password' => bcrypt('admin123'),
        'role' => 'Admin',
        'id_subjek' => $subjek->id_subjek,
        'created_date' => now()
    ]);

    // 3. Buat SOP Contoh (Tahun 2024 - Kritis)
    \App\Models\Sop::create([
        'nama_sop' => 'SOP Backup Data Server',
        'nomor_sop' => 'SOP/TI/001',
        'tahun' => '2024-01-01 00:00:00',
        'revisi_ke' => 1,
        'id_subjek' => $subjek->id_subjek,
        'status_active' => 1,
        'created_date' => now()
    ]);
}
    }
