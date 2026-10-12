<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pengajuan;
use App\Models\User;        
use App\Models\Notifikasi; 
use Illuminate\Http\Request;
use App\Traits\FcmNotificationTrait;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Presensi;

class PengajuanController extends Controller
{
    use FcmNotificationTrait;

    public function store(Request $request)
    {
        try {
            $namaFile = null;
            $namaFotoDokumentasi = null; 
            $publicHtmlPath = base_path('../');
            
            $sudahPresensi = \App\Models\Presensi::where('id_user', $request->id_user)
            ->whereDate('tanggal', $request->tanggal_mulai) 
            ->exists();

            if ($sudahPresensi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah presensi masuk hari ini.',
                ], 400);
            }
        
            // Upload bukti file
            if ($request->hasFile('bukti_file')) {
                $file = $request->file('bukti_file');
                $namaFile = time().'_'.$file->getClientOriginalName();
            
                $folderPengajuan = $publicHtmlPath . '/uploads/pengajuan';
            
                if (!file_exists($folderPengajuan)) {
                    mkdir($folderPengajuan, 0775, true);
                }
            
                $file->move($folderPengajuan, $namaFile);
            }

            // 2. PROSES UPLOAD FOTO KAMERA (DOKUMENTASI)
            if ($request->hasFile('foto_dokumentasi')) {
                $foto = $request->file('foto_dokumentasi');
                $namaFotoDokumentasi = time().'_dokumentasi_'.$foto->getClientOriginalName();
                $foto->move(
                    public_path('uploads/dokumentasi'), // Disimpan di folder terpisah agar rapi
                    $namaFotoDokumentasi
                );
            }

            // CEK APAKAH SUDAH ADA PENGAJUAN DI TANGGAL YANG SAMA
            $cek = Pengajuan::where('id_user', $request->id_user)
                ->whereDate('tanggal_mulai', $request->tanggal_mulai)
                ->whereIn('status', ['pending', 'disetujui'])
                ->exists();

            if ($cek) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah mengajukan perizinan pada tanggal tersebut.',
                ], 400);
            }

            // Simpan pengajuan ke database dengan field tambahan foto_dokumentasi
            $pengajuan = Pengajuan::create([
                'id_user' => $request->id_user,
                'jenis_pengajuan' => $request->jenis_pengajuan,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'alasan' => $request->alasan,
                'bukti_file' => $namaFile,
                'foto_dokumentasi' => $namaFotoDokumentasi,
                'status' => 'pending',
            ]);

            // ==================== LOGIKA NOTIFIKASI BARU ====================
            $user = User::find($request->id_user);
            $namaPengaju = $user ? $user->nama : 'Guru';

            $kepsek = User::where('role', 'kepala_sekolah')->first();

            if ($kepsek) {
                // A. SIMPAN KE DATABASE
                Notifikasi::create([
                    'id_user' => $kepsek->id_user,
                    'judul' => 'Pengajuan Baru',
                    'pesan' => $namaPengaju . ' mengajukan ' . $request->jenis_pengajuan,
                    'dibaca' => 'belum',
                ]);

                // B. KIRIM PUSH NOTIFICATION FCM REALTIME KE HP
                $this->sendFcmNotification(
                    $kepsek->id_user, 
                    'Pengajuan Izin Baru 📄', 
                    'Guru ' . $namaPengaju . ' mengajukan : ' . $request->jenis_pengajuan,
                    [
                        'id_pengajuan' => (string) $pengajuan->id, 
                        'type' => 'approval_pengajuan'
                    ]
                );
            }
            // ================================================================

            return response()->json([
                'message' => 'berhasil'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ], 500);
        }
    }

    public function riwayat($idUser)
    {
        $data = Pengajuan::where('id_user', $idUser)
                 ->whereNotIn('jenis_pengajuan', [
                'wfh',
                'dinas_luar'
            ])
            ->orderByDesc('created_at')
            ->get();

        return response()->json($data);
    }
public function semuaPengajuan(Request $request)
    {
        try {
            // 1. Query dasar menggunakan join murni bawaan Anda
            $query = Pengajuan::join('users', 'pengajuan.id_user', '=', 'users.id_user')
                ->select('pengajuan.*', 'users.nama', 'users.jenis_gtk');

            // 2. FILTER CARI NAMA
            if ($request->has('search') && !empty($request->search)) {
                $query->where('users.nama', 'like', '%' . $request->search . '%');
            }

            // 3. FILTER KATEGORI PEGAWAI / JENIS GTK
            if ($request->has('jenis_gtk') && !empty($request->jenis_gtk) && $request->jenis_gtk != 'Semua') {
                $query->where('users.jenis_gtk', $request->jenis_gtk);
            }

            // 4. FILTER JENIS PENGAJUAN
            if ($request->has('jenis_pengajuan') && !empty($request->jenis_pengajuan) && $request->jenis_pengajuan != 'Semua') {
                $query->where('pengajuan.jenis_pengajuan', $request->jenis_pengajuan);
            }

            // 5. FILTER STATUS
            if ($request->has('status') && !empty($request->status) && $request->status != 'Semua') {
                $query->where('pengajuan.status', strtolower($request->status));
            }

    // 1. AMBIL PARAMETER RENTANG TANGGAL DULU (PENTING UNTUK CALENDER GENERATOR)
    $startDate = $request->start_date;
    $endDate = $request->end_date;

    if ($request->filled('semester')) {
        if ($request->semester == '2024-genap') { $startDate = '2025-01-01'; $endDate = '2025-06-30'; }
        elseif ($request->semester == '2025-ganjil') { $startDate = '2025-07-01'; $endDate = '2025-12-31'; }
        elseif ($request->semester == '2025-genap') { $startDate = '2026-01-01'; $endDate = '2026-06-30'; }
        elseif ($request->semester == '2026-ganjil') { $startDate = '2026-07-01'; $endDate = '2026-12-31'; }
    }

// 7. FILTER RENTANG TANGGAL ATAU SEMESTER
if ($startDate && $endDate) {

    $query->where(function ($q) use ($startDate, $endDate) {

        $q->whereBetween('pengajuan.tanggal_mulai', [$startDate, $endDate])
          ->orWhereBetween('pengajuan.tanggal_selesai', [$startDate, $endDate]);

    });

}

            // Urutkan data terbaru
            $data = $query->orderBy('pengajuan.created_at', 'desc')->get();

            // Kembalikan dalam bentuk List array langsung murni []
            return response()->json($data, 200);

        } catch (\Exception $e) {
            return response()->json([], 500);
        }
    }

    public function verifikasi(Request $request)
    {
        $pengajuan = Pengajuan::find($request->id_pengajuan);

        if (!$pengajuan) {
            return response()->json([
                'success' => false,
                'message' => 'Data pengajuan tidak ditemukan'
            ], 404);
        }

        $pengajuan->status = $request->status; 
        $pengajuan->catatan_verifikasi = $request->catatan;
        $pengajuan->diverifikasi_oleh = $request->id_user; 
        $pengajuan->save();

        // ==================== LOGIKA NOTIFIKASI BALIK KE GURU ====================
        $guru = User::find($pengajuan->id_user);

        if ($guru) {
            $statusTeks = $request->status == 'disetujui' ? 'DISETUJUI ✅' : 'DITOLAK ❌';
            $judulNotif = 'Status Izin: ' . $statusTeks;
            $pesanNotif = 'Pengajuan ' . $pengajuan->jenis_pengajuan . ' Anda telah ' . $request->status . ' oleh Kepala Sekolah.';

            Notifikasi::create([
                'id_user' => $guru->id_user,
                'judul' => $judulNotif,
                'pesan' => $pesanNotif,
                'dibaca' => 'belum',
            ]);

            $pdf = Pdf::loadView('surat.izin', ['pengajuan' => $pengajuan]);
            $namaFile = 'surat_' . $pengajuan->id_pengajuan . '.pdf';

            $pdf->save(public_path('uploads/surat/' . $namaFile));

            $pengajuan->surat_file = $namaFile;
            $pengajuan->save();

            $this->sendFcmNotification(
                $guru->id_user, 
                $judulNotif, 
                $pesanNotif,
                [
                    'id_pengajuan' => (string) $pengajuan->id_pengajuan, 
                    'type' => 'status_izin'
                ]
            );
        }
        // =========================================================================

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan berhasil diverifikasi dan notifikasi telah terkirim ke Guru.'
        ]);
    }

public function cetakRekapIzinApi(Request $request)
{
    $startDate = $request->start_date;
    $endDate = $request->end_date;

    // Gunakan Eloquent dengan Relasi 'user' (bukan menggunakan JOIN manual)
    $query = Pengajuan::with('user');

    // 1. Filter Tanggal
    if ($startDate && $endDate) {
        $query->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('tanggal_mulai', [$startDate, $endDate])
              ->orWhereBetween('tanggal_selesai', [$startDate, $endDate]);
        });
    }

    // 2. Filter Nama Pengaju (Mencari ke dalam relasi user)
    if ($request->filled('search')) {
        $query->whereHas('user', function ($q) use ($request) {
            $q->where('nama', 'like', '%' . $request->search . '%');
        });
    }

    // 3. Filter Status Approval
    if ($request->filled('status') && $request->status != 'semua' && $request->status != 'Semua') {
        $query->where('status', strtolower($request->status));
    }

    // 4. Filter Jenis Pengajuan
    if ($request->filled('jenis_pengajuan') && $request->jenis_pengajuan != 'Semua') {
        $query->where('jenis_pengajuan', strtolower($request->jenis_pengajuan));
    }

    // 5. Filter Kategori Pegawai / GTK
    if ($request->filled('jenis_gtk') && $request->jenis_gtk != 'Semua') {
        $query->whereHas('user', function ($q) use ($request) {
            $q->where('jenis_gtk', $request->jenis_gtk);
        });
    }

    // Ambil data dengan variabel $izins agar klop dengan blade
    $izins = $query->orderBy('created_at', 'desc')->get();

    // Render ke view PDF
    $pdf = Pdf::loadView('admin.pages.rekap_izin.pdf', compact('izins'))
              ->setPaper('a4', 'landscape');

    return $pdf->download('Rekap_Perizinan_Kepsek_' . date('d_m_Y') . '.pdf');
}


public function downloadSurat($id)
{
    // 1. Cari data pengajuan beserta data user terkait
    $pengajuan = Pengajuan::with('user')->find($id);

    if (!$pengajuan) {
        return response()->json([
            'success' => false,
            'message' => 'Pengajuan tidak ditemukan',
        ], 404);
    }

    // 2. Validasi status harus Disetujui
    if (strtolower($pengajuan->status) != 'disetujui') {
        return response()->json([
            'success' => false,
            'message' => 'Surat hanya dapat diunduh setelah disetujui',
        ], 403);
    }

    // 3. Render ke view blade 'surat.izin' (mewakili folder views/surat/izin.blade.php)
    $pdf = Pdf::loadView('surat.izin', compact('pengajuan'))
              ->setPaper('a4', 'portrait');

    // 4. Set nama file unduhan secara dinamis
    $namaFile = 'Surat_Izin_' . str_replace(' ', '_', $pengajuan->user->nama ?? 'Pegawai') . '_' . date('d-m-Y') . '.pdf';

    // Download file langsung ke Flutter / Browser
    return $pdf->download($namaFile);
}

public function previewSurat($id)
{
    // 1. Cari data pengajuan beserta data user terkait
    $pengajuan = Pengajuan::with('user')->find($id);

    if (!$pengajuan) {
        abort(404, 'Pengajuan tidak ditemukan');
    }

    // 2. Render ke view blade 'surat.izin'
    $pdf = Pdf::loadView('surat.izin', compact('pengajuan'))
              ->setPaper('a4', 'portrait');

    // 3. Gunakan stream() agar PDF bisa di-preview langsung di tab browser
    return $pdf->stream('Preview_Surat_Izin.pdf');
}
}