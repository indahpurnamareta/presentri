<?php

namespace App\Models;
use App\Models\Presensi;
use App\Models\Pengajuan;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';

    protected $primaryKey = 'id_user';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = true;

    protected $fillable = [
        'username',
        'password',
        'role',
        'jenis_gtk',
        'nama',
        'nip',
        'mata_pelajaran',
        'no_hp',
        'foto',
        'device_id',
        'id_jam_kerja',
        'status_akun',
        'fcm_token'
    ];

    protected $hidden = [
        'password',
    ];

    public function presensi()
{
    return $this->hasMany(
        Presensi::class,
        'id_user',
        'id_user'
    );
}

public function pengajuan()
{
    return $this->hasMany(
        Pengajuan::class,
        'id_user',
        'id_user'
    );
}
}