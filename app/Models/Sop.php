<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sop extends Model
{
    use HasFactory;

    protected $table = 'tb_sop';
    protected $primaryKey = 'id_sop';

    // Matikan timestamps default Laravel karena Anda menggunakan kolom kustom
    public $timestamps = false;

   protected $fillable = [
    'nama_sop',
    'nomor_sop',
    'tahun',
    'revisi_ke',
    'id_subjek',
    'id_unit',      // Pastikan ini ada
    'status_active',
    'link_sop',
    'created_date',
    'modified_date',
    'created_by',
    'modify_by'
];

    public function subjek()
    {
        return $this->belongsTo(Subjek::class, 'id_subjek', 'id_subjek');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'id_unit', 'id_unit');
    }
}
