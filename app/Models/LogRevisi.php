<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogRevisi extends Model
{
    // Nama tabel sesuai migrasi sebelumnya
    protected $table = 'tb_log_revisi';

    // Karena King pakai ID standar tapi Laravel butuh kepastian primary key
    protected $primaryKey = 'id';

    // Izinkan kolom-kolom ini diisi secara massal
    protected $fillable = [
        'id_sop',
        'tanggal_revisi',
        'revisi_ke',
        'keterangan',
        'created_by'
    ];

    /**
     * Relasi Balik ke SOP
     */
    public function sop()
    {
        return $this->belongsTo(Sop::class, 'id_sop');
    }
}
