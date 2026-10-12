<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;


class ProfileController extends Controller
{
    public function statistik($idUser)
    {
        $hadir = \DB::table('presensi')
            ->where('id_user', $idUser)
            ->where('status_presensi', 'Tepat Waktu')
            ->count();

        $telat = \DB::table('presensi')
            ->where('id_user', $idUser)
            ->where('status_presensi', 'Terlambat')
            ->count();

        $izin = \DB::table('pengajuan')
            ->where('id_user', $idUser)
            ->where('jenis_pengajuan', 'Izin')
            ->count();

        $sakit = \DB::table('pengajuan')
            ->where('id_user', $idUser)
            ->where('jenis_pengajuan', 'Sakit')
            ->count();

        return response()->json([
            'hadir' => $hadir,
            'telat' => $telat,
            'izin' => $izin,
            'sakit' => $sakit,
        ]);
    }
    
public function profile($id)
{
    $user = User::find($id);

    if (!$user) {
        return response()->json(['message' => 'User tidak ditemukan'], 404);
    }

    return response()->json([
        'nama' => $user->nama,
        'nip' => $user->nip, 
        'mata_pelajaran' => $user->mata_pelajaran,
        'no_hp' => $user->no_hp,
    ]);
}

public function updateProfile(Request $request)
{
    $user = User::find($request->id_user);

    if (!$user) {
        return response()->json(['message' => 'User tidak ditemukan'], 404);
    }

    // NIP sengaja tidak diupdate demi alasan keamanan data utama (Read-Only)
    $user->nama = $request->nama;
    $user->mata_pelajaran = $request->mata_pelajaran;
    $user->no_hp = $request->no_hp;
    $user->save();

    return response()->json([
        'message' => 'Profil berhasil diperbarui'
    ]);
}
}
