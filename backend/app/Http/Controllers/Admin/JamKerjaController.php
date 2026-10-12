<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JamKerja;
use Illuminate\Support\Str;

class JamKerjaController extends Controller
{
    public function index(Request $request)
    {
        $query = JamKerja::query();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('kode_jk', 'like', '%' . $request->search . '%')
                  ->orWhere('nama_jk', 'like', '%' . $request->search . '%');
            });
        }

        $jamKerjas = $query->latest()->paginate(10);

        return view('admin.pages.jam_kerja.index', compact('jamKerjas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'mode' => 'required',
            'nama_jk' => 'required',
            'jam_masuk' => 'required',
            'batas_jam_masuk' => 'required',
            'jam_pulang' => 'required',
            'status'=> 'required',
        ]);

        // Jika jadwal baru ini diset AKTIF, nonaktifkan jadwal lain terlebih dahulu
        if ($request->status === 'aktif') {
            JamKerja::where('status', 'aktif')->update(['status' => 'nonaktif']);
        }

        $kodeJk = 'JK-' . strtoupper(Str::random(5));

        JamKerja::create([
            'kode_jk' => $kodeJk,
            'mode' => $request->mode,
            'nama_jk' => $request->nama_jk,
            'jam_masuk' => $request->jam_masuk,
            'batas_jam_masuk' => $request->batas_jam_masuk,
            'jam_pulang' => $request->jam_pulang,
            'status'=> $request->status,
        ]);

        return back()->with('success', 'Jam kerja berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $jamKerja = JamKerja::findOrFail($id);

        $request->validate([
            'mode' => 'required',
            'nama_jk' => 'required',
            'jam_masuk' => 'required',
            'batas_jam_masuk' => 'required',
            'jam_pulang' => 'required',
            'status'=> 'required',
        ]);

        // Jika jadwal ini diupdate menjadi AKTIF, nonaktifkan jadwal lain terlebih dahulu
        if ($request->status === 'aktif') {
            JamKerja::where('id_jam_kerja', '!=', $id)->where('status', 'aktif')->update(['status' => 'nonaktif']);
        }

        $jamKerja->update([
            'mode' => $request->mode,
            'nama_jk' => $request->nama_jk,
            'jam_masuk' => $request->jam_masuk,
            'batas_jam_masuk' => $request->batas_jam_masuk,
            'jam_pulang' => $request->jam_pulang,
            'status'=> $request->status,
        ]);

        return back()->with('success', 'Jam kerja berhasil diupdate');
    }

    public function delete($id)
    {
        JamKerja::findOrFail($id)->delete();
        return back()->with('success', 'Jam kerja berhasil dihapus');
    }
}