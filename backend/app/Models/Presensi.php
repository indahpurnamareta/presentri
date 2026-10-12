<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Presensi extends Model
{
    protected $table = 'presensi';

    protected $primaryKey = 'id_presensi';

    protected $fillable = [
        'id_user',
        'id_lokasi',
        'id_qr',
        'tanggal',
        'jam_masuk',
        'jam_keluar',
        'status',
        'status_keterlambatan',
        'latitude',
        'longitude',
        'jarak',
        'keterangan'
    ];

    // relasi ke user
    public function user()
    {
        return $this->belongsTo(
            User::class,
            'id_user',
            'id_user'
        );
    }
}