<?php

namespace App\Http\Controllers\Admin;

use SimpleSoftwareIO\QrCode\Facades\QrCode as QR;
use App\Http\Controllers\Controller;
use App\Models\QrCode;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QrCodeController extends Controller
{
    // Halaman QR Code tunggal
    public function index(Request $request)
    {
        // Mengambil data QR Code yang pertama/aktif. Jika belum ada, otomatis membuat baru satu data.
        $qr = QrCode::firstOrCreate(
            [], // Kriteria pencarian kosong agar mengambil baris pertama apa saja
            [
                'nama_qr' => 'QR Code Presensi Utama',
                'kode_qr' => 'QR-' . strtoupper(Str::random(8)),
                'status'  => 'aktif'
            ]
        );

        return view(
            'admin.pages.qr_code.index',
            compact('qr')
        );
    }

    // Fungsi Regenerate Kode QR (Menggantikan Store & Update lama)
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_qr' => 'required'
        ]);

        $qr = QrCode::findOrFail($id);

        // Perbarui Nama dan buat string Kode QR acak baru agar token lama hangus
        $qr->update([
            'nama_qr' => $request->nama_qr,
            'kode_qr' => 'QR-' . strtoupper(Str::random(8)),
            'status'  => 'aktif' 
        ]);

        return redirect()
            ->back()
            ->with(
                'success',
                'Kode QR berhasil diperbarui (Regenerated)!'
            );
    }

    // Cetak QR tetap dipertahankan
    public function print($id)
    {
        $qr = QrCode::findOrFail($id);

        return view(
            'admin.pages.qr_code.print',
            compact('qr')
        );
    }
}