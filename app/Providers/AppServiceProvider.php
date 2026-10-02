<?php

namespace App\Providers;

use App\Models\Notification;
use App\Models\Report;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /**
         * View Composer untuk layout utama.
         * Kirim data notifikasi & pending count sesuai role user.
         */
        View::composer('layouts.app', function ($view) {
            $user = auth()->user();

            if (!$user) {
                // Pengunjung: tidak ada notifikasi
                $view->with([
                    'notifCount'      => 0,
                    'pendingUsersCount' => 0,
                ]);
                return;
            }

            $notifCount = 0;
            $pendingUsersCount = 0;

            if ($user->isPetugas()) {
                // Petugas: hitung reservasi pending + laporan baru
                $notifCount = Reservation::where('status', 'pending')->count()
                    + Report::where('status_laporan', 'baru')->count();
            } elseif ($user->isAdmin()) {
                // Admin: hitung user pending + notifikasi pribadi
                $pendingUsersCount = User::where('status_verifikasi', 'pending')->count();
                $notifCount = $pendingUsersCount
                    + Notification::where('user_id', $user->id)
                        ->where('is_read', false)->count();
            } elseif ($user->isPengguna()) {
                // Pengguna: hitung notifikasi pribadi belum dibaca
                $notifCount = Notification::where('user_id', $user->id)
                    ->where('is_read', false)->count();
            }

            $view->with([
                'notifCount'        => $notifCount,
                'pendingUsersCount' => $pendingUsersCount,
            ]);
        });
    }
}