<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tb_monitoring')) {
            return;
        }

        Schema::table('tb_monitoring', function (Blueprint $table) {
            if (!Schema::hasColumn('tb_monitoring', 'tindakan_yang_harus_diambil')) {
                $table->string('tindakan_yang_harus_diambil', 50)->nullable()->after('hasil_monitoring');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('tb_monitoring')) {
            return;
        }

        Schema::table('tb_monitoring', function (Blueprint $table) {
            if (Schema::hasColumn('tb_monitoring', 'tindakan_yang_harus_diambil')) {
                $table->dropColumn('tindakan_yang_harus_diambil');
            }
        });
    }
};
