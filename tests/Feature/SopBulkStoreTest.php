<?php

use App\Models\Subjek;
use App\Models\Timkerja;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Schema\Blueprint;

function createUserWithRole(string $role, ?int $timkerjaId = null): User
{
    return User::create([
        'nama' => ucfirst(strtolower($role)) . ' Test',
        'email' => strtolower($role) . '@example.com',
        'username' => strtolower($role) . '_test',
        'password' => Hash::make('password'),
        'role' => $role,
        'id_timkerja' => $timkerjaId,
        'created_date' => now(),
        'modified_date' => now(),
    ]);
}

function ensureSopSchemaMatchesRuntimeExpectations(): void
{
    if (!Schema::hasColumn('tb_sop', 'status')) {
        Schema::table('tb_sop', function (Blueprint $table) {
            $table->string('status', 50)->nullable();
        });

        DB::table('tb_sop')->update(['status' => 'aktif']);
    }
}

it('allows admin to store multiple sop entries at once', function () {
    Storage::fake('public');
    ensureSopSchemaMatchesRuntimeExpectations();

    $timkerja = Timkerja::create([
        'nama_timkerja' => 'Tim Integrasi',
        'status' => 'aktif',
        'created_date' => now(),
        'modified_date' => now(),
    ]);

    $subjekA = Subjek::create([
        'id_timkerja' => $timkerja->id_timkerja,
        'nama_subjek' => 'Pelayanan Data',
        'status' => 'aktif',
        'created_date' => now(),
        'modified_date' => now(),
    ]);

    $subjekB = Subjek::create([
        'id_timkerja' => $timkerja->id_timkerja,
        'nama_subjek' => 'Pengelolaan Arsip',
        'status' => 'aktif',
        'created_date' => now(),
        'modified_date' => now(),
    ]);

    $admin = createUserWithRole('Admin');

    $response = $this
        ->actingAs($admin)
        ->post(route('admin.sop.bulkStore'), [
            'entries' => [
                [
                    'nama_sop' => 'SOP Pelayanan Data',
                    'nomor_sop' => 'BPS/001/2026',
                    'id_subjek' => $subjekA->id_subjek,
                    'tahun' => 2026,
                    'link_sop' => UploadedFile::fake()->create('pelayanan-data.pdf', 100, 'application/pdf'),
                ],
                [
                    'nama_sop' => 'SOP Arsip Digital',
                    'nomor_sop' => 'BPS/002/2026',
                    'id_subjek' => $subjekB->id_subjek,
                    'tahun' => 2026,
                    'link_sop' => UploadedFile::fake()->create('arsip-digital.pdf', 100, 'application/pdf'),
                ],
            ],
        ]);

    $response
        ->assertRedirect(route('admin.sop.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('tb_sop', [
        'nama_sop' => 'SOP Pelayanan Data',
        'nomor_sop' => 'BPS/001/2026',
        'id_subjek' => $subjekA->id_subjek,
        'created_by' => $admin->id_user,
    ]);

    $this->assertDatabaseHas('tb_sop', [
        'nama_sop' => 'SOP Arsip Digital',
        'nomor_sop' => 'BPS/002/2026',
        'id_subjek' => $subjekB->id_subjek,
        'created_by' => $admin->id_user,
    ]);

});

it('blocks non admin users from bulk storing sop entries', function () {
    ensureSopSchemaMatchesRuntimeExpectations();

    $timkerja = Timkerja::create([
        'nama_timkerja' => 'Tim Operasional',
        'status' => 'aktif',
        'created_date' => now(),
        'modified_date' => now(),
    ]);

    $subjek = Subjek::create([
        'id_timkerja' => $timkerja->id_timkerja,
        'nama_subjek' => 'Layanan Statistik',
        'status' => 'aktif',
        'created_date' => now(),
        'modified_date' => now(),
    ]);

    $operator = createUserWithRole('Operator', $timkerja->id_timkerja);

    $this
        ->actingAs($operator)
        ->post(route('admin.sop.bulkStore'), [
            'entries' => [
                [
                    'nama_sop' => 'SOP Operator',
                    'nomor_sop' => 'OPS/001/2026',
                    'id_subjek' => $subjek->id_subjek,
                    'tahun' => 2026,
                    'link_sop' => UploadedFile::fake()->create('operator.pdf', 100, 'application/pdf'),
                ],
                [
                    'nama_sop' => 'SOP Operator 2',
                    'nomor_sop' => 'OPS/002/2026',
                    'id_subjek' => $subjek->id_subjek,
                    'tahun' => 2026,
                    'link_sop' => UploadedFile::fake()->create('operator-2.pdf', 100, 'application/pdf'),
                ],
            ],
        ])
        ->assertForbidden();
});
