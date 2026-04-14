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
            $table->integer('revisi_ke');
            $table->integer('id_subjek');
            $table->boolean('status_active')->default(1);
            $table->string('link_sop', 255)->nullable();
            $table->datetime('created_date')->nullable();
            $table->datetime('modified_date')->useCurrent();
            $table->integer('created_by')->nullable();
            $table->integer('modify_by')->nullable();

            $table->primary('id_sop');
            $table->foreign('id_subjek')->references('id_subjek')->on('tb_subjek')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('tb_sop');
    }
};
