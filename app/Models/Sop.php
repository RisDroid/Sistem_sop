<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sop extends Model
{
    use HasFactory;

    protected $table = 'tb_sop';
    protected $primaryKey = 'id_sop';

    // Matikan timestamps default Laravel karena Anda menggunakan kolom kustom
    public $timestamps = false;

    protected $fillable = [
        'nama_sop',
        'nomor_sop',
        'tahun',
        'revisi_ke',
        'id_subjek',
        'file_sop',
        'link_sop',
        'status',
        'created_date',
        'modified_date',
        'created_by',
        'modified_by',
        'keterangan',
    ];

    protected $casts = [
        'revisi_ke' => 'integer',
    ];

    public function subjek()
    {
        return $this->belongsTo(Subjek::class, 'id_subjek', 'id_subjek');
    }

    public function getStatusAttribute(): ?string
    {
        return $this->attributes['status'] ?? null;
    }

    public function getUnitAttribute()
    {
        return $this->subjek?->timkerja;
    }

    public function getStatusActiveAttribute(): int
    {
        return ($this->attributes['status'] ?? null) === 'aktif' ? 1 : 0;
    }

    public function setStatusActiveAttribute($value): void
    {
        $this->attributes['status'] = (int) $value === 1 ? 'aktif' : 'nonaktif';
    }
}
