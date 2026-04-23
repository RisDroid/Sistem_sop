<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginLog extends Model
{
    protected $table = 'tb_login_logs';
    protected $primaryKey = 'id_login_log';
    public $timestamps = false;

    protected $fillable = [
        'id_user',
        'nama_user',
        'username',
        'role_user',
        'aksi',
        'deskripsi',
        'ip_address',
        'user_agent',
        'login_at',
        'logout_at',
        'created_date',
        'created_at',
    ];

    protected $casts = [
        'login_at' => 'datetime',
        'logout_at' => 'datetime',
        'created_date' => 'datetime',
        'created_at' => 'datetime',
    ];
}
