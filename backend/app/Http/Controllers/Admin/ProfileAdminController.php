<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\User;

class ProfileAdminController extends Controller
{
    /**
     * Menampilkan halaman edit profil
     */
    public function edit()
    {
        $user = Auth::user();
        return view('admin.pages.profile.edit', compact('user'));
    }

    /**
     * Memproses update data profil
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->id_user, 'id_user')],
            'no_hp' => ['nullable', 'string', 'max:15'],
            'password_baru' => ['nullable', 'string', 'min:6', 'confirmed'],
        ], [
            'username.unique' => 'Username ini sudah digunakan oleh pengguna lain.',
            'password_baru.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        // Update data basic
        $user->nama = $request->nama;
        $user->username = $request->username;
        $user->no_hp = $request->no_hp;

        // Jika user mengisi password baru
        if ($request->filled('password_baru')) {
            $user->password = Hash::make($request->password_baru);
        }

        $user->save();

        return redirect()->back()->with('success', 'Profil Anda berhasil diperbarui!');
    }
}