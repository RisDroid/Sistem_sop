<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tb_timkerja')) {
            Schema::create('tb_timkerja', function (Blueprint $table) {
                $table->integer('id_timkerja')->autoIncrement();
                $table->string('nama_timkerja', 150)->unique();
                $table->text('deskripsi')->nullable();
                $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
                $table->integer('created_by')->nullable();
                $table->dateTime('created_date')->nullable();
                $table->integer('modified_by')->nullable();
                $table->dateTime('modified_date')->nullable();
            });
        }

        if (!Schema::hasTable('tb_monitoring')) {
            Schema::create('tb_monitoring', function (Blueprint $table) {
                $table->integer('id_monitoring')->autoIncrement();
                $table->integer('id_sop');
                $table->integer('id_user')->nullable();
                $table->dateTime('tanggal');
                $table->string('kriteria_penilaian', 255);
                $table->text('hasil_monitoring');
                $table->text('catatan')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_monitoring');
        Schema::dropIfExists('tb_timkerja');
    }
};
