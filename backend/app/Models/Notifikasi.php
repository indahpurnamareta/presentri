<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    protected $table = 'notifikasi';
    protected $primaryKey =
    'id_notifikasi';

    protected $fillable = [

    'id_user',
    'judul',
    'pesan',
    'dibaca',
];
}
