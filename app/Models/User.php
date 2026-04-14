<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // King: Tambahkan baris ini!

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'tb_user';
    protected $primaryKey = 'id_user';
    public $timestamps = false;

    protected $fillable = [
        'nama',
        'username',
        'nip',
        'password',
        'role',
        'id_subjek',
        'created_by',   // King: Tambahkan ini agar bisa simpan siapa yang buat user
        'created_date'  // King: Tambahkan ini agar tanggal input tersimpan
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * RELASI KE TABEL SUBJEK
     * Menghubungkan id_subjek di tb_user ke id_subjek di tb_subjek
     */
    public function subjek(): BelongsTo
    {
        return $this->belongsTo(Subjek::class, 'id_subjek', 'id_subjek');
    }

    /**
     * RELASI KE PEMBUAT (Opsi Tambahan)
     * Untuk melihat siapa admin yang membuat akun ini
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id_user');
    }
}
