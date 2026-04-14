<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tb_sop', function (Blueprint $table) {
            /**
             * Mengubah tipe data revisi_ke dari Integer menjadi String (Varchar).
             * Ini dilakukan agar kolom bisa menampung tanda '-' untuk data perdana
             * dan tetap bisa menyimpan angka (1, 2, 3...) untuk data revisi.
             */
            $table->string('revisi_ke', 50)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_sop', function (Blueprint $table) {
            /**
             * Kembalikan ke tipe data Integer jika migration di-rollback.
             * Catatan: Jika ada data '-' di database, rollback mungkin gagal
             * kecuali data tersebut dihapus atau diubah menjadi angka terlebih dahulu.
             */
            $table->integer('revisi_ke')->change();
        });
    }
};
