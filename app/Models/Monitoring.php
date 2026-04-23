<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Monitoring extends Model
{
    use HasFactory;

    protected $table = 'tb_monitoring';
    protected $primaryKey = 'id_monitoring';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_monitoring',
        'id_sop',
        'tanggal',
        'id_user',
        'kriteria_penilaian',
        'hasil_monitoring',
        'tindakan_yang_harus_diambil',
        'catatan'
    ];

    protected static function booted(): void
    {
        static::creating(function (Monitoring $monitoring) {
            if (empty($monitoring->id_monitoring)) {
                $monitoring->id_monitoring = ((int) static::max('id_monitoring')) + 1;
            }
        });
    }

    // Relasi ke SOP
    public function sop()
    {
        return $this->belongsTo(Sop::class, 'id_sop', 'id_sop');
    }

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
