<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, MustVerifyEmailTrait, Notifiable;

    protected $table = 'tb_user';
    protected $primaryKey = 'id_user';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'nama',
        'email',
        'username',
        'nip',
        'password',
        'role',
        'id_timkerja',
        'email_verified_at',
        'remember_token',

        'created_by',
        'created_date',

        'modified_by',
        'modified_date'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
        ];
    }

    public function getAuthPassword()
    {
        return $this->password;
    }

    public function getIdAttribute(): ?int
    {
        return $this->attributes['id_user'] ?? null;
    }

    public function setNameAttribute($value): void
    {
        $this->attributes['nama'] = $value;
    }

    public function getNameAttribute(): ?string
    {
        return $this->attributes['nama'] ?? null;
    }

    public function setEmailAttribute($value): void
    {
        $this->attributes['email'] = $value;
        $this->attributes['username'] = $this->attributes['username'] ?? $value;
    }


    public function timkerja()
    {
        return $this->belongsTo(Timkerja::class,'id_timkerja','id_timkerja');
    }

    /**
     * RELASI KE PEMBUAT (Opsi Tambahan)
     * Untuk melihat siapa admin yang membuat akun ini
     */
    public function creator()
    {
        return $this->belongsTo(User::class,'created_by','id_user');
    }

    public function editor()
    {
        return $this->belongsTo(User::class,'modified_by','id_user');
    }
}
