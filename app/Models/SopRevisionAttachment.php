<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SopRevisionAttachment extends Model
{
    protected $table = 'tb_sop_revision_attachments';

    protected $primaryKey = 'id_attachment';

    public $timestamps = false;

    protected $fillable = [
        'id_sop',
        'original_name',
        'file_path',
        'created_date',
        'created_by',
    ];

    public function sop()
    {
        return $this->belongsTo(Sop::class, 'id_sop', 'id_sop');
    }
}
