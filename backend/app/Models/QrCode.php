<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QrCode extends Model
{
    protected $table = 'qrcodes';

    protected $primaryKey = 'id_qr';

    protected $fillable = [
        'kode_qr',
        'nama_qr',
        'status'
    ];
}