<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->paginate(15);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'role'     => 'required|in:admin,kasir,gudang',
            'is_active'=> 'nullable|boolean',
        ]);

        User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role'      => $request->role,
            'is_active' => $request->is_active ?? 1,
        ]);

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'      => 'required|max:255',
            'email'     => "required|email|unique:users,email,{$user->id}",
            'role'      => 'required|in:admin,kasir,gudang',
            'password'  => 'nullable|min:8',
            'is_active' => 'nullable|boolean',
        ]);

        $data = [
            'name'      => $request->name,
            'email'     => $request->email,
            'role'      => $request->role,
            'is_active' => $request->is_active ?? $user->is_active,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

  public function destroy(User $user)
{
    /** @var \App\Models\User $authUser */
    $authUser = \Illuminate\Support\Facades\Auth::user();

    if ($user->id === $authUser->id) {
        return redirect()->route('users.index')->with('error', 'Tidak bisa menghapus akun sendiri.');
    }

    $user->delete();

    return redirect()->route('users.index')->with('success', 'Pengguna berhasil dihapus.');
}
}