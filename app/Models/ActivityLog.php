<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $table = 'tb_activity_logs';
    protected $primaryKey = 'id_activity_log';
    public $timestamps = false;

    protected $fillable = [
        'id_user',
        'nama_user',
        'username',
        'role_user',
        'modul',
        'aksi',
        'deskripsi',
        'subjek_tipe',
        'subjek_id',
        'metadata',
        'ip_address',
        'user_agent',
        'created_date',
        'created_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_date' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
