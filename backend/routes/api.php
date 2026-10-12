<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Api\PresensiController;
use App\Http\Controllers\Api\PengajuanController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\StatistikController;
use App\Http\Controllers\Api\DashboardKepsekController;
use App\Http\Controllers\Api\GuruController;
use App\Http\Controllers\Api\NotifikasiController;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\MessageTarget;
use Kreait\Firebase\Http\HttpClientOptions;

/*
|--------------------------------------------------------------------------
| AUTHENTICATION & TOKEN
|--------------------------------------------------------------------------
*/
Route::post('/login', function (Request $request) {
    // Cek username
    $user = User::where('username', $request->username)->first();

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Username tidak ditemukan'
        ]);
    }

    // Cek password
    if (!Hash::check($request->password, $user->password)) {
        return response()->json([
            'success' => false,
            'message' => 'Password salah'
        ]);
    }

    // CEK ROLE
    if (!in_array($user->role, ['guru', 'kepala_sekolah'])) {
        return response()->json([
            'success' => false,
            'message' => 'Akun ini tidak memiliki akses ke aplikasi mobile.'
        ], 403);
    }

    // LOGIN PERTAMA
    if ($user->device_id == null) {
        $user->device_id = $request->device_id;
        $user->save();
    }

    // LOGIN DEVICE BERBEDA
    if ($user->device_id != $request->device_id) {
        return response()->json([
            'success' => false,
            'message' => 'Akun ini sudah digunakan pada perangkat lain.'
        ], 403);
    }

    return response()->json([
        'success' => true,
        'message' => 'Login berhasil',
        'user' => $user
    ]);
});

Route::post('/ubah-password', [AuthController::class, 'ubahPassword']);
Route::post('/simpan-token', [AuthController::class, 'simpanToken']);

/*
|--------------------------------------------------------------------------
| PRESENSI & RIWAYAT (GURU)
|--------------------------------------------------------------------------
*/
Route::post('/presensi-masuk', [PresensiController::class, 'presensiMasuk']);
Route::post('/presensi-pulang', [PresensiController::class, 'presensiPulang']);
Route::post('/presensi-dinas-luar', [PresensiController::class, 'presensiDinasLuar']);
Route::post('/presensi-wfh', [PresensiController::class, 'presensiWfh']);
Route::get('/status-hari-ini/{id_user}', [PresensiController::class, 'statusHariIni']);
Route::get('/riwayat-presensi/{id_user}', [PresensiController::class, 'riwayatPresensi']);
Route::get('/download-surat-tugas/{filename}', [PresensiController::class, 'downloadSuratTugas']);
/*
|--------------------------------------------------------------------------
| PENGAJUAN IZIN & SAKIT
|--------------------------------------------------------------------------
*/
Route::post('/pengajuan', [PengajuanController::class, 'store']);
Route::get('/pengajuan/{idUser}', [PengajuanController::class, 'riwayat']);
Route::get('/download-surat/{id}', [PengajuanController::class, 'downloadSurat']);
Route::get('/preview-surat/{id}', [PengajuanController::class, 'previewSurat']);
Route::get('/pengajuan-semua', [PengajuanController::class, 'semuaPengajuan']);
Route::post('/verifikasi-pengajuan', [PengajuanController::class, 'verifikasi']);

/*
|--------------------------------------------------------------------------
| PROFILE USER
|--------------------------------------------------------------------------
*/
Route::get('/profile/{id}', [ProfileController::class, 'profile']);
Route::post('/update-profile', [ProfileController::class, 'updateProfile']);
Route::post('/update-profile-kepsek', [ProfileController::class, 'updateProfile']);

/*
|--------------------------------------------------------------------------
| DATA & STATISTIK GURU
|--------------------------------------------------------------------------
*/
Route::get('/guru', [GuruController::class, 'index']);
Route::get('/guru/{id}', [GuruController::class, 'detail']);
Route::get('/statistik-guru/{id}', [StatistikController::class, 'guru']);

/*
|--------------------------------------------------------------------------
| DASHBOARD & MANAGEMENT KEPALA SEKOLAH
|--------------------------------------------------------------------------
*/
Route::get('/dashboard-kepsek', [DashboardKepsekController::class, 'index']);
Route::get('/statistik-kepsek', [StatistikController::class, 'kepsek']);
Route::get('/kepsek/guru-belum-presensi', [DashboardKepsekController::class, 'guruBelumPresensi']);
Route::get('/kepsek/grafik-presensi', [DashboardKepsekController::class, 'grafikPresensiMingguan']);
Route::get('/kepsek/rekap-presensi', [DashboardKepsekController::class, 'rekapPresensiApi']);

/*
|--------------------------------------------------------------------------
| UNDUH / EXPORT PDF VIA API
|--------------------------------------------------------------------------
*/
Route::get('/kepsek/rekap-presensi-pdf', [DashboardKepsekController::class, 'cetakPdfApi']);
Route::get('/kepsek/cetak-rekap-izin', [PengajuanController::class, 'cetakRekapIzinApi']);
Route::get('/kepsek/cetak-pdf-gtk', [GuruController::class, 'cetakPdfGtkApi']);

/*
|--------------------------------------------------------------------------
| NOTIFIKASI SYSTEM
|--------------------------------------------------------------------------
*/
Route::get('/notifikasi/{idUser}', [NotifikasiController::class, 'getNotifikasi']);
Route::get('/notifikasi-total/{idUser}', [NotifikasiController::class, 'totalBelumDibaca']);
Route::post('/notifikasi/baca/{idNotifikasi}', [NotifikasiController::class, 'tandaiDibaca']);
// Route Hapus 1 Notifikasi
Route::delete('/notifikasi/{id}', [NotifikasiController::class, 'destroy']);
// Route Hapus Semua Notifikasi User
Route::delete('/notifikasi/hapus-semua/{id_user}', [NotifikasiController::class, 'destroyAll']);
/*
|--------------------------------------------------------------------------
| TESTING & TROUBLESHOOTING TOOL (FIREBASE, CURL, OPENSSL)
|--------------------------------------------------------------------------
*/
Route::post('/test-kirim-notif', function (Request $request) {
    // 1. Cari user berdasarkan ID
    $user = User::find($request->id_user);

    if (!$user || !$user->fcm_token) {
        return response()->json([
            'success' => false,
            'message' => 'User atau FCM Token tidak ditemukan.'
        ], 404);
    }

    $deviceToken = $user->fcm_token; 

    // 2. Susun pesan
    $message = CloudMessage::fromArray([
        'token' => $deviceToken,
        'notification' => [
            'title' => $request->title ?? 'Uji Coba Notifikasi',
            'body'  => $request->body ?? 'Halo! Sistem Presentri berhasil terhubung dengan Firebase.',
        ],
        'data' => [
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            'status'       => 'test_success',
        ],
    ]);

    // 3. Eksekusi pengiriman dengan proteksi Try-Catch
    try {
        // Trik pamungkas: Paksa PHP melupakan file cacert.pem yang rusak khusus di lokal
        if (app()->environment('local')) {
            // Ubah konfigurasi memory runtime PHP menjadi kosong agar cURL mengabaikan file sertifikat
            ini_set('curl.cainfo', '');
            ini_set('openssl.cafile', '');
            
            // Matikan verifikasi peer secara global di context stream default PHP
            stream_context_set_default([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ],
            ]);
        }

        $messaging = app('firebase.messaging');
        $messaging->send($message);

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi berhasil dikirim ke ' . $user->nama
        ]);
        
    } catch (\Throwable $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], 500);
    }
});

Route::get('/cek-curl', function () {
    return response()->json([
        'curl.cainfo' => ini_get('curl.cainfo'),
        'openssl.cafile' => ini_get('openssl.cafile'),
        'exists' => file_exists(ini_get('curl.cainfo')),
    ]);
});

Route::get('/cek-openssl', function () {
    return response()->json([
        'openssl' => extension_loaded('openssl'),
        'curl' => extension_loaded('curl'),
        'openssl_version' => OPENSSL_VERSION_TEXT,
    ]);
});

Route::get('/tes-google', function () {
    try {
        $response = Http::get('https://oauth2.googleapis.com');
        return [
            'success' => true,
            'status' => $response->status(),
        ];
    } catch (\Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage(),
        ];
    }
});

Route::get('/cek-firebase', function () {
    try {
        $factory = app(\Kreait\Firebase\Factory::class);
        return [
            'success' => true,
            'message' => 'Firebase OK'
        ];
    } catch (\Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
});

Route::get('/cek-token/{id}', function ($id) {
    $user = User::find($id);
    return [
        'nama' => $user->nama,
        'token' => $user->fcm_token,
        'panjang' => strlen($user->fcm_token ?? ''),
    ];
});

Route::get('/tes-messaging', function () {
    try {
        $messaging = app('firebase.messaging');
        return [
            'success' => true,
            'class' => get_class($messaging),
        ];
    } catch (\Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage(),
        ];
    }
});