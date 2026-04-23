<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tb_sop_revision_attachments')) {
            return;
        }

        Schema::create('tb_sop_revision_attachments', function (Blueprint $table) {
            $table->increments('id_attachment');
            $table->integer('id_sop');
            $table->string('original_name');
            $table->string('file_path');
            $table->dateTime('created_date')->nullable();
            $table->integer('created_by')->nullable();

            $table->foreign('id_sop')
                ->references('id_sop')
                ->on('tb_sop')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_sop_revision_attachments');
    }
};
