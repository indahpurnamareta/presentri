<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RekapPresensiController;
use App\Http\Controllers\Admin\RekapIzinController;
use App\Http\Controllers\Admin\PresensiKhususController;
use App\Http\Controllers\Admin\HariLiburController;
use App\Http\Controllers\Admin\JamKerjaController;
use App\Http\Controllers\Admin\LokasiController;
use App\Http\Controllers\Admin\QrCodeController;
use App\Http\Controllers\Admin\ProfileAdminController;

/*
|--------------------------------------------------------------------------
| LANDING & AUTHENTICATION (WEB)
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('auth.cover'); 
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.proses');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| ADMIN DASHBOARD
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('auth')->name('admin.dashboard');

/*
|--------------------------------------------------------------------------
| USER MANAGEMENT (PREFIX: admin/user)
|--------------------------------------------------------------------------
*/
Route::prefix('admin/user')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('admin.user');
    Route::post('/store', [UserController::class, 'store'])->name('admin.user.store');
    Route::post('/update/{id}', [UserController::class, 'update'])->name('admin.user.update');
    Route::post('/delete/{id}', [UserController::class, 'delete'])->name('admin.user.delete');
    Route::post('/user/reset-device/{id}', [UserController::class, 'resetDevice'])->name('admin.user.resetDevice');
    Route::get('/user/download-pdf', [UserController::class, 'downloadPdf'])->name('admin.user.download-pdf');
});

/*
|--------------------------------------------------------------------------
| REKAP PRESENSI & REKAP IZIN / SAKIT
|--------------------------------------------------------------------------
*/
// Rekap Presensi
Route::get('/admin/rekap-presensi', [RekapPresensiController::class, 'index'])->name('admin.rekap.presensi');
Route::get('/admin/rekap-presensi/pdf', [RekapPresensiController::class, 'downloadPdf'])->name('admin.rekap.presensi.pdf');

// Rekap Izin
Route::get('/admin/rekap-izin', [RekapIzinController::class, 'index'])->name('admin.rekap.izin');
Route::post('/rekap-izin/store', [RekapIzinController::class, 'store'])->name('admin.rekap.izin.store');
Route::get('/admin/rekap-izin/pdf', [RekapIzinController::class, 'downloadPdf'])->name('admin.rekap.izin.pdf');
Route::post('/rekap-izin', [RekapIzinController::class, 'store'])->name('admin.rekap.izin.store');
Route::put('/rekap-izin/{id}', [RekapIzinController::class, 'update'])->name('admin.rekap.izin.update');
Route::delete('/rekap-izin/{id}', [RekapIzinController::class, 'destroy'])->name('admin.rekap.izin.destroy');
/*
|--------------------------------------------------------------------------
| PRESENSI KHUSUS (DINAS LUAR / WFH)
|--------------------------------------------------------------------------
*/
Route::get('/admin/presensi-khusus', [PresensiKhususController::class, 'index'])->name('admin.presensi.khusus');
Route::post('/presensi-khusus/store', [PresensiKhususController::class, 'store'])->name('admin.presensi.khusus.store');
Route::get('/admin/presensi-khusus/pdf', [PresensiKhususController::class, 'downloadPdf'])->name('admin.presensi.khusus.pdf');
Route::put('/presensi-khusus/{id}', [PresensiKhususController::class, 'update'])->name('presensi-khusus.update');
Route::delete('/presensi-khusus/{id}', [PresensiKhususController::class, 'destroy'])->name('presensi-khusus.destroy');


/*
|--------------------------------------------------------------------------
| MASTER DATA: HARI LIBUR
|--------------------------------------------------------------------------
*/
Route::get('/admin/hari-libur', [HariLiburController::class, 'index'])->name('admin.hari.libur');
Route::post('/admin/hari-libur/store', [HariLiburController::class, 'store'])->name('admin.hari.libur.store');
Route::post('/admin/hari-libur/update/{id}', [HariLiburController::class, 'update'])->name('admin.hari.libur.update');
Route::post('/admin/hari-libur/delete/{id}', [HariLiburController::class, 'delete'])->name('admin.hari.libur.delete');

/*
|--------------------------------------------------------------------------
| MASTER DATA: JAM KERJA
|--------------------------------------------------------------------------
*/
Route::get('/admin/jam-kerja', [JamKerjaController::class, 'index'])->name('admin.jam.kerja');
Route::post('/admin/jam-kerja/store', [JamKerjaController::class, 'store'])->name('admin.jam.kerja.store');
Route::post('/admin/jam-kerja/update/{id}', [JamKerjaController::class, 'update'])->name('admin.jam.kerja.update');
Route::post('/admin/jam-kerja/delete/{id}', [JamKerjaController::class, 'delete'])->name('admin.jam.kerja.delete');
Route::post('/admin/jam-kerja/toggle-wfh', [JamKerjaController::class, 'toggleWfhMassal'])->name('admin.jam-kerja.toggle-wfh');

/*
|--------------------------------------------------------------------------
| MASTER DATA: LOKASI ABSENSI
|--------------------------------------------------------------------------
*/
Route::get('/admin/lokasi', [LokasiController::class, 'index'])->name('admin.lokasi');
Route::post('/admin/lokasi/store', [LokasiController::class, 'store'])->name('admin.lokasi.store');
Route::post('/admin/lokasi/update/{id}', [LokasiController::class, 'update'])->name('admin.lokasi.update');
Route::post('/admin/lokasi/delete/{id}', [LokasiController::class, 'destroy'])->name('admin.lokasi.delete');

/*
|--------------------------------------------------------------------------
| MASTER DATA: QR CODE MANAGEMENT
|--------------------------------------------------------------------------
*/
Route::get('/admin/qr-code', [QrCodeController::class, 'index'])->name('admin.qrcode');
Route::post('/admin/qr-code/store', [QrCodeController::class, 'store'])->name('admin.qrcode.store');
Route::post('/admin/qr-code/update/{id}', [QrCodeController::class, 'update'])->name('admin.qrcode.update');
Route::post('/admin/qr-code/delete/{id}', [QrCodeController::class, 'delete'])->name('admin.qrcode.delete');
Route::get('/admin/qr-code/print/{id}', [QrCodeController::class, 'print'])->name('admin.qrcode.print');

/*
|--------------------------------------------------------------------------
| ADMIN PROFILE MANAGEMENT
|--------------------------------------------------------------------------
*/
Route::get('/profile', [ProfileAdminController::class, 'edit'])->name('profile.edit');
Route::post('/profile', [ProfileAdminController::class, 'update'])->name('profile.update');

//Export Excel
    // 1. Export Excel Presensi Khusus (Dinas Luar & WFH)
    Route::get('/presensi-khusus/export-excel', [PresensiKhususController::class, 'downloadExcel'])->name('presensi-khusus.export-excel');

    // 2. Export Excel Rekap Perizinan (Izin, Sakit, Cuti)
    Route::get('/rekap-izin/export-excel', [RekapIzinController::class, 'downloadExcel'])->name('rekap-izin.export-excel');

    // 3. Export Excel Rekap Presensi Utama
    Route::get('/rekap-presensi/export-excel', [RekapPresensiController::class, 'downloadExcel'])->name('rekap-presensi.export-excel');

    // 4. Export Excel Data User / GTK
    Route::get('/user/export-excel', [UserController::class, 'downloadExcel'])->name('user.export-excel');
    
    


