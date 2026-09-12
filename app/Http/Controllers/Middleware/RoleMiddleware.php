<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Cek verifikasi untuk role Pengguna
        if ($user->role === 'pengguna' && $user->status_verifikasi !== 'verified') {
            auth()->logout();
            $pesan = $user->status_verifikasi === 'pending'
                ? 'Akun Anda belum diverifikasi admin'
                : 'Akun Anda ditolak';

            return redirect()->route('login')->withErrors(['email' => $pesan]);
        }

        if (!in_array($user->role, $roles)) {
            abort(403, 'Akses tidak diizinkan untuk peran ini.');
        }

        return $next($request);
    }
}