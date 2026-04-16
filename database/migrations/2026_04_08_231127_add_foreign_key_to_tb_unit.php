<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tb_unit') || !Schema::hasColumn('tb_unit', 'id_subjek')) {
            return;
        }

        Schema::table('tb_unit', function (Blueprint $table) {
            $table->foreign('id_subjek')
                  ->references('id_subjek')
                  ->on('tb_subjek')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('tb_unit') || !Schema::hasColumn('tb_unit', 'id_subjek')) {
            return;
        }

        Schema::table('tb_unit', function (Blueprint $table) {
            $table->dropForeign(['id_subjek']);
        });
    }
};
