<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HariLibur;

class HariLiburController extends Controller
{
    // tampil halaman
    public function index(Request $request)
    {
        $query = HariLibur::query();

        // search
        if ($request->search) {
            $query->where(
                'keterangan',
                'like',
                '%' . $request->search . '%'
            );
        }

        $hariLiburs = $query
            ->latest()
            ->paginate(10);

        return view(
            'admin.pages.hari_libur.index',
            compact('hariLiburs')
        );
    }

    // tambah
    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date|unique:hari_libur,tanggal',
            'keterangan' => 'required'
        ]);

        HariLibur::create([
            'tanggal' => $request->tanggal,
            'keterangan' => $request->keterangan
        ]);

        return back()->with(
            'success',
            'Hari libur berhasil ditambahkan'
        );
    }

    // update
    public function update(Request $request, $id)
    {
        $hariLibur = HariLibur::findOrFail($id);

        $request->validate([
            'tanggal' =>
                'required|date|unique:hari_libur,tanggal,' .
                $id .
                ',id_hari_libur',

            'keterangan' => 'required'
        ]);

        $hariLibur->update([
            'tanggal' => $request->tanggal,
            'keterangan' => $request->keterangan
        ]);

        return back()->with(
            'success',
            'Hari libur berhasil diupdate'
        );
    }

    // hapus
    public function delete($id)
    {
        HariLibur::findOrFail($id)->delete();

        return back()->with(
            'success',
            'Hari libur berhasil dihapus'
        );
    }
}