<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Presensi;
use App\Models\Pengajuan;
use App\Models\HariLibur;
use App\Models\JamKerja;
use Carbon\Carbon;

class CheckAlphaOtomatis extends Command
{
    // Nama perintah yang akan dipanggil oleh sistem
    protected $signature = 'presensi:check-alpha';
    protected $description = 'Mengecek dan mencatat status Alpha jika jam pulang sudah lewat';

    public function handle()
    {
        $now = Carbon::now();
        $today = Carbon::today();
        $hariIniFormat = $today->format('Y-m-d');

        // ===================================================================
        // CEK 1: Apakah Jam pulang jam kerja aktif sudah lewat?
        // ===================================================================
        $jamKerja = JamKerja::where('status', 'aktif')->first();
        
        if (!$jamKerja) {
            $this->error('Tidak ada jam kerja aktif.');
            return 0;
        }

        $waktuPulang = Carbon::today()->setTimeFromTimeString($jamKerja->jam_pulang);

        // Jika saat ini BELUM melewati jam pulang, hentikan proses (jangan buat alpha dulu)
        if ($now->lt($waktuPulang)) {
            $this->info('Belum waktunya, jam pulang belum lewat.');
            return 0;
        }

        // ===================================================================
        // CEK 2: Apakah hari libur? (Ya -> Lewati semua)
        // ===================================================================
        if (HariLibur::whereDate('tanggal', $hariIniFormat)->exists()) {
            $this->info('Hari ini libur nasional/sekolah. Proses Alpha dihentikan.');
            return 0; 
        }

        // ===================================================================
        // CEK 3: Ambil seluruh GTK
        // ===================================================================
        $daftarGtk = User::whereIn('role', ['guru']) 
                         ->where('status_akun', 'aktif') 
                         ->get();

        foreach ($daftarGtk as $gtk) {
            $jenisGtk = strtolower($gtk->jenis_gtk);

            // ===================================================================
            // CEK 4: Sabtu/Minggu -> Lewati 
            // ===================================================================
            if ($today->isSaturday() || $today->isSunday()) {
                continue; 
            }
            
            // ===================================================================
            // CEK 5: Sudah ada record presensi hari ini? (Ya -> Lewati)
            // ===================================================================
            // Ini sudah mencakup Hadir normal, Terlambat, WFH Otomatis, dan Dinas Luar Otomatis
            // karena semuanya akan masuk ke tabel presensis.
            $sudahPresensi = Presensi::where('id_user', $gtk->id_user)
                ->whereDate('tanggal', $hariIniFormat)
                ->exists();

            if ($sudahPresensi) {
                continue;
            }

            // ===================================================================
            // CEK 6: Ada izin/sakit/cuti/WFH/dinas luar yang disetujui? (Ya -> Lewati)
            // ===================================================================
            $adaPengajuan = Pengajuan::where('id_user', $gtk->id_user)
                ->where('status', 'disetujui')
                ->whereDate('tanggal_mulai', '<=', $hariIniFormat)
                ->whereDate('tanggal_selesai', '>=', $hariIniFormat)
                ->exists();

            if ($adaPengajuan) {
                continue;
            }

            // ===================================================================
            // FINAL: INSERT PRESENSI -> status = alpha
            // ===================================================================
            Presensi::create([
                'id_user' => $gtk->id_user,
                'tanggal' => $hariIniFormat,
                'jam_masuk' => null,
                'jam_keluar' => null,
                'status' => 'alpha', 
                'status_keterlambatan' => null,
                'latitude' => '0.0',
                'longitude' => '0.0',
                'jarak' => 0,
                'keterangan' => 'Tidak Hadir',
            ]);

            $this->info("Berhasil mencatat Alpha untuk: {$gtk->nama}");
        }

        $this->info('Proses Auto-Alpha selesai dieksekusi.');
        return 0;
    }
}