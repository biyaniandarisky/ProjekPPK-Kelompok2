<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Notification;

class AuthController extends Controller
{
    /** Halaman Login (tab "Login" aktif). */
    public function showLoginForm()
    {
        return view('auth.index', ['tab' => 'login']);
    }

    /** Halaman Registrasi (tab "Registrasi Mandiri" aktif). */
    public function showRegisterForm()
    {
        return view('auth.index', ['tab' => 'register']);
    }

    /**
     * Proses Login.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|min:8',
        ], [
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal 8 karakter.',
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // Pengguna yang belum diverifikasi admin TIDAK boleh masuk
            if ($user->role === 'pengguna') {
                if ($user->status_verifikasi === 'pending') {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return redirect()->route('login')
                        ->withInput($request->only('email'))
                        ->withErrors(['email' => 'Akun Anda sedang diverifikasi oleh admin. Anda belum dapat login sampai akun disetujui.']);
                }
                if ($user->status_verifikasi === 'rejected') {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return redirect()->route('login')
                        ->withInput($request->only('email'))
                        ->withErrors(['email' => 'Verifikasi akun Anda ditolak. Silakan hubungi admin kampus.']);
                }
            }

            // Session baru setelah login (mencegah session fixation)
            $request->session()->regenerate();

            // Redirect balik kalau ada parameter redirect
            if ($request->filled('redirect')) {
                return redirect($request->redirect);
            }

            return match ($user->role) {
                'admin'    => redirect()->route('admin.dashboard'),
                'petugas'  => redirect()->route('petugas.dashboard'),
                'pengguna' => redirect()->route('pengguna.dashboard'),
                default    => redirect()->route('landing'),
            };
        }

        return back()->withInput($request->only('email'))->withErrors([
            'email' => 'Kombinasi email atau password salah.',
        ]);
    }

    /**
     * Proses Registrasi Mandiri (hanya untuk pengguna).
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:100',
            'nim_nip'  => 'required|string|max:30|unique:users,nim_nip',
            'email'    => 'required|email|max:255|unique:users,email',
            'no_hp'    => ['nullable', 'regex:/^(\+62|62|0)8[0-9]{7,13}$/'],
            'password' => 'required|min:8|confirmed',
            'ktm'      => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ], [
            'name.required'      => 'Nama lengkap wajib diisi.',
            'nim_nip.required'   => 'NIM / NIP wajib diisi.',
            'nim_nip.unique'     => 'NIM / NIP ini sudah terdaftar di sistem.',
            'email.required'     => 'Email wajib diisi.',
            'email.email'        => 'Format email tidak valid.',
            'email.unique'       => 'Email ini sudah terdaftar di sistem.',
            'no_hp.regex'        => 'Format No. HP tidak valid (contoh: 081234567890).',
            'password.required'  => 'Password wajib diisi.',
            'password.min'       => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok dengan password.',
            'ktm.mimes'          => 'Berkas KTM / KTP harus berformat JPG, PNG, atau PDF.',
            'ktm.max'            => 'Ukuran berkas KTM / KTP maksimal 2 MB.',
        ]);

        // KTM/KTP disimpan di disk PRIVATE
        $ktmPath = $request->hasFile('ktm')
            ? $request->file('ktm')->store('ktm', 'local')
            : null;

        User::create([
            'name'              => $validated['name'],
            'nim_nip'           => $validated['nim_nip'],
            'email'             => $validated['email'],
            'no_hp'             => $validated['no_hp'] ?? null,
            'ktm_path'          => $ktmPath,
            'password'          => Hash::make($validated['password']),
            'role'              => 'pengguna',
            'status_verifikasi' => 'pending',
        ]);

        return redirect()->route('login')->with('pending_notice', true);
    }

    /**
     * Logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('landing')->with('info', 'Anda telah berhasil keluar dari sistem.');
    }
}