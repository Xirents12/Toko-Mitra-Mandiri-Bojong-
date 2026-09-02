<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return Auth::check() ? redirect()->route('landing') : view('auth.login');
    }

    public function showRegister()
    {
        return Auth::check() ? redirect()->route('landing') : view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => $request->password, // cast 'hashed' di model User otomatis meng-hash
            'role'      => 'kasir', // default role pendaftar (paling aman)
            'is_active' => true,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('landing');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            if (!Auth::user()->is_active) {
                Auth::logout();
                return back()->withErrors(['email' => 'Akun Anda tidak aktif. Hubungi administrator.']);
            }
            $request->session()->regenerate();
            return redirect()->intended(route('landing'));
        }

        return back()->withErrors(['email' => 'Email atau password tidak sesuai.'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('landing');
    }
}
