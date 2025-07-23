<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UploadFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'upload_session_id',
        'name',
        'path',
        'size',
        'type',
    ];

    public function session()
    {
        return $this->belongsTo(UploadSession::class, 'upload_session_id');
    }
}
