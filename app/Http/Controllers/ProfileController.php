<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Halaman profil pengguna yang sedang login.
     */
    public function index()
    {
        return view('profile.index');
    }

    /**
     * Ganti password akun sendiri (dengan verifikasi password lama).
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password'      => 'required|string',
            'password'              => 'required|min:8|confirmed',
            'password_confirmation' => 'required|string',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password lama tidak sesuai.']);
        }

        if ($request->current_password === $request->password) {
            return back()->withErrors(['password' => 'Password baru tidak boleh sama dengan password lama.']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Password berhasil diperbarui.');
    }
}
