<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    // Mengambil daftar notifikasi milik user tertentu
    public function getNotifikasi($idUser)
        {
            $notif = Notifikasi::where('id_user', $idUser)
                ->latest()
                ->get();

            return response()->json($notif);
        }

    // Mengambil jumlah notifikasi yang belum dibaca
    public function totalBelumDibaca($idUser)
        {
            $total = Notifikasi::where('id_user', $idUser)
                ->where('dibaca', 'belum')
                ->count();

            return response()->json([
                'total' => $total
            ]);
        }
        
    public function tandaiDibaca($idNotifikasi)
        {
            // Mencari berdasarkan id_notifikasi (karena sudah didefinisikan sebagai primaryKey di Model)
            $notif = Notifikasi::find($idNotifikasi);
            
            if ($notif) {
                $notif->dibaca = 'sudah';
                $notif->save();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Notifikasi berhasil ditandai sudah dibaca.'
                ], 200);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Notifikasi tidak ditemukan dengan ID: ' . $idNotifikasi
            ], 404);
        }
}