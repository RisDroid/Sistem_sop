<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tb_subjek', function (Blueprint $table) {
            $table->integer('id_subjek')->autoIncrement();
            $table->integer('id_timkerja')->nullable();
            $table->string('nama_subjek', 255);
            $table->text('deskripsi')->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->datetime('created_date')->nullable();
            $table->datetime('modified_date')->useCurrent();
            $table->integer('created_by')->nullable();
            $table->integer('modified_by')->nullable();
        });
    }

    public function down(): void {
        Schema::dropIfExists('tb_subjek');
    }
};
