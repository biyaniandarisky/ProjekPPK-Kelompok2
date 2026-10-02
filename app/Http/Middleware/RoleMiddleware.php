<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle request berdasarkan role.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  Role yang diizinkan (admin, petugas, pengguna)
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        // 1. Cek login
        if (!$user) {
            return redirect()->route('login')
                ->with('info', 'Silakan login terlebih dahulu.');
        }

        // 2. Cek verifikasi khusus pengguna
        if ($user->role === 'pengguna' && $user->status_verifikasi !== 'verified') {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $pesan = match ($user->status_verifikasi) {
                'pending'   => 'Akun Anda sedang diverifikasi oleh admin. Anda belum dapat login sampai akun disetujui.',
                'rejected'  => 'Verifikasi akun Anda ditolak. Silakan hubungi admin kampus.',
                'suspended' => 'Akun Anda diblokir. Silakan hubungi admin kampus.',
                default     => 'Akun Anda tidak dapat digunakan. Silakan hubungi admin kampus.',
            };

            return redirect()->route('login')->withErrors(['email' => $pesan]);
        }

        // 3. Cek role
        if (!in_array($user->role, $roles)) {
            // Log aktivitas akses ditolak (kalau model ActivityLog ada)
            if (class_exists(\App\Models\ActivityLog::class)) {
                \App\Models\ActivityLog::create([
                    'user_id'     => $user->id,
                    'action'      => 'access_denied',
                    'description' => "User {$user->name} ({$user->role}) mencoba akses route " . $request->path(),
                    'ip_address'  => $request->ip(),
                    'user_agent'  => $request->userAgent(),
                ]);
            }

            // Log ke file log Laravel
            Log::warning('Access denied', [
                'user_id' => $user->id,
                'role'    => $user->role,
                'route'   => $request->path(),
                'allowed' => $roles,
            ]);

            abort(403, 'Akses tidak diizinkan untuk peran ini.');
        }

        return $next($request);
    }
}