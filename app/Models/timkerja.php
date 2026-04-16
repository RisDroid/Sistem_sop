<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Timkerja extends Model
{
    protected $table = 'tb_timkerja';
    protected $primaryKey = 'id_timkerja';
    public $timestamps = false;

    protected $fillable = [
        'nama_timkerja',
        'deskripsi',
        'status',
        'created_by',
        'created_date',
        'modified_by',
        'modified_date'
    ];

    public function subjek()
    {
        return $this->hasMany(Subjek::class, 'id_timkerja', 'id_timkerja');
    }

    public function user()
    {
        return $this->hasMany(User::class, 'id_timkerja', 'id_timkerja');
    }
}
