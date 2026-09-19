<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\PetugasController;
use App\Http\Controllers\AdminController;

// Landing Page & Ketersediaan Slot (Pengunjung & Semua Aktor)
Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::get('/fasilitas/{id}/ketersediaan', [LandingController::class, 'checkAvailability'])->name('fasilitas.ketersediaan');

// Autentikasi Satu Pintu
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Group: Pengguna (Mahasiswa, Dosen, Staf)
Route::middleware(['auth', 'role:pengguna'])->prefix('pengguna')->name('pengguna.')->group(function () {
    Route::get('/dashboard', [PenggunaController::class, 'dashboard'])->name('dashboard');
    Route::post('/reservasi', [PenggunaController::class, 'storeReservasi'])->name('reservasi.store');
    Route::post('/reservasi/{id}/batal', [PenggunaController::class, 'cancelReservasi'])->name('reservasi.cancel');
    Route::post('/laporan', [PenggunaController::class, 'storeLaporan'])->name('laporan.store');
});

// Group: Petugas
Route::middleware(['auth', 'role:petugas'])->prefix('petugas')->name('petugas.')->group(function () {
    Route::get('/dashboard', [PetugasController::class, 'dashboard'])->name('dashboard');
    Route::post('/reservasi/{id}/approve', [PetugasController::class, 'approveReservasi'])->name('reservasi.approve');
    Route::post('/reservasi/{id}/reject', [PetugasController::class, 'rejectReservasi'])->name('reservasi.reject');
    Route::post('/reservasi/{id}/emergency-cancel', [PetugasController::class, 'emergencyCancel'])->name('reservasi.emergency_cancel');
    Route::post('/laporan/{id}/process', [PetugasController::class, 'processLaporan'])->name('laporan.process');
    Route::post('/laporan/{id}/resolve', [PetugasController::class, 'resolveLaporan'])->name('laporan.resolve');
    Route::post('/fasilitas/{id}/status', [PetugasController::class, 'updateFacilityStatus'])->name('fasilitas.status');
});

// Group: Admin
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Req 14 & 15: Pendaftaran Akun Direct (Petugas & Pengguna)
    Route::post('/register-user', [AdminController::class, 'storeUserByAdmin'])->name('users.store');
    
    // Req 15: Verifikasi Pengguna
    Route::post('/users/{id}/verify', [AdminController::class, 'verifyUser'])->name('users.verify');
    Route::post('/users/{id}/reject', [AdminController::class, 'rejectUser'])->name('users.reject');
    
    // Req 16: Kelola Fasilitas
    Route::post('/facilities', [AdminController::class, 'storeFacility'])->name('facilities.store');
    Route::put('/facilities/{id}', [AdminController::class, 'updateFacility'])->name('facilities.update');
    Route::post('/facilities/{id}/toggle', [AdminController::class, 'toggleFacilityStatus'])->name('facilities.toggle');
    
    // Req 17: Export Rekap Full Data (CSV/Excel/PDF)
    Route::get('/rekap/export/{format}', [AdminController::class, 'exportFullData'])->name('rekap.export');
});