<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ], [
            'email.required'    => 'Email wajib diisi.',
            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal 6 karakter.',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $user = Auth::user();

            // Verifikasi status untuk pengguna
            if ($user->role === 'pengguna') {
                if ($user->status_verifikasi === 'pending') {
                    Auth::logout();
                    return back()->withErrors(['email' => 'Akun Anda belum diverifikasi admin']);
                }
                if ($user->status_verifikasi === 'rejected') {
                    Auth::logout();
                    return back()->withErrors(['email' => 'Akun Anda ditolak']);
                }
            }

            // Redirect otomatis sesuai peran
            return match ($user->role) {
                'admin'    => redirect()->route('admin.dashboard'),
                'petugas'  => redirect()->route('petugas.dashboard'),
                'pengguna' => redirect()->route('pengguna.dashboard'),
                default    => redirect()->route('landing'),
            };
        }

        return back()->withErrors([
            'email' => 'Kombinasi email atau password salah.',
        ]);
    }

    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ], [
            'name.required'     => 'Nama lengkap wajib diisi.',
            'email.required'    => 'Email institusi wajib diisi.',
            'email.unique'      => 'Email ini sudah terdaftar di sistem.',
            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal 6 karakter.',
        ]);

        User::create([
            'name'              => $validated['name'],
            'email'             => $validated['email'],
            'password'          => Hash::make($validated['password']),
            'role'              => 'pengguna',
            'status_verifikasi' => 'pending', // Menunggu verifikasi admin
        ]);

        return redirect()->route('login')->with('success', 'Registrasi mandiri berhasil! Akun Anda sedang menunggu verifikasi oleh Admin kampus.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('landing')->with('info', 'Anda telah berhasil keluar dari sistem.');
    }
}