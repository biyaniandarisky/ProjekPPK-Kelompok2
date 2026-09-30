<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\PetugasController;
use App\Http\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| Landing Page & Ketersediaan Slot (Pengunjung & Semua Aktor)
|--------------------------------------------------------------------------
*/
Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::get('/fasilitas/{id}/ketersediaan', [LandingController::class, 'checkAvailability'])
    ->name('fasilitas.ketersediaan');
Route::post('/pesan/intent', [LandingController::class, 'bookingIntent'])
    ->name('booking.intent');

/*
|--------------------------------------------------------------------------
| Autentikasi (Login, Registrasi, Logout)
|--------------------------------------------------------------------------
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
| Group: Pengguna (Mahasiswa, Dosen, Staf)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:pengguna'])
    ->prefix('pengguna')
    ->name('pengguna.')
    ->group(function () {

        Route::get('/dashboard', [PenggunaController::class, 'dashboard'])->name('dashboard');

        // Reservasi
        Route::get('/reservasi', [PenggunaController::class, 'reservasiIndex'])->name('reservasi.index');
        Route::get('/reservasi/buat', [PenggunaController::class, 'createReservasi'])->name('reservasi.create');
        Route::post('/reservasi', [PenggunaController::class, 'storeReservasi'])->name('reservasi.store');
        Route::post('/reservasi/{id}/batal', [PenggunaController::class, 'cancelReservasi'])->name('reservasi.cancel');

        // Laporan Kendala
        Route::get('/laporan', [PenggunaController::class, 'laporanIndex'])->name('laporan.index');
        Route::get('/laporan/buat', [PenggunaController::class, 'createLaporan'])->name('laporan.create');
        Route::post('/laporan', [PenggunaController::class, 'storeLaporan'])->name('laporan.store');

        // Notifikasi
        Route::get('/notifikasi', [PenggunaController::class, 'indexNotifikasi'])->name('notifikasi.index');

        Route::get('/reservasi/{id}/cetak', [PenggunaController::class, 'cetakReservasi'])
            ->name('reservasi.cetak');
    });

/*
|--------------------------------------------------------------------------
| Group: Petugas
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:petugas'])
    ->prefix('petugas')
    ->name('petugas.')
    ->group(function () {

        Route::get('/dashboard', [PetugasController::class, 'dashboard'])->name('dashboard');
        Route::get('/reservasi', [PetugasController::class, 'reservasiIndex'])->name('reservasi.index');
        Route::get('/laporan', [PetugasController::class, 'laporanIndex'])->name('laporan.index');
        Route::get('/fasilitas', [PetugasController::class, 'fasilitasIndex'])->name('fasilitas.index');
        Route::get('/notifikasi', [PetugasController::class, 'notifikasiIndex'])->name('notifikasi.index');

        // Aksi Reservasi
        Route::post('/reservasi/{id}/approve', [PetugasController::class, 'approveReservasi'])->name('reservasi.approve');
        Route::post('/reservasi/{id}/reject', [PetugasController::class, 'rejectReservasi'])->name('reservasi.reject');
        Route::post('/reservasi/{id}/emergency-cancel', [PetugasController::class, 'emergencyCancel'])->name('reservasi.emergency_cancel');

        // Aksi Laporan Kendala
        Route::post('/laporan/{id}/process', [PetugasController::class, 'processLaporan'])->name('laporan.process');
        Route::post('/laporan/{id}/resolve', [PetugasController::class, 'resolveLaporan'])->name('laporan.resolve');

        // Status Fasilitas oleh Petugas
        Route::post('/fasilitas/{id}/status', [PetugasController::class, 'updateFacilityStatus'])->name('fasilitas.status');
    });

/*
|--------------------------------------------------------------------------
| Group: Admin
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        // Rekap & Laporan
        Route::get('/rekap/okupansi', [AdminController::class, 'rekapOkupansi'])->name('rekap.okupansi');
        Route::get('/rekap/kerusakan', [AdminController::class, 'rekapKerusakan'])->name('rekap.kerusakan');
        Route::get('/rekap/export/{format}', [AdminController::class, 'exportFullData'])->name('rekap.export');

        // Kelola Pengguna & Verifikasi (Diperlengkap dengan Index)
        Route::get('/users', [AdminController::class, 'usersIndex'])->name('users.index'); // [TAMBAHAN] Tampil daftar user
        Route::post('/register-user', [AdminController::class, 'storeUserByAdmin'])->name('users.store');
        Route::post('/users/{id}/verify', [AdminController::class, 'verifyUser'])->name('users.verify');
        Route::post('/users/{id}/reject', [AdminController::class, 'rejectUser'])->name('users.reject');
        Route::get('/users/{id}/ktm', [AdminController::class, 'showKtm'])->name('users.ktm');
        Route::post('/petugas', [AdminController::class, 'storePetugas'])->name('petugas.store');
        Route::post('/pengguna', [AdminController::class, 'storePenggunaDirect'])->name('pengguna.store');

        // Kelola Fasilitas (Diperlengkap dengan Index & Destroy)
        Route::get('/facilities', [AdminController::class, 'facilitiesIndex'])->name('facilities.index'); // [TAMBAHAN] Tampil daftar fasilitas
        Route::post('/facilities', [AdminController::class, 'storeFacility'])->name('facilities.store');
        Route::put('/facilities/{id}', [AdminController::class, 'updateFacility'])->name('facilities.update');
        Route::delete('/facilities/{id}', [AdminController::class, 'destroyFacility'])->name('facilities.destroy'); // [TAMBAHAN] Hapus fasilitas
        Route::post('/facilities/{id}/toggle', [AdminController::class, 'toggleFacilityStatus'])->name('facilities.toggle');
    });