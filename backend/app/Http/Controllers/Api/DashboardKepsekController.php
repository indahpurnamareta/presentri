<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Presensi;
use App\Models\Pengajuan;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardKepsekController extends Controller
{
    public function index()
    {
        $hariIni = now()->format('Y-m-d');

       $totalGuru = User::whereIn('role', ['guru', 'kepala_sekolah'])->count();

        $hadirHariIni = Presensi::where(
            'tanggal',
            $hariIni
        )
        ->where(
            'status',
            'hadir'
        )
        ->count();

        $terlambatHariIni = Presensi::where(
            'tanggal',
            $hariIni
        )
        ->where(
            'status_keterlambatan',
            'terlambat'
        )
        ->count();

        $izinMenunggu = Pengajuan::where('jenis_pengajuan', 'Izin')
            ->where('status', 'disetujui')
            ->whereDate('tanggal_mulai', '<=', $hariIni)
            ->whereDate('tanggal_selesai', '>=', $hariIni)
            ->count();

        $sakitMenunggu = Pengajuan::where('jenis_pengajuan', 'Sakit')
            ->where('status', 'disetujui')
            ->whereDate('tanggal_mulai', '<=', $hariIni)
            ->whereDate('tanggal_selesai', '>=', $hariIni)
            ->count();

        $cutiMenunggu = Pengajuan::where('jenis_pengajuan', 'Cuti')
            ->where('status', 'disetujui')
            ->whereDate('tanggal_mulai', '<=', $hariIni)
            ->whereDate('tanggal_selesai', '>=', $hariIni)
            ->count();

        return response()->json([
            'total_guru' => $totalGuru,
            'hadir'      => $hadirHariIni,
            'terlambat'  => $terlambatHariIni,
            'izin'       => $izinMenunggu,
            'sakit'      => $sakitMenunggu,
            'cuti'       => $cutiMenunggu,
        ]);
    }


public function guruBelumPresensi()
    {
        $hariIni = now()->toDateString();

        // [POIN 1 FIX] Hanya ambil ID User yang SUDAH PRESENSI HADIR (Hadir, Masuk, Selesai)
        // Jadi user yang berstatus 'alpha' di tabel presensi TIDAK TERMASUK di sini (alias tetap dianggap BELUM PRESENSI)
        $userSudahHadir = Presensi::whereDate('tanggal', $hariIni)
            ->whereIn('status', ['hadir', 'masuk', 'selesai'])
            ->pluck('id_user')
            ->toArray();

        // Ambil ID User yang PUNYA PENGAJUAN DISETUJUI hari ini (Izin, Sakit, Cuti, Dinas Luar, WFH)
        $userPunyaPengajuan = Pengajuan::where('status', 'disetujui')
            ->whereDate('tanggal_mulai', '<=', $hariIni)
            ->whereDate('tanggal_selesai', '>=', $hariIni)
            ->pluck('id_user')
            ->toArray();

        // Gabungkan ID user yang dianggap "Sudah Beres" (Hadir Sah atau Ada Perizinan Sah)
        $excludedUserIds = array_unique(array_merge($userSudahHadir, $userPunyaPengajuan));

        // Ambil Guru, TK & Kepsek yang TIDAK ada di daftar "Sudah Beres"
        // (Berarti yang BELUM PRESENSI sama sekali + yang BERSTATUS ALFA di DB akan MASUK ke daftar ini)
        $guru = User::whereIn('role', ['guru', 'kepala_sekolah'])
            ->whereNotIn('id_user', $excludedUserIds)
            ->select('id_user', 'nama', 'username', 'role')
            ->get();

        return response()->json($guru);
    }

private function buildRekapQuery(Request $request)
{
    // 1. AMBIL PARAMETER RENTANG TANGGAL DULU (PENTING UNTUK CALENDER GENERATOR)
    $startDate = $request->start_date;
    $endDate = $request->end_date;

    if ($request->filled('semester')) {
        if ($request->semester == '2024-genap') { $startDate = '2025-01-01'; $endDate = '2025-06-30'; }
        elseif ($request->semester == '2025-ganjil') { $startDate = '2025-07-01'; $endDate = '2025-12-31'; }
        elseif ($request->semester == '2025-genap') { $startDate = '2026-01-01'; $endDate = '2026-06-30'; }
        elseif ($request->semester == '2026-ganjil') { $startDate = '2026-07-01'; $endDate = '2026-12-31'; }
    }
    

    // 2. KUERI 1: TABEL PRESENSI FISIK
    $queryPresensi = DB::table('presensi')
        ->join('users', 'presensi.id_user', '=', 'users.id_user')
        ->select(
            'presensi.id_user',
            'presensi.tanggal',
            'presensi.jam_masuk',
            'presensi.jam_keluar',
            'presensi.status',
            'presensi.status_keterlambatan',
            'presensi.keterangan',
            'presensi.updated_at',
            'users.nama',
            'users.role',
            'users.jenis_gtk'
        );


    // 3. KUERI 2: TABEL PENGAJUAN PERIZINAN (IZIN, SAKIT, CUTI)
    $queryPengajuan = DB::table('pengajuan')
        ->join('users', 'pengajuan.id_user', '=', 'users.id_user')
        ->whereNotIn('pengajuan.jenis_pengajuan', ['dinas_luar', 'wfh'])
        ->where('pengajuan.status', 'disetujui')
        ->select(
            'pengajuan.id_user',
            'pengajuan.tanggal_mulai as tanggal',
            DB::raw("'--:--' as jam_masuk"),
            DB::raw("'--:--' as jam_keluar"),
            'pengajuan.jenis_pengajuan as status',
            DB::raw("'' as status_keterlambatan"),
            'pengajuan.alasan as keterangan',
            'pengajuan.updated_at',
            'users.nama',
            'users.role',
            'users.jenis_gtk'
        );

    // A. Filter Pencarian Nama
    if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $queryPresensi->where('users.nama', 'like', $search);
            $queryPengajuan->where('users.nama', 'like', $search);
        }

    // B. Filter Jenis GTK (Guru / Tenaga Kependidikan)
    if ($request->filled('role')) {
            $roleFilter = strtolower($request->role);
            if ($roleFilter == 'guru') {
                $queryPresensi->where('users.role', 'guru')->where('users.jenis_gtk', 'Guru');
                $queryPengajuan->where('users.role', 'guru')->where('users.jenis_gtk', 'Guru');
            } elseif ($roleFilter == 'tenaga') {
                $queryPresensi->where('users.role', 'guru')->where('users.jenis_gtk', 'Tenaga Kependidikan');
                $queryPengajuan->where('users.role', 'guru')->where('users.jenis_gtk', 'Tenaga Kependidikan');
            } elseif ($roleFilter == 'kepala_sekolah') {
                $queryPresensi->where('users.role', 'kepala_sekolah');
                $queryPengajuan->where('users.role', 'kepala_sekolah');
            } else {
                $queryPresensi->where('users.role', $request->role);
                $queryPengajuan->where('users.role', $request->role);
            }
        }

    // C. Filter Berdasarkan Status Kehadiran
        if ($request->filled('status')) {
            $st = strtolower($request->status);
            $queryPresensi->where(DB::raw('LOWER(presensi.status)'), '=', $st);
            $queryPengajuan->where(DB::raw('LOWER(pengajuan.jenis_pengajuan)'), '=', $st);
        }

    // D. Filter Keterlambatan (Hanya berlaku di kueri fisik)
    if ($request->filled('keterlambatan')) {
            $kt = strtolower($request->keterlambatan);
            $queryPresensi->where(DB::raw('LOWER(presensi.status_keterlambatan)'), '=', $kt);
            $queryPengajuan->whereRaw('0 = 1'); // Keterlambatan tidak ada di pengajuan
        }
    
    // E. Filter Rentang Waktu / Tanggal Utama
    if ($request->filled('start_date') && $request->filled('end_date')) {
        $queryPresensi->whereBetween('presensi.tanggal', [$request->start_date, $request->end_date]);
        $queryPengajuan->whereBetween('pengajuan.tanggal_mulai', [$request->start_date, $request->end_date]);
    } elseif ($request->filled('semester')) {
        if ($request->semester == '2024-genap') { $range = ['2025-01-01', '2025-06-30']; }
        elseif ($request->semester == '2025-ganjil') { $range = ['2025-07-01', '2025-12-31']; }
        elseif ($request->semester == '2025-genap') { $range = ['2026-01-01', '2026-06-30']; }
        elseif ($request->semester == '2026-ganjil') { $range = ['2026-07-01', '2026-12-31']; }

        if (isset($range)) {
            $queryPresensi->whereBetween('presensi.tanggal', $range);
            $queryPengajuan->whereBetween('pengajuan.tanggal_mulai', $range);
        }
    }


    // 6. GABUNGKAN KETIGANYA MENGGUNAKAN UNION ALL
    return $queryPresensi
        ->unionAll($queryPengajuan);
        
}

/**
     * API JSON List untuk ListView Flutter
     */
    public function rekapPresensiApi(Request $request)
    {
        try {
            // 1. Ambil query data hasil union dari database
            $query = $this->buildRekapQuery($request);
            $rawPresensis = $query->orderBy('updated_at', 'desc')->get();

            // 2. Map data secara eksplisit ke bentuk Array murni
            $presensis = $rawPresensis->map(function ($item) {
                // Cari data user
                $user = \App\Models\User::find($item->id_user);
                
                // Set default url dokumentasi null
                $urlDokumentasi = null;

                // Ambil status (antisipasi jika data objek berupa huruf besar/kecil)
                $statusData = strtolower($item->status);

                // Jika statusnya adalah dinas luar, cari datanya ke tabel pengajuan
               if (in_array($statusData, ['dinas_luar', 'dinas luar', 'wfh'])) {
                
                $pengajuan = \App\Models\Pengajuan::where('id_user', $item->id_user)
                    ->whereDate('tanggal_mulai', '<=', $item->tanggal)
                    ->whereDate('tanggal_selesai', '>=', $item->tanggal)
                    ->where(function($q) use ($statusData) {
                        // Jika status presensinya seputar dinas luar
                        if ($statusData == 'dinas_luar' || $statusData == 'dinas luar') {
                            $q->where('jenis_pengajuan', 'Dinas Luar')
                              ->orWhere('jenis_pengajuan', 'dinas_luar');
                        } 
                        // Jika status presensinya wfh
                        elseif ($statusData == 'wfh') {
                            $q->where('jenis_pengajuan', 'WFH')
                              ->orWhere('jenis_pengajuan', 'wfh')
                              ->orWhere('jenis_pengajuan', 'Wfh');
                        }
                    })
                    ->where('status', 'disetujui')
                    ->first();

                // Ambil gambar jika data pengajuan ditemukan
                if ($pengajuan) {
                    // Antisipasi: jika foto_dokumentasi kosong tetapi diunggah di bukti_file
                    $namaFileGambar = $pengajuan->foto_dokumentasi ?? $pengajuan->bukti_file;
                    $folder = $pengajuan->foto_dokumentasi ? 'dokumentasi' : 'pengajuan';

                    if ($namaFileGambar) {
                        $urlDokumentasi = asset('uploads/' . $folder . '/' . $namaFileGambar);
                    }
                }
            }

                // WAJIB RETURN BENTUK ARRAY MURNI SEPERTI INI AGAR DIJAMIN MASUK KE JSON
                return [
                    'id_user'              => $item->id_user,
                    'tanggal'              => $item->tanggal,
                    'jam_masuk'            => $item->jam_masuk,
                    'jam_keluar'           => $item->jam_keluar,
                    'status'               => $item->status,
                    'status_keterlambatan' => $item->status_keterlambatan ?? '',
                    'url_dokumentasi'      => $urlDokumentasi, // Ini nama key di Laravel
                    'user' => $user ? [
                        'nama'           => $user->nama,
                        'jenis_gtk'      => $user->jenis_gtk,
                        'mata_pelajaran' => $user->mata_pelajaran
                    ] : ['nama' => 'User Terhapus', 'jenis_gtk' => '-', 'mata_pelajaran' => '-']
                ];
            });

            // Kembalikan hasil array murni ke JSON Flutter
            return response()->json($presensis, 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    
    /**
     * API 2: Berfungsi mencetak & mengunduh PDF rekap sesuai filter aktif Flutter
     */
public function cetakPdfApi(Request $request)
    {
        $query = $this->buildRekapQuery($request);
        $rawPresensis = $query->orderBy('updated_at', 'desc')->get();

        $presensis = $rawPresensis->map(function ($item) {
            $item->user = User::find($item->id_user);
            $item->pengajuan = Pengajuan::where('id_user', $item->id_user)
                ->whereDate('tanggal_mulai', '<=', $item->tanggal)
                ->whereDate('tanggal_selesai', '>=', $item->tanggal)
                ->where('jenis_pengajuan', $item->status)
                ->where('status', 'disetujui')
                ->first();
            return $item;
        });

        $pdf = Pdf::loadView('admin.pages.rekap_presensi.pdf', compact('presensis'))
                  ->setPaper('a4', 'landscape');

        return $pdf->download('Rekap_Presensi_Kepsek_' . date('d_m_Y') . '.pdf');
    }


}