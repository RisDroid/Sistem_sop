<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        // 2. Cari user di tb_user yang username DAN password-nya COCOK (Plain Text)
        $user = User::where('username', $request->username)
                    ->where('password', $request->password) // Cek langsung teks biasa
                    ->first();

        // 3. Jika user ketemu
        if ($user) {
            // Login-kan user ke sistem secara manual
            Auth::login($user);

            $request->session()->regenerate();

            // 4. Logika Redirect Berdasarkan Role
            if ($user->role === 'Admin') {
                return redirect()->intended('/admin/dashboard');
            } elseif ($user->role === 'Operator') {
                return redirect()->intended('/operator/dashboard');
            } else {
                return redirect()->intended('/viewer/dashboard');
            }
        }

        // 5. Jika gagal, balikkan ke login dengan pesan error
        return back()->withErrors([
            'username' => 'Username atau Password salah (Plain Text Mode).',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
