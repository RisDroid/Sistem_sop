<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tb_user', function (Blueprint $table) {
            $table->integer('id_user')->autoIncrement();
            $table->string('email', 255)->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('name', 255)->nullable();
            $table->string('nama', 255);
            $table->string('username', 255)->unique();
            $table->string('nip', 255)->nullable()->unique();
            $table->string('password', 255);
            $table->enum('role', ['Admin', 'Operator', 'Viewer'])->default('Admin');
            $table->integer('id_subjek')->nullable(); // Relasi ke tb_subjek
            $table->integer('id_timkerja')->nullable();
            $table->datetime('created_date')->nullable();
            $table->datetime('modified_date')->useCurrent();
            $table->integer('created_by')->nullable();
            $table->integer('modified_by')->nullable();
            $table->rememberToken();
        });
    }

    public function down(): void {
        Schema::dropIfExists('tb_user');
    }
};
