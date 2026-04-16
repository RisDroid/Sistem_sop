<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Monitoring extends Model
{
    use HasFactory;

    protected $table = 'tb_monitoring';
    protected $primaryKey = 'id_monitoring';
    public $timestamps = false;

    protected $fillable = [
        'id_sop',
        'tanggal',
        'id_user',
        'kriteria_penilaian',
        'hasil_monitoring',
        'catatan'
    ];

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
