<?php

namespace App\Providers;

use App\Models\Report;
use App\Models\Reservation;
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
        // Jumlah notifikasi (badge lonceng) untuk navbar, khusus role petugas.
        View::composer('layouts.app', function ($view) {
            $user = auth()->user();

            if ($user && $user->isPetugas()) {
                $view->with([
                    'notifCount' => Reservation::where('status', 'pending')->count()
                        + Report::where('status_laporan', 'baru')->count(),
                ]);
            }
        });
    }
}
