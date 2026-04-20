<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tb_evaluasi')) {
            return;
        }

        Schema::create('tb_evaluasi', function (Blueprint $table) {
            $table->integer('id_evaluasi')->autoIncrement();
            $table->integer('id_sop');
            $table->integer('id_user')->nullable();
            $table->dateTime('tanggal');
            $table->text('kriteria_evaluasi');
            $table->text('hasil_evaluasi');
            $table->text('catatan')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_evaluasi');
    }
};
