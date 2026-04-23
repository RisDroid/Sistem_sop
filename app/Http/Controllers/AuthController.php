<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Support\LoginLogger;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credential = (string) ($request->input('username') ?: $request->input('email'));

        $request->merge(['username' => $credential]);

        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $user = User::query()
            ->where('username', $credential)
            ->when(Schema::hasColumn('tb_user', 'email'), function ($query) use ($credential) {
                $query->orWhere('email', $credential);
            })
            ->first();

        if ($user && ($user->password === $request->password || Hash::check($request->password, $user->password))) {
            Auth::login($user);

            $request->session()->regenerate();

            LoginLogger::log('login', $user, $request, ['username' => $user->username]);

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            LoginLogger::log('logout', $user, $request, ['username' => $user->username]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
