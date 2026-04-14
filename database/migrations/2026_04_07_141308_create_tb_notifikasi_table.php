<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan Migrasi.
     */
    public function up(): void
    {
        Schema::create('tb_notifikasi', function (Blueprint $table) {
            $table->id(); // ID Notifikasi

            // Relasi ke SOP yang bermasalah
            $table->integer('id_sop')->nullable();

            // Isi Pesan Notifikasi
            $table->string('pesan', 255);

            // Status: 0 = Belum Dibaca, 1 = Sudah Dibaca
            $table->boolean('status_baca')->default(0);

            // Waktu Notifikasi Muncul
            $table->timestamps(); // Ini akan membuat created_at dan updated_at

            // Set Foreign Key agar sinkron dengan tabel SOP
            $table->foreign('id_sop')
                  ->references('id_sop')
                  ->on('tb_sop')
                  ->onDelete('cascade');
        });
    }

    /**
     * Batalkan Migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_notifikasi');
    }
};
