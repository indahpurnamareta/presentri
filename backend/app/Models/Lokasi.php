<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lokasi extends Model
{
    protected $table = 'lokasi';

    protected $primaryKey = 'id_lokasi';

    protected $fillable = [
        'nama_lokasi',
        'latitude',
        'longitude',
        'radius',
        'status'
    ];
}