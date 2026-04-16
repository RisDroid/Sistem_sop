<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subjek extends Model
{
    protected $table = 'tb_subjek';
    protected $primaryKey = 'id_subjek';
    public $timestamps = false;

    protected $fillable = [
        'id_timkerja',
        'nama_subjek',
        'deskripsi',
        'status',
        'created_by',
        'created_date',
        'modified_by',
        'modified_date'
    ];

    public function timkerja()
    {
        return $this->belongsTo(Timkerja::class, 'id_timkerja', 'id_timkerja');
    }

    public function sop()
    {
        return $this->hasMany(Sop::class, 'id_subjek', 'id_subjek');
    }

    public function sops()
    {
        return $this->sop();
    }
}
