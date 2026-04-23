<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tb_activity_log')) {
            return;
        }

        Schema::create('tb_activity_log', function (Blueprint $table) {
            $table->integer('id_activity_log')->autoIncrement();
            $table->integer('id_user')->nullable();
            $table->string('nama_user', 150)->nullable();
            $table->string('role_user', 50)->nullable();
            $table->string('modul', 100);
            $table->string('aksi', 50);
            $table->text('deskripsi');
            $table->string('subjek_tipe', 100)->nullable();
            $table->string('subjek_id', 100)->nullable();
            $table->json('metadata')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->dateTime('created_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_activity_log');
    }
};
