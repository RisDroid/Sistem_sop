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
        Schema::create('tb_log_revisi', function (Blueprint $table) {
            $table->id(); // ID Primary Key Log

            // Relasi ke tabel SOP King (Gunakan integer karena id_sop King itu integer)
            $table->integer('id_sop');

            // Data Revisi
            $table->date('tanggal_revisi');
            $table->integer('revisi_ke');
            $table->text('keterangan'); // Catatan apa saja yang diubah

            // Audit Trails (Siapa yang input)
            $table->integer('created_by')->nullable();

            // Timestamps standar Laravel (created_at & updated_at)
            $table->timestamps();

            // Set Foreign Key agar data konsisten
            // Jika SOP dihapus, log revisinya juga ikut terhapus
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
        Schema::dropIfExists('tb_log_revisi');
    }
};
