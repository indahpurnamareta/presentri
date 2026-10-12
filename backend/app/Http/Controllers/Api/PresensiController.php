<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Presensi;
use App\Models\Lokasi;
use App\Models\QrCode;
use Carbon\Carbon;
use App\Models\JamKerja;
use App\Models\Pengajuan;

class PresensiController extends Controller
{
    
private function isHariLibur($date)
    {
        // Menggunakan model HariLibur yang terhubung dengan database Anda
        // Mencari apakah tanggal hari ini ada di dalam tabel hari_libur
        $liburDinamis = \App\Models\HariLibur::whereDate('tanggal', $date)->first();
    
        if ($liburDinamis) {
            return [
                'is_libur' => true,
                'keterangan' => 'Hari Libur: ' . $liburDinamis->keterangan
            ];
        }
    
        // Jika tidak terdaftar di tabel hari_libur, maka sistem menganggapnya sebagai hari kerja/kegiatan aktif
        return [
            'is_libur' => false,
            'keterangan' => 'Hari Kerja'
        ];
    }


 public function presensiMasuk(Request $request)
    {
        $request->validate([
            'id_user' => 'required',
            'kode_qr' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
        ]);

        $today = Carbon::today();
        
        // CEK DATA LIBUR DARI DATABASE ADMIN
        $cekLibur = $this->isHariLibur($today);
        if ($cekLibur['is_libur']) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal Presensi: ' . $cekLibur['keterangan']
            ]);
        }

        // 1. Ambil Jam Kerja yang AKTIF saat ini
        $jamKerja = JamKerja::where('status', 'aktif')->first();
        if (!$jamKerja) {
            return response()->json([
                'success' => false,
                'message' => 'Sistem Sekolah Belum Siap: Jam kerja aktif belum diatur di database.'
            ]);
        }

        // 2. SINKRONISASI BARU: Cek apakah Jam Kerja yang aktif bermode 'wfh_massal'
        $isWfhMassal = ($jamKerja->mode === 'wfh_massal');

        // 3. Cek status pengajuan khusus aktif individu (WFH / Dinas Luar secara personal)
        $pengajuanAktif = Pengajuan::where('id_user', $request->id_user)
            ->whereDate('tanggal_mulai', '<=', $today)
            ->whereDate('tanggal_selesai', '>=', $today)
            ->where('status', 'disetujui')
            ->first();

        // GURU BEBAS DARI QR & JARAK JIKA: Sekolah sedang WFH Massal ATAU Guru punya izin personal
        $isIzinKhusus = $isWfhMassal || ($pengajuanAktif && in_array($pengajuanAktif->jenis_pengajuan, ['wfh', 'dinas_luar']));

        // Ambil data default lokasi & QR aktif terlebih dahulu agar database tidak error NOT NULL
        $lokasi = Lokasi::where('status', 'aktif')->first();
        $qr = QrCode::where('status', 'aktif')->first();

        if (!$lokasi || !$qr) {
            return response()->json([
                'success' => false,
                'message' => 'Sistem Sekolah Belum Siap: Lokasi atau QR aktif tidak ditemukan di database.'
            ]);
        }

        $idQr = $qr->id_qr;
        $idLokasi = $lokasi->id_lokasi;
        $jarak = 0; 

        // 4. Jika BUKAN WFH Massal atau Izin Khusus, jalankan validasi fisik secara ketat (QR & Jarak)
        if (!$isIzinKhusus) {
            $qrSpesifik = QrCode::where('kode_qr', $request->kode_qr)->where('status', 'aktif')->first();
            if (!$qrSpesifik) {
                return response()->json([
                    'success' => false,
                    'message' => 'QR Code tidak valid'
                ]);
            }
            $idQr = $qrSpesifik->id_qr;

            // Hitung jarak (Haversine) ke sekolah
            $earthRadius = 6371000;
            $latFrom = deg2rad($request->latitude);
            $lonFrom = deg2rad($request->longitude);
            $latTo = deg2rad($lokasi->latitude);
            $lonTo = deg2rad($lokasi->longitude);

            $latDelta = $latTo - $latFrom;
            $lonDelta = $lonTo - $lonFrom;

            $angle = 2 * asin(sqrt(
                pow(sin($latDelta / 2), 2) +
                cos($latFrom) * cos($latTo) *
                pow(sin($lonDelta / 2), 2)
            ));

            $jarak = $angle * $earthRadius;

            // Validasi radius sekolah
            if ($jarak > $lokasi->radius) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda berada di luar area sekolah',
                    'jarak' => round($jarak)
                ]);
            }
        }

        // 5. Cek apakah sudah pernah presensi masuk hari ini
        $cek = Presensi::where('id_user', $request->id_user)
            ->whereDate('tanggal', $today)
            ->first();

        if ($cek) {
            // JIKA RECORD TERNYATA ALPHA
            if (strtolower($cek->status) === 'alpha') {
                return response()->json([
                    'success' => false,
                    'message' => 'Presensi hari ini telah tercatat alfa'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Anda sudah presensi masuk hari ini'
            ]);
        }

        $now = Carbon::now();

        // Validasi rentang waktu buka/tutup presensi masuk berdasarkan JAM KERJA AKTIF
        $jamMasukBase = Carbon::today()->setTimeFromTimeString($jamKerja->jam_masuk);
        $bukaPresensi = $jamMasukBase->copy()->subHour();
        $tutupPresensi = Carbon::today()->setTimeFromTimeString($jamKerja->jam_pulang);

        if ($now->lt($bukaPresensi) || $now->gt($tutupPresensi)) {
            $formatTutup = $tutupPresensi->format('H:i');
            return response()->json([
                'success' => false,
                'message' => "Presensi masuk hari ini sudah ditutup pukul $formatTutup",
            ]);
        }

        // 6. PENENTUAN STATUS KEHADIRAN & KETERLAMBATAN
        $batasMasuk = Carbon::today()->setTimeFromTimeString($jamKerja->batas_jam_masuk);
        
        if ($isWfhMassal) {
            // Jika WFH massal aktif, semua otomatis tepat waktu dan statusnya 'wfh'
            $statusKeterlambatan = 'tepat_waktu'; 
            $statusKehadiran = 'wfh';
            $keterangan = 'WFH Massal Sekolah Aktif';
        } else if ($pengajuanAktif && in_array($pengajuanAktif->jenis_pengajuan, ['wfh', 'dinas_luar'])) {
            $statusKeterlambatan = 'tepat_waktu'; 
            $statusKehadiran = strtolower($pengajuanAktif->jenis_pengajuan);
            $keterangan = 'Presensi ' . strtoupper($pengajuanAktif->jenis_pengajuan) . ' Mandiri';
        } else {
            $statusKeterlambatan = $now->gt($batasMasuk) ? 'terlambat' : 'tepat_waktu';
            $statusKehadiran = 'hadir';
            $keterangan = '-';
        }

        // 7. Simpan data ke database presensi
        Presensi::create([
            'id_user' => $request->id_user,
            'id_lokasi' => $idLokasi, 
            'id_qr' => $idQr,         
            'tanggal' => Carbon::today(),
            'jam_masuk' => $now->format('H:i:s'),
            'jam_keluar' => null,
            'status' => $statusKehadiran, // Otomatis menyimpan string 'wfh' jika wfh massal aktif
            'status_keterlambatan' => $statusKeterlambatan,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'jarak' => round($jarak),
            'keterangan' => $keterangan,
        ]);

        return response()->json([
            'success' => true,
            'message' => $isIzinKhusus 
                ? 'Presensi masuk khusus (' . strtoupper($statusKehadiran) . ') berhasil!' 
                : 'Presensi berhasil'
        ]);
    }
    
public function presensiPulang(Request $request)
    {
        $request->validate([
            'id_user' => 'required',
            'kode_qr' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
        ]);

        $today = Carbon::today();

        // 1. Ambil Jam Kerja yang AKTIF
        $jamKerja = JamKerja::where('status', 'aktif')->first();
        if (!$jamKerja) {
            return response()->json([
                'success' => false,
                'message' => 'Jam kerja aktif tidak ditemukan.'
            ]);
        }
        
        // Presensi pulang HANYA BISA dilakukan jika waktu sekarang >= jam_pulang
        // =========================================================================
        $now = Carbon::now();
        $jamPulangBuka = Carbon::today()->setTimeFromTimeString($jamKerja->jam_pulang);

        if ($now->lt($jamPulangBuka)) {
            $formatJamPulang = $jamPulangBuka->format('H:i');
            return response()->json([
                'success' => false,
                'message' => "Presensi pulang dibuka pukul $formatJamPulang WIB."
            ]);
        }

        // 2. Cek apakah sekolah sedang WFH Massal atau Guru punya izin personal
        $isWfhMassal = ($jamKerja->mode === 'wfh_massal');
        
        $pengajuanAktif = Pengajuan::where('id_user', $request->id_user)
            ->whereDate('tanggal_mulai', '<=', $today)
            ->whereDate('tanggal_selesai', '>=', $today)
            ->where('status', 'disetujui')
            ->first();

        $isIzinKhusus = $isWfhMassal || ($pengajuanAktif && in_array($pengajuanAktif->jenis_pengajuan, ['wfh', 'dinas_luar']));
        $jarak = 0; 

        // 3. Jika bukan WFH Massal / Izin Khusus, jalankan validasi fisik (QR & Radius Pulang)
        if (!$isIzinKhusus) {
            $qr = QrCode::where('kode_qr', $request->kode_qr)->where('status', 'aktif')->first();
            if (!$qr) {
                return response()->json([
                    'success' => false,
                    'message' => 'QR Code tidak valid'
                ]);
            }

            $lokasi = Lokasi::where('status', 'aktif')->first();
            if (!$lokasi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lokasi belum diatur'
                ]);
            }

            // HAVERSINE DISTANCE
            $earthRadius = 6371000;
            $latFrom = deg2rad($request->latitude);
            $lonFrom = deg2rad($request->longitude);
            $latTo = deg2rad($lokasi->latitude);
            $lonTo = deg2rad($lokasi->longitude);

            $latDelta = $latTo - $latFrom;
            $lonDelta = $lonTo - $lonFrom;

            $angle = 2 * asin(sqrt(
                pow(sin($latDelta / 2), 2) +
                cos($latFrom) * cos($latTo) *
                pow(sin($lonDelta / 2), 2)
            ));

            $jarak = $angle * $earthRadius;

            if ($jarak > $lokasi->radius) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda berada di luar area sekolah',
                    'jarak' => round($jarak)
                ]);
            }
        }

        // 4. Cek data presensi masuk hari ini
        $presensi = Presensi::where('id_user', $request->id_user)
            ->whereDate('tanggal', $today)
            ->first();

        if (!$presensi) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum presensi masuk'
            ]);
        }
        
        if (strtolower($presensi->status) === 'alpha') {
            return response()->json([
                'success' => false,
                'message' => 'Presensi hari ini telah tercatat alfa'
            ]);
        }

        if ($presensi->jam_keluar != null) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah presensi pulang'
            ]);
        }

        // 5. Update jam pulang guru
        $presensi->update([
            'jam_keluar' => Carbon::now()->format('H:i:s')
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Presensi pulang berhasil dicatat!'
        ]);
    }
    

public function statusHariIni($id_user)
{
    $today = Carbon::today();
    
    // 1. CEK APAKAH HARI INI TERDAFTAR DI TABEL HARI LIBUR
    $cekLibur = $this->isHariLibur($today);
    if ($cekLibur['is_libur']) {
        return response()->json([
            'status' => 'libur', 
            'text' => $cekLibur['keterangan'],
        ]);
    }
    // 2. CEK PENGAJUAN IZIN KHUSUS / DISPENSASI (DINAS LUAR / WFH) TERLEBIH DAHULU
    $pengajuan = Pengajuan::where('id_user', $id_user)
        ->whereDate('tanggal_mulai', '<=', $today)
        ->whereDate('tanggal_selesai', '>=', $today)
        ->where('status', 'disetujui')
        ->whereIn('jenis_pengajuan', ['dinas_luar', 'wfh'])
        ->latest()
        ->first();

    // 3. CEK DATA PRESENSI HARI INI
    $presensi = Presensi::where('id_user', $id_user)
        ->whereDate('tanggal', $today)
        ->first();

    // JIKA ADA PENGAJUAN DINAS LUAR / WFH
    if ($pengajuan) {
        $statusSlug = strtolower($pengajuan->jenis_pengajuan);
        $tglMulai = Carbon::parse($pengajuan->tanggal_mulai)->format('d M Y');
        $tglSelesai = Carbon::parse($pengajuan->tanggal_selesai)->format('d M Y');
        $stringTanggal = ($tglMulai == $tglSelesai) ? $tglMulai : "$tglMulai - $tglSelesai";

        // Cek status presensinya untuk memberikan status spesifik
        $isSudahPresensi = ($presensi && $presensi->jam_masuk != null);
        $isSudahSelesai = ($presensi && $presensi->jam_keluar != null);

        return response()->json([
            'status' => $statusSlug, // Tetap 'dinas_luar' atau 'wfh' agar Badge Flutter SELALU MUNCUL!
            'text' => ucwords(str_replace('_', ' ', $statusSlug)),
            'tanggal' => $stringTanggal,
            'keterangan' => $pengajuan->alasan ?? 'Penugasan ' . strtoupper($statusSlug),
            'bukti_file' => $pengajuan->bukti_file ?? $pengajuan->file ?? null,
            
            // Tambahkan info status presensinya agar Flutter tahu dia sudah klik presensi atau belum
            'is_sudah_masuk' => $isSudahPresensi,
            'is_sudah_selesai' => $isSudahSelesai,
            'jam_masuk' => $presensi ? $presensi->jam_masuk : null,
            'jam_keluar' => $presensi ? $presensi->jam_keluar : null,
        ]);
    }

    if ($presensi) {
        // =========================================================================
        // TAMBAHAN PROTEKSI FITUR AUTO-ALPHA:
        // Jika statusnya terdeteksi 'alpha' dari sistem, jangan baca sebagai 'masuk'.
        // =========================================================================
        if (strtolower($presensi->status) === 'alpha') {
            return response()->json([
                'status' => 'alpha',
                'text' => 'Tidak Presensi Tanpa Keterangan',
            ]);
        }

        if ($presensi->jam_keluar == null) {
            return response()->json([
                'status' => 'masuk',
                'text' => 'Sudah Presensi Masuk',
            ]);
        }
        return response()->json([
            'status' => 'selesai',
            'text' => 'Presensi Selesai Hari Ini',
        ]);
    }

    // 3. JIKA BELUM PRESENSI, BARU CEK PENGAJUAN IZIN KHUSUS / LAYANAN DISPENSASI INDIVIDU
    $pengajuan = Pengajuan::where('id_user', $id_user)
        ->whereDate('tanggal_mulai', '<=', $today)
        ->whereDate('tanggal_selesai', '>=', $today)
        ->latest()
        ->first();

    if ($pengajuan) {
        if (strtolower($pengajuan->status) == 'pending') {
            $jenis = str_replace('_', ' ', $pengajuan->jenis_pengajuan);
            return response()->json([
                'status' => 'pending',
                'text' => ucwords($jenis) . ' Telah Diajukan',
            ]);
        }

        if (strtolower($pengajuan->status) == 'disetujui') {
            $jenis = str_replace('_', ' ', $pengajuan->jenis_pengajuan);
            $statusSlug = strtolower($pengajuan->jenis_pengajuan);
            
            // RESPON KHUSUS DINAS LUAR / WFH DENGAN DATA TAMBAHAN
            return response()->json([
                'status' => $statusSlug,
                'text' => ucwords($jenis) . ' Disetujui',
                'tanggal' => $today->toDateString(),
                'keterangan' => $pengajuan->alasan ?? 'Penugasan ' . ucwords($jenis),
                'bukti_file' => $pengajuan->bukti_file ?? $pengajuan->file ?? null,
            ]);
        }
    }

    // 4. CEK LOGIKA WFH MASSAL SEKOLAH
    $jamKerja = JamKerja::where('status', 'aktif')->first();
    if ($jamKerja && $jamKerja->mode === 'wfh_massal') {
        return response()->json([
            'status' => 'wfh', 
            'text' => 'Sekolah Sedang WFH',
        ]);
    }

    // Default jika benar-benar tidak ada data apapun
    return response()->json([
        'status' => 'belum',
        'text' => 'Belum Presensi',
    ]);
}

public function riwayatPresensi($id_user)
{
    // 1. Ambil data presensi
    $data = Presensi::where('id_user', $id_user)->get();

    // 2. Ambil pengajuan yang disetujui (izin, sakit, cuti, dinas_luar, wfh)
    $pengajuans = Pengajuan::where('id_user', $id_user)
        ->where('status', 'disetujui')
        ->whereIn('jenis_pengajuan', ['izin', 'sakit', 'cuti', 'dinas_luar', 'wfh'])
        ->get();

    foreach ($pengajuans as $pengajuan) {

        $tanggal = Carbon::parse($pengajuan->tanggal_mulai);
        $selesai = Carbon::parse($pengajuan->tanggal_selesai);

        while ($tanggal->lte($selesai)) {
            $tglStr = $tanggal->toDateString();

            // Cek apakah di tanggal ini user SUDAH presensi di tabel presensi
            $presensiEksis = $data->firstWhere('tanggal', $tglStr);

            if ($presensiEksis) {
                // Jika SUDAH presensi (misal presensi Dinas Luar/WFH via app):
                // Cukup tempelkan file bukti_file nya ke record presensi tersebut
                $presensiEksis->bukti_file = $pengajuan->bukti_file ?? $pengajuan->file ?? null;
            } else {
                // Jika BELUM presensi:
                // Push data pengajuan sebagai record baru
                $data->push([
                    'tanggal' => $tglStr,
                    'status' => $pengajuan->jenis_pengajuan,
                    'status_keterlambatan' => null,
                    'jam_masuk' => null,
                    'jam_keluar' => null,
                    'keterangan' => $pengajuan->alasan ?? ucfirst(str_replace('_', ' ', $pengajuan->jenis_pengajuan)),
                    'bukti_file' => $pengajuan->bukti_file ?? $pengajuan->file ?? null,
                    'foto_dokumentasi' => $pengajuan->foto_dokumentasi ?? null,
                ]);
            }

            $tanggal->addDay();
        }
    }

    return response()->json(
        $data->sortBy('tanggal')->values()
    );
}
    
 /**
     * Endpoint Khusus untuk pencatatan Presensi Dinas Luar kilat via Kamera HP langsung
     */
    public function presensiDinasLuar(Request $request)
    {
        try {
            $request->validate([
                'id_user' => 'required',
                'foto_dokumentasi' => 'required|image|mimes:jpeg,png,jpg|max:5120', // batas max 5MB
            ]);

            $today = Carbon::today()->toDateString();

            // 1. Cek apakah hari ini guru tersebut sudah memiliki data presensi
            $presensiEksis = Presensi::where('id_user', $request->id_user)
                ->whereDate('tanggal', $today)
                ->first();

            if ($presensiEksis) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah melakukan presensi hari ini!'
                ]);
            }                
              
             $publicHtmlPath = base_path('../');
            // 1. PROSES UPLOAD BUKTI FILE (DOKUMEN LAMPIRAN)
            if ($request->hasFile('foto_dokumentasi')) {
                $file = $request->file('foto_dokumentasi');
                $namaFile = 'dinas_' . $request->id_user . '_' . time() . '.' . $file->getClientOriginalExtension();
                
                // Menyimpan langsung ke public_html/uploads/pengajuan
                $file->move(
                    $publicHtmlPath . '/uploads/dokumentasi',
                    $namaFile
                );
            }
            //UPDATE DOKUMENTASI DI PENGAJUAN
            $pengajuan = Pengajuan::where('id_user', $request->id_user)
                ->where('jenis_pengajuan', 'dinas_luar')
                ->where('status', 'disetujui')
                ->whereDate('tanggal_mulai', '<=', $today)
                ->whereDate('tanggal_selesai', '>=', $today)
                ->first();

            if ($pengajuan) {
                $pengajuan->update([
                    'foto_dokumentasi' => $namaFile
                ]);
            }

            // Ambil data default lokasi & QR aktif agar database tidak melempar error 'NOT NULL constraints'
            $lokasi = Lokasi::where('status', 'aktif')->first();
            $qr = QrCode::where('status', 'aktif')->first();

            // 3. Masukkan langsung records ke tabel presensi sebagai 'dinas_luar' yang sah
            $dinasLuar = Presensi::create([
                'id_user' => $request->id_user,
                'id_lokasi' => $lokasi ? $lokasi->id_lokasi : null, // Ambil ID default penjamin relasi database
                'id_qr' => $qr ? $qr->id_qr : null,                 // Ambil ID default penjamin relasi database
                'tanggal' => $today,
                'jam_masuk' => Carbon::now()->format('H:i:s'),
                'jam_keluar' => null, 
                'status' => 'dinas_luar', 
                
                // Samakan string value 'tepat_waktu' dengan fungsi presensiMasuk Anda (menggunakan snake_case)
                'status_keterlambatan' => 'tepat_waktu', 
                
                'latitude' => $request->latitude ?? '0.0',
                'longitude' => $request->longitude ?? '0.0',
                'jarak' => 0,
                'keterangan' => 'Presensi Dinas Luar',
                
                // Jika tabel presensi Anda memiliki kolom khusus penyimpan path gambar/foto, aktifkan baris ini:
                // 'foto' => $pathFotoDb
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Presensi Dinas Luar berhasil dicatat! Selamat bertugas.',
                'data' => $dinasLuar
            ]);

        } catch (\Exception $e) {
            // PENGAMAN UTAMA: Jika ada error internal PHP/Database, paksa kirim JSON agar Flutter tidak menerima HTML
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan internal server Laravel.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }

public function downloadSuratTugas($filename)
{
    // Path folder tempat surat tugas disimpan di server
    $filePath = base_path('../uploads/pengajuan/' . $filename);

    if (file_exists($filePath)) {
        // Response 'download' memaksa browser/HP untuk mengunduh berkas, bukan sekadar preview
        return response()->download($filePath, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    return response()->json([
        'success' => false,
        'message' => 'Berkas tidak ditemukan di server.'
    ], 404);
}

public function presensiWfh(Request $request)
    {
        try {
            $request->validate([
                'id_user' => 'required',
                'foto_dokumentasi' => 'required|image|mimes:jpeg,png,jpg|max:5120', // batas max 5MB
            ]);

            $today = Carbon::today()->toDateString();

            // 1. Cek apakah hari ini guru tersebut sudah memiliki data presensi
            $presensiEksis = Presensi::where('id_user', $request->id_user)
                ->whereDate('tanggal', $today)
                ->first();

            if ($presensiEksis) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah melakukan presensi hari ini!'
                ]);
            }                
              
             $publicHtmlPath = base_path('../');
            // 1. PROSES UPLOAD BUKTI FILE (DOKUMEN LAMPIRAN)
            if ($request->hasFile('foto_dokumentasi')) {
                $file = $request->file('foto_dokumentasi');
                $namaFile = 'wfh_' . $request->id_user . '_' . time() . '.' . $file->getClientOriginalExtension();
                
                // Menyimpan langsung ke public_html/uploads/pengajuan
                $file->move(
                    $publicHtmlPath . '/uploads/dokumentasi',
                    $namaFile
                );
            }
            //UPDATE DOKUMENTASI DI PENGAJUAN
            $pengajuan = Pengajuan::where('id_user', $request->id_user)
                ->where('jenis_pengajuan', 'wfh')
                ->where('status', 'disetujui')
                ->whereDate('tanggal_mulai', '<=', $today)
                ->whereDate('tanggal_selesai', '>=', $today)
                ->first();

            if ($pengajuan) {
                $pengajuan->update([
                    'foto_dokumentasi' => $namaFile
                ]);
            }

            // Ambil data default lokasi & QR aktif agar database tidak melempar error 'NOT NULL constraints'
            $lokasi = Lokasi::where('status', 'aktif')->first();
            $qr = QrCode::where('status', 'aktif')->first();

            // 3. Masukkan langsung records ke tabel presensi sebagai 'wfh' yang sah
            $wfh = Presensi::create([
                'id_user' => $request->id_user,
                'id_lokasi' => $lokasi ? $lokasi->id_lokasi : null, // Ambil ID default penjamin relasi database
                'id_qr' => $qr ? $qr->id_qr : null,                 // Ambil ID default penjamin relasi database
                'tanggal' => $today,
                'jam_masuk' => Carbon::now()->format('H:i:s'),
                'jam_keluar' => null, 
                'status' => 'wfh', 
                
                // Samakan string value 'tepat_waktu' dengan fungsi presensiMasuk Anda (menggunakan snake_case)
                'status_keterlambatan' => 'tepat_waktu', 
                
                'latitude' => $request->latitude ?? '0.0',
                'longitude' => $request->longitude ?? '0.0',
                'jarak' => 0,
                'keterangan' => 'Presensi WFH',
                
                // Jika tabel presensi Anda memiliki kolom khusus penyimpan path gambar/foto, aktifkan baris ini:
                // 'foto' => $pathFotoDb
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Presensi WFH berhasil dicatat! ',
                'data' => $wfh
            ]);

        } catch (\Exception $e) {
            // PENGAMAN UTAMA: Jika ada error internal PHP/Database, paksa kirim JSON agar Flutter tidak menerima HTML
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan internal server Laravel.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }
}