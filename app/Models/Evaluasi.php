<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluasi extends Model
{
    use HasFactory;

    protected $table = 'tb_evaluasi';
    protected $primaryKey = 'id_evaluasi';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_evaluasi',
        'id_sop',
        'id_user',
        'tanggal',
        'kriteria_evaluasi',
        'hasil_evaluasi',
        'catatan',
    ];

    protected $casts = [
        'kriteria_evaluasi' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (Evaluasi $evaluasi) {
            if (empty($evaluasi->id_evaluasi)) {
                $evaluasi->id_evaluasi = ((int) static::max('id_evaluasi')) + 1;
            }
        });
    }

    public function sop()
    {
        return $this->belongsTo(Sop::class, 'id_sop', 'id_sop');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
