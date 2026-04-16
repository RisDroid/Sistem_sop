<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tb_sop', function (Blueprint $table) {
            $table->integer('id_sop')->autoIncrement();
            $table->text('nama_sop');
            $table->string('nomor_sop', 50);
            $table->datetime('tahun'); // Tanggal revisi terakhir
            $table->string('revisi_ke', 50)->default('-');
            $table->integer('id_subjek');
            $table->integer('id_unit')->nullable();
            $table->boolean('status_active')->default(1);
            $table->string('link_sop', 255)->nullable();
            $table->text('keterangan')->nullable();
            $table->datetime('created_date')->nullable();
            $table->datetime('modified_date')->useCurrent();
            $table->integer('created_by')->nullable();
            $table->integer('modify_by')->nullable();
        });
    }

    public function down(): void {
        Schema::dropIfExists('tb_sop');
    }
};
