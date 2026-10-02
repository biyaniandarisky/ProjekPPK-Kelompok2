<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Reservation;
use App\Models\Report;
use App\Models\Notification;
use Carbon\Carbon;

/*
|--------------------------------------------------------------------------
| Artisan Commands
|--------------------------------------------------------------------------
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Command: Auto-Expire Reservasi Pending (> 24 Jam)
|--------------------------------------------------------------------------
*/
Artisan::command('reservasi:expire-pending', function () {
    $batas = Carbon::now()->subHours(24);

    $expired = Reservation::where('status', 'pending')
        ->where('created_at', '<', $batas)
        ->get();

    $count = 0;
    foreach ($expired as $reservasi) {
        $reservasi->update([
            'status'       => 'expired',
            'alasan_batal' => 'Otomatis dibatalkan karena tidak diproses dalam 24 jam.',
        ]);

        // Notifikasi ke pemesan
        Notification::create([
            'user_id'        => $reservasi->user_id,
            'reservation_id' => $reservasi->id,
            'judul'          => 'Reservasi Kedaluwarsa',
            'pesan'          => 'Reservasi Anda di ' . ($reservasi->facility->nama_fasilitas ?? 'fasilitas') . ' otomatis dibatalkan karena tidak diproses dalam 24 jam.',
            'tipe'           => 'warning',
            'link'           => route('pengguna.reservasi.index'),
        ]);

        $count++;
    }

    $this->info("Berhasil expire {$count} reservasi pending.");
})->purpose('Batalkan reservasi pending yang sudah lebih dari 24 jam');

/*
|--------------------------------------------------------------------------
| Command: Kirim Pengingat Reservasi (1 Jam Sebelum)
|--------------------------------------------------------------------------
*/
Artisan::command('reservasi:reminder', function () {
    $now = Carbon::now();
    $batasBawah = $now->copy()->addMinutes(55);
    $batasAtas  = $now->copy()->addMinutes(65);

    $reservasi = Reservation::with('facility', 'user')
        ->where('status', 'approved')
        ->whereDate('tanggal', $now->toDateString())
        ->whereNull('reminded_at')
        ->whereBetween('start_time', [
            $batasBawah->format('H:i:s'),
            $batasAtas->format('H:i:s'),
        ])
        ->get();

    $count = 0;
    foreach ($reservasi as $r) {
        Notification::create([
            'user_id'        => $r->user_id,
            'reservation_id' => $r->id,
            'judul'          => 'Pengingat Reservasi',
            'pesan'          => 'Reservasi Anda di ' . ($r->facility->nama_fasilitas ?? 'fasilitas') . ' akan dimulai dalam 1 jam (' . substr($r->start_time, 0, 5) . ' WIB).',
            'tipe'           => 'info',
            'link'           => route('pengguna.reservasi.index'),
        ]);

        $r->update(['reminded_at' => $now]);
        $count++;
    }

    $this->info("Berhasil kirim {$count} pengingat reservasi.");
})->purpose('Kirim pengingat 1 jam sebelum reservasi dimulai');

/*
|--------------------------------------------------------------------------
| Command: Auto-Cancel Reservasi jika Fasilitas Nonaktif
|--------------------------------------------------------------------------
*/
Artisan::command('reservasi:auto-cancel-nonaktif', function () {
    $reservasi = Reservation::with('facility', 'user')
        ->whereIn('status', ['pending', 'approved'])
        ->whereHas('facility', function ($q) {
            $q->where('status', 'nonaktif');
        })
        ->get();

    $count = 0;
    foreach ($reservasi as $r) {
        $r->update([
            'status'       => 'cancelled',
            'alasan_batal' => 'Fasilitas dinonaktifkan oleh admin.',
        ]);

        Notification::create([
            'user_id'        => $r->user_id,
            'reservation_id' => $r->id,
            'judul'          => 'Reservasi Dibatalkan',
            'pesan'          => 'Reservasi Anda di ' . ($r->facility->nama_fasilitas ?? 'fasilitas') . ' dibatalkan karena fasilitas dinonaktifkan.',
            'tipe'           => 'danger',
            'link'           => route('pengguna.reservasi.index'),
        ]);

        $count++;
    }

    $this->info("Berhasil batalkan {$count} reservasi karena fasilitas nonaktif.");
})->purpose('Batalkan reservasi jika fasilitas dinonaktifkan');

/*
|--------------------------------------------------------------------------
| Command: Bersihkan Notifikasi Lama (> 30 Hari)
|--------------------------------------------------------------------------
*/
Artisan::command('notifikasi:bersihkan', function () {
    $count = Notification::where('is_read', true)
        ->where('created_at', '<', Carbon::now()->subDays(30))
        ->delete();

    $this->info("Berhasil hapus {$count} notifikasi lama.");
})->purpose('Hapus notifikasi yang sudah dibaca dan lebih dari 30 hari');

/*
|--------------------------------------------------------------------------
| Scheduler
|--------------------------------------------------------------------------
*/

// Expire pending > 24 jam — tiap jam
Schedule::command('reservasi:expire-pending')->hourly();

// Pengingat 1 jam sebelum — tiap 15 menit
Schedule::command('reservasi:reminder')->everyFifteenMinutes();

// Auto-cancel fasilitas nonaktif — tiap 6 jam
Schedule::command('reservasi:auto-cancel-nonaktif')->everySixHours();

// Bersihkan notifikasi lama — tiap hari jam 02:00
Schedule::command('notifikasi:bersihkan')->dailyAt('02:00');