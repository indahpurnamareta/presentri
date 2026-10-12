<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pengajuan;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use App\Exports\DinasLuarExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\File;
use App\Models\Notifikasi; 
use App\Traits\FcmNotificationTrait;

class PresensiKhususController extends Controller
{
    use FcmNotificationTrait;
    
    public function index(Request $request)
{
    $query = Pengajuan::with('user');

    // Hanya tampilkan WFH & Dinas Luar
    $query->whereIn('jenis_pengajuan', [
        'wfh',
        'dinas_luar'
    ]);

// 1. FILTER NAMA PEGAWAI (INTEGRASI DENGAN KATEGORI PEGAWAI)
// Kita buat logic whereHas ke tabel users untuk menangani search, role, dan jenis_gtk sekaligus
    if ($request->search || $request->role) {
        $query->whereHas('user', function ($q) use ($request) {
            // Jika ada pencarian nama
            if ($request->search) {
                $q->where('nama', 'like', '%' . $request->search . '%');
            }

            // Jika ada filter kategori pegawai (Logika Guru & Tenaga Kependidikan)
            if ($request->role == 'guru') {
                $q->where('role', 'guru')->where('jenis_gtk', 'Guru');
            } 
            elseif ($request->role == 'tenaga') {
                $q->where('role', 'guru')->where('jenis_gtk', 'Tenaga Kependidikan');
            }
            elseif ($request->role == 'kepala_sekolah') {
                $q->where('role', 'kepala_sekolah');
            }
            elseif ($request->role) {
                $q->where('role', $request->role);
            }
        });
    }

    // 2. FILTER STATUS PERIZINAN (disetujui, ditolak, pending)
    if ($request->status) {
        $query->where('status', $request->status);
    }

    // 3. FILTER JENIS PENGAJUAN (izin, sakit, cuti, dll)
    if ($request->jenis) {
        $query->where('jenis_pengajuan', $request->jenis);
    }

    // 4. OTOMATISASI FILTER SEMESTER (BERDASARKAN TANGGAL AKADEMIK)
    $tanggalAwal = $request->tanggal_awal;
    $tanggalAkhir = $request->tanggal_akhir;

    if ($request->semester) {
        // Format value: "2025-ganjil" atau "2026-genap"
        [$tahunMulai, $tipeSemester] = explode('-', $request->semester);
        $tahunMulai = (int)$tahunMulai;

        if ($tipeSemester === 'ganjil') {
            // Ganjil: Juli s/d Desember
            $tanggalAwal = $tahunMulai . '-07-01';
            $tanggalAkhir = $tahunMulai . '-12-31';
        } else {
            // Genap: Januari s/d Juni tahun berikutnya
            $tahunBesok = $tahunMulai + 1;
            $tanggalAwal = $tahunBesok . '-01-01';
            $tanggalAkhir = $tahunBesok . '-06-30';
        }
    }

    // 5. EKSEKUSI FILTER TANGGAL (BAIK DARI MANUAL ATAU DROP-DOWN SEMESTER)
    if ($tanggalAwal && $tanggalAkhir) {
        $query->where(function($q) use ($tanggalAwal, $tanggalAkhir) {
            $q->whereBetween('tanggal_mulai', [$tanggalAwal, $tanggalAkhir])
              ->orWhereBetween('tanggal_selesai', [$tanggalAwal, $tanggalAkhir]);
        });
    }

    // PAGINATION
    $pengajuans = $query
        ->orderByDesc('updated_at')
        ->paginate(10)
        ->withQueryString();

   $gurus = User::whereIn('role', ['guru', 'kepala_sekolah'])
    ->orderBy('nama')
    ->get();

    return view(
        'admin.pages.presensi_khusus.index',
        compact('pengajuans', 'gurus')
    );
}

// FUNGSI BARU: Entri Tugas Dinas Luar & WFH oleh Tim Kurikulum
public function store(Request $request)
    {
        $request->validate([
            'id_user' => 'required|exists:users,id_user',
            'jenis_pengajuan' => 'required|in:wfh,dinas_luar',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'alasan' => 'required|string|max:500',
            'bukti_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:20480'
        ]);

        $namaFileSurat = null;
            // PATH ADJUSTMENT UNTUK HOSTING CPANEL
            // base_path() mengarah ke folder 'presentri'. Menggunakan '../' akan membuatnya keluar satu tingkat ke 'public_html'
            $publicHtmlPath = base_path('../');

            // 1. PROSES UPLOAD BUKTI FILE (DOKUMEN LAMPIRAN)
            if ($request->hasFile('bukti_file')) {
                $file = $request->file('bukti_file');
                $namaFileSurat = time().'_'.$file->getClientOriginalName();
                
                // Menyimpan langsung ke public_html/uploads/pengajuan
                $file->move(
                    $publicHtmlPath . '/uploads/pengajuan',
                    $namaFileSurat
                );
            }

        // Ambil ID admin/kurikulum yang sedang login untuk track verifikator
        $adminId = Auth::id(); 

         $pengajuan = Pengajuan::create([
            'id_user' => $request->id_user,
            'jenis_pengajuan' => $request->jenis_pengajuan,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'alasan' => $request->alasan,
            'surat_file' => null, 
            'bukti_file' => $namaFileSurat, // Diisi file surat tugas jika dinas luar
            'status' => 'disetujui', // LANGSUNG DISETUJUI
            'diverifikasi_oleh' => $adminId,
            'catatan_verifikasi' => 'Diinput langsung oleh Tim Kurikulum'
        ]);
        
        // ==================== LOGIKA NOTIFIKASI STORE KEPSEK ====================
        $user = User::find($request->id_user);
        $namaPegawai = $user ? $user->nama : 'Pegawai';
        // Rapikan format Teks Dinas Luar / WFH
        $jenisTeks = str_replace('_', ' ', $request->jenis_pengajuan);
        $jenisTeks = ucwords($jenisTeks); // "Dinas Luar" atau "Wfh"
        
        $kepsek = User::where('role', 'kepala_sekolah')->first();
        
        if ($kepsek) {
            $pesanNotif = 'Tim Kurikulum menambahkan data ' . $jenisTeks . ' : ' . $namaPegawai;
        
            \App\Models\Notifikasi::create([
                'id_user' => $kepsek->id_user,
                'judul' => 'Penambahan Presensi Khusus',
                'pesan' => $pesanNotif,
                'dibaca' => 'belum',
            ]);
        
            if (method_exists($this, 'sendFcmNotification')) {
                $idPengajuan = $pengajuan->id_pengajuan ?? $pengajuan->id;
        
                $this->sendFcmNotification(
                    $kepsek->id_user, 
                    'Penambahan Presensi Khusus', 
                    $pesanNotif,
                    [
                        'id_pengajuan' => (string) $idPengajuan, 
                        'type' => 'approval_pengajuan'
                    ]
                );
            }
        }
        // ================================================================

        return redirect()->back()->with('success', 'Penugasan Guru berhasil diaktifkan sistem.');
    }
    
public function update(Request $request, $id)
{
        $pengajuan = Pengajuan::findOrFail($id);

        $request->validate([
            'id_user' => 'required|exists:users,id_user',
            'jenis_pengajuan' => 'required|in:wfh,dinas_luar',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'alasan' => 'required|string|max:500',
            'status' => 'required|in:pending,disetujui,ditolak',
            'bukti_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:20480'
        ]);

        $publicHtmlPath = base_path('../');
        $namaFileSurat = $pengajuan->bukti_file;

        // Jika upload file bukti baru
        if ($request->hasFile('bukti_file')) {
            // Hapus file lama jika ada
            if ($pengajuan->bukti_file && File::exists($publicHtmlPath . '/uploads/pengajuan/' . $pengajuan->bukti_file)) {
                File::delete($publicHtmlPath . '/uploads/pengajuan/' . $pengajuan->bukti_file);
            }

            $file = $request->file('bukti_file');
            $namaFileSurat = time() . '_' . $file->getClientOriginalName();
            $file->move($publicHtmlPath . '/uploads/pengajuan', $namaFileSurat);
        }

        $pengajuan->update([
            'id_user' => $request->id_user,
            'jenis_pengajuan' => $request->jenis_pengajuan,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'alasan' => $request->alasan,
            'status' => $request->status,
            'bukti_file' => $namaFileSurat,
            'diverifikasi_oleh' => Auth::id(),
            'catatan_verifikasi' => 'Diubah oleh Admin/Kurikulum'
        ]);
        
        // ==================== LOGIKA NOTIFIKASI UPDATE KEPSEK ====================
        $user = $pengajuan->user;
        $namaPegawai = $user ? $user->nama : 'Pegawai';
        $jenisTeks = ucwords(str_replace('_', ' ', $request->jenis_pengajuan));

        $kepsek = User::where('role', 'kepala_sekolah')->first();

        if ($kepsek) {
            $pesanNotif = 'Tim Kurikulum memperbarui data ' . $jenisTeks . ' : ' . $namaPegawai;

            \App\Models\Notifikasi::create([
                'id_user' => $kepsek->id_user,
                'judul' => 'Pembaruan Presensi Khusus',
                'pesan' => $pesanNotif,
                'dibaca' => 'belum',
            ]);

            if (method_exists($this, 'sendFcmNotification')) {
                $idPengajuan = $pengajuan->id_pengajuan ?? $pengajuan->id;

                $this->sendFcmNotification(
                    $kepsek->id_user, 
                    'Pembaruan Presensi Khusus', 
                    $pesanNotif,
                    [
                        'id_pengajuan' => (string) $idPengajuan, 
                        'type' => 'approval_pengajuan'
                    ]
                );
            }
        }
        // ================================================================

        return redirect()->back()->with('success', 'Data penugasan khusus berhasil diperbarui.');
    }
    
    public function destroy($id)
    {
        $pengajuan = Pengajuan::findOrFail($id);
        $publicHtmlPath = base_path('../');
        $user = $pengajuan->user;
        $namaPegawai = $user ? $user->nama : 'GTK';
        $jenisTeks = ucwords(str_replace('_', ' ', $pengajuan->jenis_pengajuan));

        // Hapus file bukti lampiran jika ada di folder uploads
        if ($pengajuan->bukti_file && File::exists($publicHtmlPath . '/uploads/pengajuan/' . $pengajuan->bukti_file)) {
            File::delete($publicHtmlPath . '/uploads/pengajuan/' . $pengajuan->bukti_file);
        }

        // Hapus file foto dokumentasi dari HP guru jika ada
        if ($pengajuan->foto_dokumentasi && File::exists($publicHtmlPath . '/uploads/dokumentasi/' . $pengajuan->foto_dokumentasi)) {
            File::delete($publicHtmlPath . '/uploads/dokumentasi/' . $pengajuan->foto_dokumentasi);
        }

        $pengajuan->delete();
      // ==================== LOGIKA NOTIFIKASI DESTROY KEPSEK ====================
        $kepsek = User::where('role', 'kepala_sekolah')->first();
    
        if ($kepsek) {
            $pesanNotif = 'Tim Kurikulum menghapus data ' . $jenisTeks . ' : ' . $namaPegawai;
    
            \App\Models\Notifikasi::create([
                'id_user' => $kepsek->id_user,
                'judul' => 'Penghapusan Presensi Khusus',
                'pesan' => $pesanNotif,
                'dibaca' => 'belum',
            ]);
    
            if (method_exists($this, 'sendFcmNotification')) {
                $this->sendFcmNotification(
                    $kepsek->id_user, 
                    'Penghapusan Presensi Khusus', 
                    $pesanNotif,
                    [
                        'type' => 'approval_pengajuan'
                    ]
                );
            }
        }
        // ================================================================

        return redirect()->back()->with('success', 'Data penugasan khusus berhasil dihapus.');
    }

    public function downloadPdf(Request $request)
    {
        $query = Pengajuan::with('user'); 
        $query->whereIn('jenis_pengajuan', [
        'wfh',
        'dinas_luar'
         ]);

        if ($request->search) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->jenis) {
            // Disesuaikan dengan nama kolom asli: jenis_pengajuan
            $query->where('jenis_pengajuan', $request->jenis);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->tanggal_awal && $request->tanggal_akhir) {
            $query->where(function($q) use ($request) {
                $q->whereBetween('tanggal_mulai', [$request->tanggal_awal, $request->tanggal_akhir])
                  ->orWhereBetween('tanggal_selesai', [$request->tanggal_awal, $request->tanggal_akhir]);
            });
        }

        $izins = $query->latest()->get();

        $pdf = Pdf::loadView('admin.pages.presensi_khusus.pdf', compact('izins', 'request'))
                  ->setPaper('a4', 'landscape');

        return $pdf->download('Dinas_luar_WFH_' . date('Y-m-d') . '.pdf');
    }


    public function downloadExcel(Request $request)
{
    return Excel::download(new DinasLuarExport($request), 'Rekap_Dinas_Luar_WFH.xlsx');
}
}