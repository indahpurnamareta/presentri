<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use App\Models\Pengajuan;
use App\Models\User;
use Illuminate\Http\Request;

class StatistikController extends Controller
{
public function guru(Request $request, $idUser)
{
    // 1. Buat instance query dasar murni dari database
    $queryPresensi = Presensi::where('id_user', $idUser);
    $queryPengajuan = Pengajuan::where('id_user', $idUser)->where('status', 'disetujui');

    // 2. Filter Tanggal Berdasarkan Semester jika dipilih dari Flutter
    if ($request->has('semester') && $request->semester != 'semua' && $request->semester != '') {
        $parts = explode('-', $request->semester);
        
        if (count($parts) == 2) {
            $tahun = $parts[0];         // Contoh: 2025 atau 2026
            $tipeSemester = strtolower($parts[1]);  // ganjil / genap

            if ($tipeSemester == 'ganjil') {
                $tanggalMulai = $tahun . '-07-01';
                $tanggalSelesai = $tahun . '-12-31';
            } else {
                $tanggalMulai = $tahun . '-01-01';
                $tanggalSelesai = $tahun . '-06-30';
            }

            // Terapkan filter rentang tanggal ke query presensi dan pengajuan
            $queryPresensi->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai]);
            $queryPengajuan->whereBetween('tanggal_mulai', [$tanggalMulai, $tanggalSelesai]);
        }
    }

    // 3. Hitung hasil menggunakan kloning query (clone) agar tidak saling tumpang tindih

    // Tepat Waktu: jika status = 'hadir' dan status_keterlambatan BUKAN 'terlambat'
    $tepat_waktu = (clone $queryPresensi)
        ->where('status', 'hadir')
        ->where(function($q) {
            $q->where('status_keterlambatan', '!=', 'terlambat')
              ->orWhereNull('status_keterlambatan')
              ->orWhere('status_keterlambatan', '');
        })
        ->count();

    // Terlambat: jika status_keterlambatan = 'terlambat'
    $terlambat = (clone $queryPresensi)
        ->where('status_keterlambatan', 'terlambat')
        ->count();

    // Alpha / Alfa: jika status = 'alpha' (atau 'alfa' sesuai database kecil semua)
    $alpha = (clone $queryPresensi)
        ->where(function($q) {
            $q->where('status', 'alpha')
              ->orWhere('status', 'alfa');
        })
        ->count();

    // Dinas Luar: dihitung berdasarkan presensi berstatus 'dinas_luar'
    $dinas_luar = (clone $queryPresensi)
        ->where('status', 'dinas_luar')
        ->count();

    // WFH: dihitung berdasarkan presensi berstatus 'wfh'
    $wfh = (clone $queryPresensi)
        ->where('status', 'wfh')
        ->count();

    // Izin: diambil dari tabel pengajuan yang disetujui
    $izin = (clone $queryPengajuan)
        ->where(function($q) {
            $q->where('jenis_pengajuan', 'Izin')
              ->orWhere('jenis_pengajuan', 'izin');
        })
        ->count();

    // Sakit: diambil dari tabel pengajuan yang disetujui
    $sakit = (clone $queryPengajuan)
        ->where(function($q) {
            $q->where('jenis_pengajuan', 'Sakit')
              ->orWhere('jenis_pengajuan', 'sakit');
        })
        ->count();

    // Cuti: diambil dari tabel pengajuan yang disetujui (antisipasi huruf besar/kecil)
    $cuti = (clone $queryPengajuan)
        ->where(function($q) {
            $q->where('jenis_pengajuan', 'Cuti')
              ->orWhere('jenis_pengajuan', 'cuti');
        })
        ->count();

    // 4. Return response JSON dengan key yang dicari oleh Flutter Anda
    return response()->json([
        'tepat_waktu' => $tepat_waktu,
        'terlambat'   => $terlambat,
        'izin'        => $izin,
        'sakit'       => $sakit,
        'cuti'        => $cuti,
        'dinas_luar'  => $dinas_luar,
        'wfh'         => $wfh,
        'alpha'       => $alpha,
    ]);
}

    public function kepsek()
    {
        $totalGuru = User::where('role', 'guru')->count();
        $hadir = Presensi::whereDate('tanggal', now())->count();
        
        $izin = Pengajuan::whereDate('tanggal_mulai', now())
            ->where('jenis_pengajuan', 'Izin')
            ->count();

        $sakit = Pengajuan::whereDate('tanggal_mulai', now())
            ->where('jenis_pengajuan', 'Sakit')
            ->count();

        return response()->json([
            'total_guru' => $totalGuru,
            'hadir' => $hadir,
            'izin' => $izin,
            'sakit' => $sakit,
        ]);
    }
}