<?php

use App\Http\Middleware\RoleMiddleware;
use App\Exceptions\InvalidSlotException;
use App\Exceptions\ReservationConflictException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    /* =========================================================
     |  ROUTING
     ========================================================= */
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        // api: __DIR__ . '/../routes/api.php', // ← buka kalau butuh API
    )

    /* =========================================================
     |  MIDDLEWARE
     ========================================================= */
    ->withMiddleware(function (Middleware $middleware): void {
        // Alias middleware
        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);

        // Redirect guest (belum login) ke halaman login
        $middleware->redirectGuestsTo(fn () => route('login'));

        // Redirect user yang sudah login (akses /login) ke dashboard
        $middleware->redirectUsersTo(function () {
            $user = auth()->user();
            if (!$user) {
                return '/';
            }
            return match ($user->role) {
                'admin'    => route('admin.dashboard'),
                'petugas'  => route('petugas.dashboard'),
                'pengguna' => route('pengguna.dashboard'),
                default    => '/',
            };
        });

        // Trust proxies (untuk production / di belakang load balancer)
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO
        );

        // CSRF exception (kalau butuh exclude route tertentu)
        $middleware->validateCsrfTokens(except: [
            // 'stripe/*',
            // 'webhook/*',
        ]);

        // Middleware global tambahan (opsional)
        // $middleware->append(\App\Http\Middleware\LogRequests::class);

        // Middleware untuk group 'web' (opsional)
        // $middleware->web(append: [
        //     \App\Http\Middleware\SetLocale::class,
        // ]);
    })

    /* =========================================================
     |  EXCEPTION HANDLING
     ========================================================= */
    ->withExceptions(function (Exceptions $exceptions): void {
        // Render JSON untuk API / request yang expect JSON
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson()
        );

        // Custom render untuk InvalidSlotException
        $exceptions->render(function (InvalidSlotException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'type'    => 'invalid_slot',
                ], 422);
            }
            return back()->withInput()->withErrors(['slot' => $e->getMessage()]);
        });

        // Custom render untuk ReservationConflictException
        $exceptions->render(function (ReservationConflictException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'type'    => 'reservation_conflict',
                ], 409);
            }
            return back()->withInput()->withErrors(['conflict' => $e->getMessage()]);
        });

        // Custom render untuk 403 (Akses Ditolak)
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak.',
                ], 403);
            }
            return response()->view('errors.403', [], 403);
        });

        // Custom render untuk 404 (Tidak Ditemukan)
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan.',
                ], 404);
            }
            return response()->view('errors.404', [], 404);
        });

        // Custom render untuk 500 (Server Error)
        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => app()->environment('production')
                        ? 'Terjadi kesalahan pada server.'
                        : $e->getMessage(),
                ], 500);
            }
            // Biarkan Laravel handle default untuk web
            return null;
        });
    })

    ->create();