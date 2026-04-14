<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model {
    protected $table = 'tb_unit';
    protected $primaryKey = 'id_unit';
    protected $fillable = ['id_subjek', 'nama_unit', 'status', 'created_by'];
    public $timestamps = false;

    public function subjek() {
        return $this->belongsTo(Subjek::class, 'id_subjek', 'id_subjek');
    }
}
