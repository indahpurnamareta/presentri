<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Presensi;
use App\Models\Pengajuan;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Exports\PresensiExport;
use Maatwebsite\Excel\Facades\Excel;

class RekapPresensiController extends Controller
{
    /**
     * Fungsi Helper untuk menyatukan data Presensi riil dan Pengajuan Perizinan via SQL UNION
     */
    private function buildRekapQuery(Request $request)
    {
        // --- PENENTUAN RENTANG TANGGAL UTAMA ---
        $tanggalAwal = $request->tanggal_awal;
        $tanggalAkhir = $request->tanggal_akhir;

        if ($request->semester) {
            [$tahunMulai, $tipeSemester] = explode('-', $request->semester);
            $tahunMulai = (int)$tahunMulai;

            if ($tipeSemester === 'ganjil') {
                $tanggalAwal = $tahunMulai . '-07-01';
                $tanggalAkhir = $tahunMulai . '-12-31';
            } else {
                $tahunBesok = $tahunMulai + 1;
                $tanggalAwal = $tahunBesok . '-01-01';
                $tanggalAkhir = $tahunBesok . '-06-30';
            }
        }

        // 1. Query Utama dari tabel Presensi (Sekarang SUDAH termasuk data 'alpha' riil dari sistem otomatis)
        $queryPresensi = DB::table('presensi')
            ->select(
                'id_user',
                'tanggal',
                'jam_masuk',
                'jam_keluar',
                'status', 
                'status_keterlambatan',
                'keterangan',
                'updated_at'
            );

        // 2. Query Kedua dari tabel Pengajuan (Untuk Izin, Sakit, Cuti resmi)
        $queryPengajuan = DB::table('pengajuan')
            ->whereNotIn('jenis_pengajuan', ['dinas_luar', 'wfh'])
            ->where('status', 'disetujui') 
            ->select(
                'id_user',
                'tanggal_mulai as tanggal',
                DB::raw("'--:--' as jam_masuk"),
                DB::raw("'--:--' as jam_keluar"),
                'jenis_pengajuan as status',
                DB::raw("'-' as status_keterlambatan"),
                'alasan as keterangan',
                'updated_at'
            );

        // --- EKSEKUSI FILTER TANGGAL RIIL ---
        if ($tanggalAwal && $tanggalAkhir) {
            $queryPresensi->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir]);
            
            $queryPengajuan->where(function($q) use ($tanggalAwal, $tanggalAkhir) {
                $q->whereBetween('tanggal_mulai', [$tanggalAwal, $tanggalAkhir])
                  ->orWhereBetween('tanggal_selesai', [$tanggalAwal, $tanggalAkhir]);
            });
        }

        // --- FILTER NAMA & KATEGORI GTK ---
        $userQuery = DB::table('users');
        if ($request->search) {
            $userQuery->where('nama', 'like', '%' . $request->search . '%');
        }

        if ($request->role == 'guru') {
            $userQuery->where('role', 'guru')->where('jenis_gtk', 'Guru');
        } elseif ($request->role == 'tenaga') {
            $userQuery->where('role', 'guru')->where('jenis_gtk', 'Tenaga Kependidikan');
         } elseif ($request->role == 'kepala_sekolah') {
            $userQuery->where('role', 'kepala_sekolah');
        } elseif ($request->role) {
            $userQuery->where('role', $request->role);
        }

        if ($request->search || $request->role) {
            $userIds = $userQuery->pluck('id_user');
            $queryPresensi->whereIn('id_user', $userIds);
            $queryPengajuan->whereIn('id_user', $userIds);
        }

        // --- FILTER STATUS KEHADIRAN ---
        if ($request->status) {
            $st = strtolower($request->status);
            if ($st === 'alpha') {
                // Karena alpha sudah jadi data riil di tabel presensi, kita cukup ambil dari queryPresensi saja
                $queryPresensi->where('status', 'alpha');
                $queryPengajuan->whereRaw('0 = 1'); // Matikan query izin karena tidak dicari
            } else {
                $queryPresensi->where('status', $request->status);
                $queryPengajuan->where('jenis_pengajuan', $request->status);
            }
        }

        // --- FILTER KETERLAMBATAN ---
        if ($request->keterlambatan) {
            $queryPresensi->where('status_keterlambatan', $request->keterlambatan);
            $queryPengajuan->whereRaw('0 = 1');
        }

        // --- GABUNGKAN SEGMEN DENGAN UNION ALL (Sekaruh hanya 2 query, jauh lebih cepat!) ---
        $combinedQuery = $queryPresensi->unionAll($queryPengajuan);

        return DB::table(DB::raw("({$combinedQuery->toSql()}) as rekap"))
            ->mergeBindings($combinedQuery);
    }

    public function index(Request $request)
    {
        $query = $this->buildRekapQuery($request);
        
        // Ambil data dengan Pagination teratur (10 data per halaman agar rapi saat sidang)
        $rawPresensis = $query->orderBy('updated_at', 'desc')->paginate(10);

        // Transformasikan hasil agar relasi model User tetap bisa dipanggil di Blade via $presensi->user
        $presensis = $rawPresensis->through(function ($item) {
            $item->user = User::find($item->id_user);

            $item->pengajuan = Pengajuan::where('id_user', $item->id_user)
                ->whereDate('tanggal_mulai', '<=', $item->tanggal)
                ->whereDate('tanggal_selesai', '>=', $item->tanggal)
                ->where('jenis_pengajuan', $item->status)
                ->where('status', 'disetujui')
                ->first();

            return $item;
        });

        return view('admin.pages.rekap_presensi.index', compact('presensis'));
    }

    public function downloadPdf(Request $request)
    {
        $query = $this->buildRekapQuery($request);
        
        // Tarik semua data tanpa pagination agar masuk semuanya ke halaman PDF
        $rawPresensis = $query->orderBy('tanggal', 'desc')->get();

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

        $pdf = Pdf::loadView('admin.pages.rekap_presensi.pdf', [
            'presensis' => $presensis,
            'request' => $request
        ])->setPaper('a4', 'landscape');

        return $pdf->download('Rekap_Presensi_Guru_' . date('Y-m-d') . '.pdf');
    }

    public function downloadExcel(Request $request)
    {
        return Excel::download(new PresensiExport($request), 'Rekap_Presensi_Guru.xlsx');
    }
}