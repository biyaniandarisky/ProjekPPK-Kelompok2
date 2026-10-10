<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\PetugasController;
use App\Http\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES (Pengunjung — tanpa login)
|--------------------------------------------------------------------------
*/

// Landing page
Route::get('/', [LandingController::class, 'index'])->name('landing');

// Daftar fasilitas (pengunjung & semua role)
Route::get('/fasilitas', [FacilityController::class, 'index'])->name('facilities.index');
Route::get('/fasilitas/{id}', [FacilityController::class, 'show'])->name('facilities.show');

// Cek ketersediaan slot (AJAX)
Route::get('/fasilitas/{id}/ketersediaan', [LandingController::class, 'checkAvailability'])
    ->name('fasilitas.ketersediaan');

// Intent booking (dari popup landing page)
Route::post('/pesan/intent', [LandingController::class, 'bookingIntent'])
    ->name('booking.intent');

/*
|--------------------------------------------------------------------------
| AUTH ROUTES (Login, Register, Logout)
|--------------------------------------------------------------------------
| Registrasi mandiri HANYA untuk pengguna (mahasiswa/dosen/staf).
| Petugas tidak punya registrasi mandiri: akunnya dibuat oleh admin.
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| GROUP: PENGGUNA (Mahasiswa / Dosen / Staf)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:pengguna'])
    ->prefix('pengguna')
    ->name('pengguna.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [PenggunaController::class, 'dashboard'])
            ->name('dashboard');

        // === RESERVASI ===
        Route::get('/reservasi', [PenggunaController::class, 'reservasiIndex'])
            ->name('reservasi.index');
        Route::get('/reservasi/buat', [PenggunaController::class, 'createReservasi'])
            ->name('reservasi.create');
        Route::post('/reservasi', [PenggunaController::class, 'storeReservasi'])
            ->middleware('throttle:10,1')
            ->name('reservasi.store');
        Route::post('/reservasi/{id}/batal', [PenggunaController::class, 'cancelReservasi'])
            ->name('reservasi.cancel');
        Route::get('/reservasi/{id}/cetak', [PenggunaController::class, 'cetakReservasi'])
            ->name('reservasi.cetak');

        // === LAPORAN KENDALA ===
        Route::get('/laporan', [PenggunaController::class, 'laporanIndex'])
            ->name('laporan.index');
        Route::get('/laporan/buat', [PenggunaController::class, 'createLaporan'])
            ->name('laporan.create');
        Route::post('/laporan', [PenggunaController::class, 'storeLaporan'])
            ->middleware('throttle:10,1')
            ->name('laporan.store');

        // === NOTIFIKASI ===
        Route::get('/notifikasi', [PenggunaController::class, 'indexNotifikasi'])
            ->name('notifikasi.index');
    });

/*
|--------------------------------------------------------------------------
| GROUP: PETUGAS
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:petugas'])
    ->prefix('petugas')
    ->name('petugas.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [PetugasController::class, 'dashboard'])
            ->name('dashboard');

        // === RESERVASI ===
        Route::get('/reservasi', [PetugasController::class, 'reservasiIndex'])
            ->name('reservasi.index');
        Route::post('/reservasi/{id}/approve', [PetugasController::class, 'approveReservasi'])
            ->name('reservasi.approve');
        Route::post('/reservasi/{id}/reject', [PetugasController::class, 'rejectReservasi'])
            ->name('reservasi.reject');
        Route::post('/reservasi/{id}/emergency-cancel', [PetugasController::class, 'emergencyCancel'])
            ->name('reservasi.emergency_cancel');

        // === LAPORAN KENDALA ===
        Route::get('/laporan', [PetugasController::class, 'laporanIndex'])
            ->name('laporan.index');
        Route::post('/laporan/{id}/process', [PetugasController::class, 'processLaporan'])
            ->name('laporan.process');
        Route::post('/laporan/{id}/resolve', [PetugasController::class, 'resolveLaporan'])
            ->name('laporan.resolve');
        Route::post('/laporan/{id}/reject', [PetugasController::class, 'rejectLaporan'])
            ->name('laporan.reject');

        // === FASILITAS ===
        Route::get('/fasilitas', [PetugasController::class, 'fasilitasIndex'])
            ->name('fasilitas.index');
        Route::post('/fasilitas/{id}/status', [PetugasController::class, 'updateFacilityStatus'])
            ->name('fasilitas.status');

        // === NOTIFIKASI ===
        Route::get('/notifikasi', [PetugasController::class, 'notifikasiIndex'])
            ->name('notifikasi.index');
    });

/*
|--------------------------------------------------------------------------
| GROUP: ADMIN
|--------------------------------------------------------------------------
| Semua halaman admin adalah tab di satu view (admin.dashboard);
| route GET lain hanya redirect ke tab yang sesuai (?page=...).
*/

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Dashboard (semua tab)
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        // ===== REKAP & EXPORT (okupansi + frekuensi kerusakan per fasilitas/lokasi) =====
        Route::get('/rekap', [AdminController::class, 'rekapIndex'])->name('rekap.index');
        Route::get('/rekap/okupansi', [AdminController::class, 'rekapIndex'])->name('rekap.okupansi');
        Route::get('/rekap/kerusakan', [AdminController::class, 'rekapIndex'])->name('rekap.kerusakan');
        Route::get('/rekap/export/{format}', [AdminController::class, 'exportFullData'])
            ->whereIn('format', ['csv', 'excel', 'pdf'])
            ->name('rekap.export');

        // ===== AKUN: DAFTAR LANGSUNG (petugas & pengguna) =====
        Route::get('/users', [AdminController::class, 'usersIndex'])->name('users.index');
        Route::get('/users/create', [AdminController::class, 'usersCreate'])->name('users.create');
        Route::post('/petugas', [AdminController::class, 'storePetugas'])->name('petugas.store');
        Route::delete('/petugas/{id}', [AdminController::class, 'destroyPetugas'])->name('petugas.destroy');
        Route::post('/pengguna', [AdminController::class, 'storePenggunaDirect'])->name('pengguna.store');
        Route::delete('/pengguna/{id}', [AdminController::class, 'destroyPengguna'])->name('pengguna.destroy');
        Route::post('/register-user', [AdminController::class, 'storeUserByAdmin'])->name('users.store'); // generik (opsional)

        // ===== VERIFIKASI REGISTRASI MANDIRI PENGGUNA =====
        Route::post('/users/{id}/verify', [AdminController::class, 'verifyUser'])->name('users.verify');
        Route::post('/users/{id}/reject', [AdminController::class, 'rejectUser'])->name('users.reject');
        Route::get('/users/{id}/ktm', [AdminController::class, 'showKtm'])->name('users.ktm');

        // ===== KELOLA FASILITAS (tambah / edit / nonaktifkan) =====
        Route::get('/facilities', [AdminController::class, 'facilitiesIndex'])->name('facilities.index');
        Route::get('/facilities/create', [AdminController::class, 'facilitiesIndex'])->name('facilities.create');
        Route::post('/facilities', [AdminController::class, 'storeFacility'])->name('facilities.store');
        Route::put('/facilities/{id}', [AdminController::class, 'updateFacility'])->name('facilities.update');
        Route::post('/facilities/{id}/toggle', [AdminController::class, 'toggleFacilityStatus'])->name('facilities.toggle');
    });