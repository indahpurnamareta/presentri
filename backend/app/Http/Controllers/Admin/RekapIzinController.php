<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pengajuan;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use App\Models\Notifikasi; 
use App\Traits\FcmNotificationTrait;
use App\Exports\IzinExport;
use Maatwebsite\Excel\Facades\Excel;

class RekapIzinController extends Controller
{
    use FcmNotificationTrait;
    public function index(Request $request)
    {
    $query = Pengajuan::with('user');

    // Hanya tampilkan izin, sakit, cuti
    $query->whereIn('jenis_pengajuan', [
        'izin',
        'sakit',
        'cuti'
    ]);

    // 1. FILTER NAMA PEGAWAI 
   
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
        'admin.pages.rekap_izin.index',
        compact('pengajuans', 'gurus')
    );
}

// ENTRI IZIN
public function store(Request $request)
    {
        $request->validate([
            'id_user' => 'required|exists:users,id_user',
            'jenis_pengajuan' => 'required|in:izin,sakit,cuti',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'alasan' => 'required|string|max:500',
            'bukti_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:20480'
        ]);

        $namaFileSurat = null;
            // PATH ADJUSTMENT UNTUK HOSTING CPANEL
           
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
            'bukti_file' => $namaFileSurat, 
            'status' => 'disetujui', 
            'diverifikasi_oleh' => $adminId,
            'catatan_verifikasi' => 'Diinput langsung oleh Tim Kurikulum'
        ]);
        
         // ==================== LOGIKA NOTIFIKASI BARU ====================
        $user = User::find($request->id_user);
        $namaPegawai = $user ? $user->nama : 'Pegawai';
        $jenisTeks = ucfirst($request->jenis_pengajuan); // Membuat huruf depan jadi kapital (contoh: Izin, Sakit, Cuti)
        
        $kepsek = User::where('role', 'kepala_sekolah')->first();
        
        if ($kepsek) {
            // Format pesan: "Tim Kurikulum menambahkan data Izin : Ahmad Dahlan"
            $pesanNotif = 'Tim Kurikulum menambahkan data ' . $jenisTeks . ' : ' . $namaPegawai;
        
            // A. SIMPAN KE DATABASE
            \App\Models\Notifikasi::create([
                'id_user' => $kepsek->id_user,
                'judul' => 'Penambahan Data Perizinan',
                'pesan' => $pesanNotif,
                'dibaca' => 'belum',
            ]);
        
            // B. KIRIM PUSH NOTIFICATION FCM REALTIME KE HP KEPSEK
            if (method_exists($this, 'sendFcmNotification')) {
                $idPengajuan = $pengajuan->id_pengajuan ?? $pengajuan->id;
        
                $this->sendFcmNotification(
                    $kepsek->id_user, 
                    'Penambahan Data Perizinan', 
                    $pesanNotif,
                    [
                        'id_pengajuan' => (string) $idPengajuan, 
                        'type' => 'approval_pengajuan'
                    ]
                );
            }
        }
        // ================================================================

        return redirect()->back()->with('success', 'Data perizinan berhasil ditambahkan.');
    }

   // 2. UPDATE IZIN
public function update(Request $request, $id)
{
    $pengajuan = Pengajuan::findOrFail($id);

    $request->validate([
        'jenis_pengajuan' => 'required|in:izin,sakit,cuti',
        'tanggal_mulai' => 'required|date',
        'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        'alasan' => 'required|string|max:500',
        'status' => 'required|in:pending,disetujui,ditolak',
        'bukti_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:20480'
    ]);

    $data = [
        'jenis_pengajuan' => $request->jenis_pengajuan,
        'tanggal_mulai' => $request->tanggal_mulai,
        'tanggal_selesai' => $request->tanggal_selesai,
        'alasan' => $request->alasan,
        'status' => $request->status,
    ];

    if ($request->hasFile('bukti_file')) {
        $publicHtmlPath = base_path('../');
        
        // Hapus file lama jika ada
        if ($pengajuan->bukti_file && file_exists($publicHtmlPath . '/uploads/pengajuan/' . $pengajuan->bukti_file)) {
            @unlink($publicHtmlPath . '/uploads/pengajuan/' . $pengajuan->bukti_file);
        }

        $file = $request->file('bukti_file');
        $namaFile = time() . '_' . $file->getClientOriginalName();
        $file->move($publicHtmlPath . '/uploads/pengajuan', $namaFile);
        $data['bukti_file'] = $namaFile;
    }

    $pengajuan->update($data);

    // ==================== LOGIKA NOTIFIKASI UPDATE KEPSEK ====================
    $user = $pengajuan->user;
    $namaPegawai = $user ? $user->nama : 'Pegawai';
    $jenisTeks = ucfirst($request->jenis_pengajuan);

    $kepsek = User::where('role', 'kepala_sekolah')->first();

    if ($kepsek) {
        $pesanNotif = 'Tim Kurikulum memperbarui data ' . $jenisTeks . ' : ' . $namaPegawai;

        // A. SIMPAN KE DATABASE LOKAL
        \App\Models\Notifikasi::create([
            'id_user' => $kepsek->id_user,
            'judul' => 'Pembaruan Data Perizinan',
            'pesan' => $pesanNotif,
            'dibaca' => 'belum',
        ]);

        // B. KIRIM PUSH NOTIFICATION FCM REALTIME
        if (method_exists($this, 'sendFcmNotification')) {
            $idPengajuan = $pengajuan->id_pengajuan ?? $pengajuan->id;

            $this->sendFcmNotification(
                $kepsek->id_user, 
                'Pembaruan Data Perizinan', 
                $pesanNotif,
                [
                    'id_pengajuan' => (string) $idPengajuan, 
                    'type' => 'approval_pengajuan'
                ]
            );
        }
    }
    // ================================================================

    return redirect()->back()->with('success', 'Data perizinan berhasil diperbarui.');
}

// 3. HAPUS IZIN
public function destroy($id)
{
    $pengajuan = Pengajuan::findOrFail($id);
    
    // Simpan info data sebelum dihapus untuk teks notifikasi
    $user = $pengajuan->user;
    $namaPegawai = $user ? $user->nama : 'Pegawai';
    $jenisTeks = ucfirst($pengajuan->jenis_pengajuan);
    
    // Hapus file fisik jika ada
    if ($pengajuan->bukti_file) {
        $publicHtmlPath = base_path('../');
        if (file_exists($publicHtmlPath . '/uploads/pengajuan/' . $pengajuan->bukti_file)) {
            @unlink($publicHtmlPath . '/uploads/pengajuan/' . $pengajuan->bukti_file);
        }
    }

    $pengajuan->delete();

    // ==================== LOGIKA NOTIFIKASI DESTROY KEPSEK ====================
    $kepsek = User::where('role', 'kepala_sekolah')->first();

    if ($kepsek) {
        $pesanNotif = 'Tim Kurikulum menghapus data ' . $jenisTeks . ' : ' . $namaPegawai;

        // A. SIMPAN KE DATABASE LOKAL
        \App\Models\Notifikasi::create([
            'id_user' => $kepsek->id_user,
            'judul' => 'Penghapusan Data Perizinan',
            'pesan' => $pesanNotif,
            'dibaca' => 'belum',
        ]);

        // B. KIRIM PUSH NOTIFICATION FCM REALTIME
        if (method_exists($this, 'sendFcmNotification')) {
            $this->sendFcmNotification(
                $kepsek->id_user, 
                'Penghapusan Data Perizinan', 
                $pesanNotif,
                [
                    'type' => 'approval_pengajuan'
                ]
            );
        }
    }
    // ================================================================

    return redirect()->back()->with('success', 'Data perizinan berhasil dihapus.');
}
    public function downloadPdf(Request $request)
    {
        $query = Pengajuan::with('user');
        $query->whereIn('jenis_pengajuan', [
        'izin',
        'sakit',
        'cuti'
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

        $pdf = Pdf::loadView('admin.pages.rekap_izin.pdf', compact('izins', 'request'))
                  ->setPaper('a4', 'landscape');

        return $pdf->download('Rekap_Izin_Guru_' . date('Y-m-d') . '.pdf');
    }

    public function downloadExcel(Request $request)
{
    return Excel::download(new IzinExport($request), 'Rekap_Izin_Sakit_Cuti.xlsx');
}
}