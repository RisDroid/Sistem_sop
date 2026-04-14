<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tb_unit', function (Blueprint $table) {
            // Kita pastikan kolom id_subjek tipenya cocok dengan tb_subjek
            // Lalu kita ikat sebagai Foreign Key
            $table->foreign('id_subjek')
                  ->references('id_subjek')
                  ->on('tb_subjek')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('tb_unit', function (Blueprint $table) {
            $table->dropForeign(['id_subjek']);
        });
    }
};
