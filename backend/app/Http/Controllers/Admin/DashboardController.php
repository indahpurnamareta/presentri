<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Presensi;
use App\Models\Pengajuan;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Mengambil tanggal hari ini secara real-time
        $today = Carbon::today()->toDateString();

        // 1. Total seluruh guru terdaftar (tetap total keseluruhan)
         $totalGuru = User::whereIn('role', ['guru', 'kepala_sekolah'])->count();

        // 2. Total hadir tepat waktu HARI INI
        $totalHadir = Presensi::whereDate('created_at', $today) 
            ->where('status', 'hadir')
            ->where('status_keterlambatan', 'tepat_waktu')
            ->count();

        // 3. Total terlambat HARI INI
        $totalTerlambat = Presensi::whereDate('created_at', $today)
            ->where('status_keterlambatan', 'terlambat')
            ->count();

        // 4. Total alpha HARI INI
        $totalTidakHadir = Presensi::whereDate('created_at', $today)
            ->where('status', 'alpha')
            ->count();

        // 5. Total izin HARI INI
        $totalIzin = Presensi::whereDate('created_at', $today)
            ->where('status', 'izin')
            ->count();

        // 6. Total sakit HARI INI
        $totalSakit = Presensi::whereDate('created_at', $today)
            ->where('status', 'sakit')
            ->count();

        // 7. Total cuti HARI INI
        // Memastikan status disetujui dan hari ini berada di antara tanggal_mulai dan tanggal_selesai cuti
        $totalCuti = Pengajuan::where('jenis_pengajuan', 'cuti')
            ->where('status', 'disetujui')
            ->where(function($query) use ($today) {
                $query->whereDate('tanggal_mulai', '<=', $today)
                      ->whereDate('tanggal_selesai', '>=', $today);
            })
            ->count();
        
     // Memastikan status disetujui dan hari ini berada di antara tanggal_mulai dan tanggal_selesai cuti
        $totalDinasLuar = Pengajuan::where('jenis_pengajuan', 'dinas_luar')
            ->where('status', 'disetujui')
            ->where(function($query) use ($today) {
                $query->whereDate('tanggal_mulai', '<=', $today)
                      ->whereDate('tanggal_selesai', '>=', $today);
            })
            ->count();

        return view('admin.pages.dashboard.index', compact(
            'totalGuru',
            'totalHadir',
            'totalTerlambat',
            'totalTidakHadir',
            'totalIzin',
            'totalSakit',
            'totalCuti',
            'totalDinasLuar'
        ));
    }
}