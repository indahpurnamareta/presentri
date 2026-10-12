<?php

namespace App\Models;
use App\Models\User;

use Illuminate\Database\Eloquent\Model;

class Pengajuan extends Model
{
    protected $table = 'pengajuan';

    protected $primaryKey = 'id_pengajuan';

    protected $fillable = [
        'id_user',
        'jenis_pengajuan',
        'tanggal_mulai',
        'tanggal_selesai',
        'alasan',
        'bukti_file',
        'surat_file',
        'foto_dokumentasi',
        'status',
        'diverifikasi_oleh',
        'catatan_verifikasi'
    ];

    public function user()
    {
        return $this->belongsTo(
            User::class,
            'id_user',
            'id_user'
        );
    }

}