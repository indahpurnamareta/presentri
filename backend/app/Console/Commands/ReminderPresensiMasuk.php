<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User; 
use App\Models\Presensi; 
use App\Models\JamKerja;
use App\Models\HariLibur; // Tambahan model HariLibur
use App\Models\Pengajuan; // Tambahan model Pengajuan
use Carbon\Carbon;

class ReminderPresensiMasuk extends Command
{
    // Nama command yang akan dipanggil oleh sistem
    protected $signature = 'reminder:presensi-masuk';
    protected $description = 'Mengirim notifikasi pengingat presensi masuk kepada guru yang belum absen';

    public function handle()
    {
        // 1. Ambil jam kerja yang sedang aktif
        $jamKerja = JamKerja::where('status', 'aktif')->first();
        
        if (!$jamKerja) {
            $this->info('Tidak ada jam kerja yang aktif saat ini.');
            return 0;
        }

        $now = Carbon::now();
        $hariIni = $now->format('Y-m-d');
        $hariKe = $now->dayOfWeek; // 0 = Minggu, 1 = Senin, ..., 6 = Sabtu

        // 2. PROTEKSI HARI LIBUR NASIONAL / SEKOLAH
        $isLibur = HariLibur::whereDate('tanggal', $hariIni)->exists();
        if ($isLibur || $hariKe === 0) { // Jika hari libur atau hari Minggu, langsung stop
            $this->info('Hari ini hari libur atau hari Minggu. Reminder tidak dikirim.');
            return 0;
        }

        // 3. Ambil semua User dengan role 'guru' yang memiliki fcm_token dan status aktif
        $paraGtk = User::where('role', 'guru')
                        ->where('status_akun', 'aktif')
                        ->whereNotNull('fcm_token')
                        ->get();

        foreach ($paraGtk as $gtk) {
            $jenisGtk = strtolower($gtk->jenis_gtk);

            
            // Sabtu libur (dayOfWeek = 6)
            if ($hariKe === 6) {
                continue;
            }

            // 5. CEK APAKAH SUDAH PRESENSI HARI INI
            $sudahAbsen = Presensi::where('id_user', $gtk->id_user)
                                  ->whereDate('tanggal', $hariIni)
                                  ->exists();

            if ($sudahAbsen) {
                continue; // Skip jika sudah presensi
            }

            // 6. CEK APAKAH ADA IZIN/SAKIT/CUTI/DL/WFH YANG DISETUJUI HARI INI
            $adaIzin = Pengajuan::where('id_user', $gtk->id_user)
                                ->where('status', 'disetujui')
                                ->whereDate('tanggal_mulai', '<=', $hariIni)
                                ->whereDate('tanggal_selesai', '>=', $hariIni)
                                ->exists();

            if ($adaIzin) {
                continue; // Skip jika sudah mengajukan perizinan resmi yang disetujui
            }

            // 7. JIKA lolos semua pengecekan, kirim push notification via Firebase
            $this->kirimNotifikasiFirebase($gtk, $jamKerja->jam_masuk);
        }

        $this->info('Notifikasi pengingat presensi berhasil diproses.');
        return 0;
    }

    private function kirimNotifikasiFirebase($guru, $jamMasuk)
    {
        $formatJam = \Carbon\Carbon::parse($jamMasuk)->format('H:i');
        
        $jsonPath = storage_path('app/firebase_credentials.json');
        if (!file_exists($jsonPath)) {
            $this->error('File firebase_credentials.json tidak ditemukan di storage/app/');
            return;
        }

        $credentials = json_decode(file_get_contents($jsonPath), true);
        $projectId = $credentials['project_id'];

        $accessToken = $this->getGoogleAccessToken($credentials);

        if (!$accessToken) {
            $this->error('Gagal men-generate Google Access Token.');
            return;
        }

        $fcmUrl = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        $dataNotif = [
            'message' => [
                'token' => $guru->fcm_token,
                'notification' => [
                    'title' => '⏰ Jangan Lupa Presensi Pagi!',
                    'body' => "Halo " . $guru->nama . ", waktu masuk jam " . $formatJam . " WIB. Yuk segera lakukan presensi scanning QR!",
                ],
                'android' => [
                    'priority' => 'HIGH', 
                    'ttl' => '0s',
                    'notification' => [
                        'sound' => 'default',
                    ]
                ],
                'data' => [
                    'tipe' => 'reminder_presensi',
                ]
            ]
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fcmUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dataNotif));
        
        $response = curl_exec($ch);
        curl_close($ch);

        $this->info("Respon Firebase V1 untuk {$guru->nama}: " . $response);

        // Simpan riwayat ke database lokal
        \App\Models\Notifikasi::create([
            'id_user' => $guru->id_user,
            'judul' => '⏰ Jangan Lupa Presensi Pagi!',
            'pesan' => "Waktu masuk jam " . $formatJam . " WIB. Segera lakukan presensi ya!",
            'dibaca' => 'belum'
        ]);
    }

    private function getGoogleAccessToken($credentials)
    {
        $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
        
        $time = time();
        $payload = json_encode([
            'iss' => $credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/cloud-platform',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $time + 3600,
            'iat' => $time
        ]);

        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        $signature = '';
        openssl_sign($base64UrlHeader . "." . $base64UrlPayload, $signature, $credentials['private_key'], 'SHA256');
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ]));

        $result = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($result, true);
        return $response['access_token'] ?? null;
    }
}