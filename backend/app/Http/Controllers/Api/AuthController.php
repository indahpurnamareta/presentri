<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    // ==========================================
    // UBAH PASSWORD USER
    // ==========================================
    public function ubahPassword(Request $request)
    {
        // 1. Validasi input request
        $request->validate([
            'id_user' => 'required',
            'password_lama' => 'required',
            'password_baru' => 'required|min:6',
        ]);

        // 2. Cari data user berdasarkan ID
        $user = User::find($request->id_user);

        // 3. Respon jika user tidak ditemukan
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan'
            ], 404);
        }

        // 4. Validasi kecocokan password lama
        if (!Hash::check($request->password_lama, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password lama salah'
            ], 400);
        }

        // 5. Hash password baru dan simpan perubahan
        $user->password = Hash::make($request->password_baru);
        $user->save();

        // 6. Respon sukses ubah password
        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diubah'
        ]);
    }

    // ==========================================
    // SIMPAN FCM TOKEN (NOTIFIKASI)
    // ==========================================
    public function simpanToken(Request $request)
    {
        // 1. Cari data user berdasarkan ID
        $user = User::find($request->id_user);

        // 2. Respon jika user tidak ditemukan
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan dengan ID: ' . $request->id_user
            ]);
        }

        // 3. Update FCM token ke database dan simpan
        $user->fcm_token = $request->fcm_token;
        $user->save();

        // 4. Respon sukses perbarui token
        return response()->json([
            'success' => true,
            'message' => 'Token berhasil diperbarui di database'
        ]);
    }
}