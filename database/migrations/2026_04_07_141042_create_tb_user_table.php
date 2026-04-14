<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tb_user', function (Blueprint $table) {
            $table->integer('id_user')->autoIncrement();
            $table->string('nama', 255);
            $table->string('username', 255)->unique();
            $table->string('nip', 255)->unique();
            $table->string('password', 255);
            $table->enum('role', ['Admin', 'Operator', 'Viewer']);
            $table->integer('id_subjek')->nullable(); // Relasi ke tb_subjek
            $table->datetime('created_date')->nullable();
            $table->datetime('modified_date')->useCurrent();
            $table->integer('created_by')->nullable();
            $table->integer('modified_by')->nullable();

            $table->primary('id_user');
            // Foreign Key Link
            $table->foreign('id_subjek')->references('id_subjek')->on('tb_subjek')->onDelete('set null');
        });
    }

    public function down(): void {
        Schema::dropIfExists('tb_user');
    }
};
