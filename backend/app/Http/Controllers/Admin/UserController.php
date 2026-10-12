<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\GuruExport;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    // =======================
    // HALAMAN USER
    // =======================
    public function index(Request $request)
    {
        $query = User::query();

        // search
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%')
                    ->orWhere('username', 'like', '%' . $request->search . '%');
            });
        }

        // filter role
        if ($request->role == 'guru') {
            $query->where('role', 'guru')
                ->where('jenis_gtk', 'Guru');
        } elseif ($request->role == 'tenaga') {
            $query->where('role', 'guru')
                ->where('jenis_gtk', 'Tenaga Kependidikan');
        } elseif ($request->role) {
            $query->where('role', $request->role);
        }

        $users = $query->orderBy('nama', 'asc')
            ->paginate(10)
            ->withQueryString();

        return view('admin.pages.user.index', compact('users'));
    }

    // =======================
    // TAMBAH USER
    // =======================
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required',
            'username' => 'required|unique:users,username',
            'password' => 'required|min:6',
            'role' => 'required',
            'nip' => 'nullable',
            'mata_pelajaran' => 'nullable',
            'no_hp' => 'nullable',
        ]);

        // kepala sekolah cuma 1
        if ($request->role == 'kepala_sekolah') {
            $cek = User::where('role', 'kepala_sekolah')->exists();

            if ($cek) {
                return back()->with('error', 'Kepala sekolah hanya boleh 1 akun');
            }
        }

        User::create([
            'nama' => $request->nama,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'jenis_gtk' => $request->role == 'guru' ? $request->jenis_gtk : null,
            'nip' => $request->nip,
            'mata_pelajaran' => $request->mata_pelajaran,
            'no_hp' => $request->no_hp,
            'status_akun' => $request->status_akun
        ]);

        return back()->with('success', 'User berhasil ditambahkan');
    }

    // =======================
    // UPDATE USER
    // =======================
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'nama' => 'required',
            'username' => 'required',
            'role' => 'required',
        ]);

        // validasi kepala sekolah
        if ($request->role == 'kepala_sekolah' && User::where('role', 'kepala_sekolah')->where('id_user', '!=', $id)->exists()) {
            return back()->with('error', 'Kepala sekolah hanya boleh 1 akun');
        }

        $data = [
            'nama' => $request->nama,
            'username' => $request->username,
            'role' => $request->role,
            'jenis_gtk' => $request->role == 'guru' ? $request->jenis_gtk : null,
            'nip' => $request->nip,
            'mata_pelajaran' => $request->mata_pelajaran,
            'no_hp' => $request->no_hp,
            'status_akun' => $request->status_akun,
        ];
        
        // Jika role guru/GTK, simpan jenis_gtk, jika bukan guru maka set null
        if ($request->role == 'guru') {
            $data['jenis_gtk'] = $request->jenis_gtk ?? 'Guru';
        } else {
            $data['jenis_gtk'] = null;
        }

        // kalau password diisi
        if ($request->password) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return back()->with('success', 'User berhasil diupdate');
    }

    // =======================
    // HAPUS USER
    // =======================
    public function delete($id)
    {
        User::findOrFail($id)->delete();

        return back()->with('success', 'User berhasil dihapus');
    }

    // =======================
    // RESET DEVICE
    // =======================
    public function resetDevice($id)
    {
        $user = User::findOrFail($id);
        $user->device_id = null;
        $user->save();

        return back()->with('success', 'Perangkat ' . $user->nama . ' berhasil direset');
    }

    // =======================
    // DOWNLOAD PDF
    // =======================
    public function downloadPdf(Request $request)
    {
        // 1. Ambil query data user seperti pada fungsi index()
        $query = User::query();

        // Logika Search (Sama persis dengan index)
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%')
                    ->orWhere('username', 'like', '%' . $request->search . '%');
            });
        }

        // Logika Filter Role & GTK (Sama persis dengan index)
        if ($request->role == 'guru') {
            $query->where('role', 'guru')->where('jenis_gtk', 'Guru');
        } elseif ($request->role == 'tenaga') {
            $query->where('role', 'guru')->where('jenis_gtk', 'Tenaga Kependidikan');
        } elseif ($request->role) {
            $query->where('role', $request->role);
        }

        // 2. Ambil data user yang sudah terfilter
        $users = $query->latest()->get();

        // 3. Render ke view blade PDF yang sudah kita buat sebelumnya
        $pdf = Pdf::loadView('admin.pages.user.pdf', compact('users'))
            ->setPaper('a4', 'portrait'); // Set ukuran kertas A4 Tegak

        // 4. Unduh file dengan nama yang dinamis
        return $pdf->download('Rekap_Data_User_GTK_' . date('d-m-Y') . '.pdf');
    }

    public function downloadExcel(Request $request)
{
    return Excel::download(new GuruExport($request), 'Rekap_Data_Guru.xlsx');
}
}