<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    // tampil halaman login
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // proses login
    public function login(Request $request)
    {
        // validasi
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        // ambil user dari database
        $user = User::where('username', $request->username)
            ->where('status_akun', 'aktif')
            ->first();

        // kalau user tidak ditemukan
        if (!$user) {
            return back()->with(
                'error',
                'Username tidak ditemukan'
            );
        }

        // cek password
        if (!Hash::check($request->password, $user->password)) {

            return back()->with(
                'error',
                'Password salah'
            );
        }
        // HANYA TIM KURIKULUM YANG BOLEH LOGIN WEB
        if ($user->role !== 'tim_kurikulum') {
            return back()->with(
                'error',
                'Anda tidak berhak mengakses halaman ini. Silakan gunakan aplikasi mobile Presentri.'
            );
        }
        
        // login manual
        Auth::login($user);
        
        // regenerate session
        $request->session()->regenerate();
        
        // redirect dashboard tim kurikulum
        return redirect('/dashboard');


        // login manual
        Auth::login($user);

        // regenerate session
        $request->session()->regenerate();

    }

    // logout
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }
}