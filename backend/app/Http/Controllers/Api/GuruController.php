<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Presensi;
use App\Models\Pengajuan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class GuruController extends Controller
{
    // AMBIL SEMUA DATA GURU
    public function index()
    {
        // 1. Ambil semua data user dengan role guru
        $guru = User::where('role', 'guru')->get();

        // 2. Kembalikan data dalam bentuk JSON
        return response()->json($guru);
    }

    // AMBIL DETAIL DATA & REKAP STATUS GURU
    public function detail($id)
    {
        // 1. Cari data spesifik guru berdasarkan ID
        $guru = User::find($id);

        // 2. Hitung total kehadiran (status hadir)
        $hadir = Presensi::where('id_user', $id)
            ->where('status', 'hadir')
            ->count();

        // 3. Hitung total keterlambatan
        $terlambat = Presensi::where('id_user', $id)
            ->where('status_keterlambatan', 'terlambat')
            ->count();

        // 4. Hitung total izin yang telah disetujui
        $izin = Pengajuan::where('id_user', $id)
            ->where('jenis_pengajuan', 'Izin')
            ->where('status', 'disetujui')
            ->count();

        // 5. Hitung total sakit yang telah disetujui
        $sakit = Pengajuan::where('id_user', $id)
            ->where('jenis_pengajuan', 'Sakit')
            ->where('status', 'disetujui')
            ->count();

        // 6. Ambil 10 riwayat presensi terbaru
        $riwayatPresensi = Presensi::where('id_user', $id)
            ->latest()
            ->take(10)
            ->get();

        // 7. Ambil 10 riwayat pengajuan izin/sakit terbaru
        $riwayatPengajuan = Pengajuan::where('id_user', $id)
            ->latest()
            ->take(10)
            ->get();

        // 8. Kembalikan semua data rekap dalam bentuk JSON
        return response()->json([
            'id_user' => $guru->id_user,
            'nama' => $guru->nama,
            'username' => $guru->username,
            'nip' => $guru->nip,
            'mata_pelajaran' => $guru->mata_pelajaran,
            'no_hp' => $guru->no_hp,
            'hadir' => $hadir,
            'terlambat' => $terlambat,
            'izin' => $izin,
            'sakit' => $sakit,
            'presensi' => $riwayatPresensi,
            'pengajuan' => $riwayatPengajuan,
        ]);
    }

      // CETAK REKAP PDF DATA GTK VIA API
    public function cetakPdfGtkApi(Request $request)
    {
        // 1. Buat query dasar untuk user ber-role guru
        $query = User::where('role', 'guru');

        // 2. Filter pencarian nama jika parameter 'search' diisi
        if ($request->filled('search')) {
            $query->where('nama', 'like', '%' . $request->search . '%');
        }

        // 3. Filter berdasarkan 'jenis_gtk' jika disaring spesifik (bukan 'Semua')
        if ($request->filled('jenis_gtk') && $request->jenis_gtk != 'Semua') {
            $query->where('jenis_gtk', $request->jenis_gtk);
        }

        // 4. Ambil data akhir diurutkan alfabetis berdasarkan nama
        $users = $query->orderBy('nama', 'asc')->get();

        // 5. Render data ke halaman blade PDF dengan orientasi landscape A4
        $pdf = Pdf::loadView('admin.pages.user.pdf', compact('users'))
            ->setPaper('a4', 'landscape');

        // 6. Unduh file PDF secara langsung
        return $pdf->download('Data_Guru_dan_Tenaga_Kependidikan_' . date('d_m_Y') . '.pdf');
    }
}