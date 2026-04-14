<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subjek extends Model
{
    use HasFactory;

    protected $table = 'tb_subjek';
    protected $primaryKey = 'id_subjek';
    public $timestamps = false; // Karena kamu pakai kolom manual created_date

    protected $fillable = [
        'nama_subjek',
        'deskripsi',
        'status',
        'created_by',
        'created_date',
        'modified_by',
        'modified_date'
    ];

    /**
     * Boot function untuk mengisi created_date otomatis saat tambah data
     */
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (!$model->created_date) {
                $model->created_date = now();
            }
        });
    }

    public function units()
    {
        return $this->hasMany(Unit::class, 'id_subjek', 'id_subjek');
    }

    public function sops()
    {
        return $this->hasMany(Sop::class, 'id_subjek', 'id_subjek');
    }
}
