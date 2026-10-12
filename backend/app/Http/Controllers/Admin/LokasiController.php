<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lokasi;
use Illuminate\Support\Facades\DB; // Tambahkan ini untuk transaction

class LokasiController extends Controller
{
    // halaman lokasi
    public function index(Request $request)
    {
        $search = $request->search;

        $lokasis = Lokasi::when($search, function ($query) use ($search) {
            $query->where('nama_lokasi', 'like', "%$search%");
        })->latest()->get();

        return view(
            'admin.pages.lokasi.index',
            compact('lokasis')
        );
    }

    // tambah lokasi
    public function store(Request $request)
    {
        $request->validate([
            'nama_lokasi' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'radius' => 'required|numeric',
            'status' => 'required|in:aktif,nonaktif', // Validasi diperketat biar aman
        ]);

        // Membungkus dengan DB::transaction agar data konsisten
        DB::transaction(function () use ($request) {
            
            // JIKA lokasi baru diset AKTIF, ubah semua lokasi yang 'aktif' saat ini menjadi 'nonaktif'
            if ($request->status == 'aktif') {
                Lokasi::where('status', 'aktif')->update(['status' => 'nonaktif']);
            }

            Lokasi::create([
                'nama_lokasi' => $request->nama_lokasi,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'radius' => $request->radius,
                'status' => $request->status,
            ]);
        });

        return back()->with(
            'success',
            'Lokasi berhasil ditambahkan'
        );
    }

    // update lokasi
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_lokasi' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'radius' => 'required|numeric',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        $lokasi = Lokasi::findOrFail($id);

        DB::transaction(function () use ($request, $lokasi) {
            
            // JIKA status diubah menjadi AKTIF, matikan lokasi aktif lainnya
            if ($request->status == 'aktif') {
                Lokasi::where('status', 'aktif')
                    ->where($lokasi->getKeyName(), '!=', $lokasi->getKey()) // Mengabaikan lokasi yang sedang di-edit saat ini
                    ->update(['status' => 'nonaktif']);
            }

            $lokasi->update([
                'nama_lokasi' => $request->nama_lokasi,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'radius' => $request->radius,
                'status' => $request->status,
            ]);
        });

        return back()->with(
            'success',
            'Lokasi berhasil diperbarui'
        );
    }

    // hapus lokasi
    public function destroy($id)
    {
        Lokasi::findOrFail($id)->delete();

        return back()->with(
            'success',
            'Lokasi berhasil dihapus'
        );
    }
}