<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JamKerja extends Model
{
    protected $table = 'jam_kerja';

    protected $primaryKey = 'id_jam_kerja';

    protected $fillable = [
        'kode_jk',
        'mode',
        'nama_jk',
        'jam_masuk',
        'batas_jam_masuk',
        'jam_pulang',
        'status'
    ];
}