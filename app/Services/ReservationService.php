<?php

namespace App\Services;

use App\Exceptions\InvalidSlotException;
use App\Exceptions\ReservationConflictException;
use App\Models\Facility;
use App\Models\Notification;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReservationService
{
    private const JAM_BUKA = '07:00';
    private const JAM_TUTUP = '20:00';
    private const INTERVAL_MENIT = 30;
    private const DURASI_MIN = 30;
    private const DURASI_MAX = 240;
    private const MIN_JAM_SEBELUM = 120;
    private const MAX_HARI_KE_DEPAN = 30;
    private const MAX_RESERVASI_AKTIF = 10;
    private const MAX_SPAM_PER_HARI = 5;

    /**
     * Memeriksa bentrok jadwal dengan reservasi lain yang telah disetujui (status: approved)
     * Menggunakan interval overlap check: (A_start < B_end) AND (A_end > B_start)
     */
    public function hasConflict(int $facilityId, mixed $tanggal, mixed $startTime, mixed $endTime, ?int $excludeReservationId = null): bool
    {
        // Pastikan tanggal berupa string Y-m-d yang valid
        $parsedTanggal = $tanggal instanceof Carbon 
            ? $tanggal->format('Y-m-d') 
            : Carbon::parse((string) $tanggal)->format('Y-m-d');

        // Pastikan format waktu aman (mengambil string waktu H:i:s)
        $startStr = is_string($startTime) && str_contains($startTime, ':') ? $startTime : ((string)$startTime . ':00:00');
        $endStr   = is_string($endTime) && str_contains($endTime, ':') ? $endTime : ((string)$endTime . ':00:00');

        $start = Carbon::parse($startStr)->format('H:i:s');
        $end   = Carbon::parse($endStr)->format('H:i:s');

        $query = Reservation::query()
            ->where('facility_id', $facilityId)
            ->where('tanggal', $parsedTanggal)
            ->where('status', 'approved')
            ->where(function ($q) use ($start, $end) {
                $q->where('start_time', '<', $end)
                  ->where('end_time', '>', $start);
            });

        if ($excludeReservationId) {
            $query->where('id', '!=', $excludeReservationId);
        }

        return $query->exists();
    }

    /**
     * Membuat reservasi baru dengan validasi lengkap
     */
    public function create(array $data, int $userId): Reservation
    {
        $facility = Facility::find($data['facility_id']);
        if (!$facility) {
            throw new InvalidSlotException('Fasilitas tidak ditemukan.');
        }
        if ($facility->status !== 'aktif') {
            throw new InvalidSlotException('Fasilitas ini sedang tidak dapat dipesan.');
        }

        $tanggal = Carbon::parse($data['tanggal'])->startOfDay();
        if ($tanggal->lt(now()->startOfDay())) {
            throw new InvalidSlotException('Tanggal tidak boleh di masa lalu.');
        }
        if ($tanggal->gt(now()->addDays(self::MAX_HARI_KE_DEPAN)->startOfDay())) {
            throw new InvalidSlotException('Reservasi maksimal ' . self::MAX_HARI_KE_DEPAN . ' hari ke depan.');
        }

        $start = Carbon::parse($data['tanggal'] . ' ' . $data['start_time']);
        $end   = Carbon::parse($data['tanggal'] . ' ' . $data['end_time']);

        $this->validateSlot($start, $end);
        $this->validateOperationalHours($start, $end);

        $durasi = $start->diffInMinutes($end);
        if ($durasi < self::DURASI_MIN) {
            throw new InvalidSlotException('Durasi reservasi minimal ' . self::DURASI_MIN . ' menit.');
        }
        if ($durasi > self::DURASI_MAX) {
            throw new InvalidSlotException('Durasi reservasi maksimal ' . (self::DURASI_MAX / 60) . ' jam.');
        }

        if (now()->diffInMinutes($start, false) < self::MIN_JAM_SEBELUM) {
            throw new InvalidSlotException('Reservasi minimal ' . (self::MIN_JAM_SEBELUM / 60) . ' jam sebelum waktu mulai.');
        }

        $aktif = Reservation::where('user_id', $userId)
            ->whereIn('status', ['pending', 'approved'])
            ->where('tanggal', '>=', now()->toDateString())
            ->count();

        if ($aktif >= self::MAX_RESERVASI_AKTIF) {
            throw new InvalidSlotException('Anda sudah memiliki ' . self::MAX_RESERVASI_AKTIF . ' reservasi aktif. Selesaikan dulu.');
        }

        $hariIni = Reservation::where('user_id', $userId)
            ->whereDate('created_at', now()->toDateString())
            ->count();

        if ($hariIni >= self::MAX_SPAM_PER_HARI) {
            throw new InvalidSlotException('Anda sudah membuat ' . self::MAX_SPAM_PER_HARI . ' reservasi hari ini.');
        }

        $bentrokSendiri = Reservation::where('user_id', $userId)
            ->whereIn('status', ['pending', 'approved'])
            ->where('tanggal', $data['tanggal'])
            ->where(function ($q) use ($data) {
                $q->where('start_time', '<', $data['end_time'])
                  ->where('end_time', '>', $data['start_time']);
            })
            ->exists();

        if ($bentrokSendiri) {
            throw new InvalidSlotException('Anda sudah punya reservasi di jam ini.');
        }

        if ($this->hasConflict($data['facility_id'], $data['tanggal'], $data['start_time'], $data['end_time'])) {
            throw new ReservationConflictException('Jadwal ini sudah dipesan atau sedang ditinjau.');
        }

        $reservasi = DB::transaction(function () use ($data, $userId) {
            return Reservation::create([
                'facility_id' => $data['facility_id'],
                'user_id'     => $userId,
                'tanggal'     => $data['tanggal'],
                'start_time'  => $data['start_time'],
                'end_time'    => $data['end_time'],
                'tujuan'      => $data['tujuan'],
                'status'      => 'pending',
            ]);
        });

        $this->notifyPetugas($reservasi);

        return $reservasi;
    }

    /**
     * Membatalkan reservasi secara mandiri oleh pengguna
     */
    public function cancel(int $reservationId, int $userId, string $alasan = 'Dibatalkan oleh pengguna.'): Reservation
    {
        $reservasi = Reservation::where('user_id', $userId)->find($reservationId);

        if (!$reservasi) {
            throw new InvalidSlotException('Reservasi tidak ditemukan.');
        }
        if (!in_array($reservasi->status, ['pending', 'approved'])) {
            throw new InvalidSlotException('Reservasi ini sudah tidak dapat dibatalkan.');
        }

        $tanggalSaja = $reservasi->tanggal instanceof Carbon 
            ? $reservasi->tanggal->format('Y-m-d') 
            : Carbon::parse($reservasi->tanggal)->format('Y-m-d');

        $startDateTime = Carbon::parse($tanggalSaja . ' ' . $reservasi->start_time);
        
        if (now()->diffInMinutes($startDateTime, false) < self::MIN_JAM_SEBELUM) {
            throw new InvalidSlotException('Pembatalan hanya bisa minimal ' . (self::MIN_JAM_SEBELUM / 60) . ' jam sebelum waktu mulai.');
        }

        $reservasi->update([
            'status'       => 'cancelled',
            'alasan_batal' => $alasan,
        ]);

        $this->notifyPetugasPembatalan($reservasi);

        return $reservasi;
    }

    /**
     * Menyetujui reservasi oleh petugas
     */
    public function approve(int $reservationId, int $petugasId): Reservation
    {
        $reservasi = Reservation::find($reservationId);

        if (!$reservasi) {
            throw new InvalidSlotException('Reservasi tidak ditemukan.');
        }
        if ($reservasi->status !== 'pending') {
            throw new InvalidSlotException('Reservasi ini sudah tidak berstatus menunggu.');
        }

        if ($this->hasConflict($reservasi->facility_id, $reservasi->tanggal, $reservasi->start_time, $reservasi->end_time, $reservasi->id)) {
            throw new ReservationConflictException('Jadwal bertabrakan dengan reservasi lain yang sudah disetujui.');
        }

        $reservasi->update([
            'status'     => 'approved',
            'petugas_id' => $petugasId,
        ]);

        $this->notifyUserApproved($reservasi);

        return $reservasi;
    }

    /**
     * Menolak reservasi oleh petugas
     */
    public function reject(int $reservationId, int $petugasId, string $alasan): Reservation
    {
        $reservasi = Reservation::find($reservationId);

        if (!$reservasi) {
            throw new InvalidSlotException('Reservasi tidak ditemukan.');
        }
        if ($reservasi->status !== 'pending') {
            throw new InvalidSlotException('Reservasi ini sudah tidak berstatus menunggu.');
        }

        $reservasi->update([
            'status'       => 'rejected',
            'alasan_tolak' => $alasan,
            'petugas_id'   => $petugasId,
        ]);

        $this->notifyUserRejected($reservasi, $alasan);

        return $reservasi;
    }

    private function validateSlot(Carbon $start, Carbon $end): void
    {
        if ($start->minute % self::INTERVAL_MENIT !== 0) {
            throw new InvalidSlotException('Jam mulai harus kelipatan 30 menit.');
        }
        if ($end->minute % self::INTERVAL_MENIT !== 0) {
            throw new InvalidSlotException('Jam selesai harus kelipatan 30 menit.');
        }
    }

    private function validateOperationalHours(Carbon $start, Carbon $end): void
    {
        $jamBuka  = Carbon::parse($start->format('Y-m-d') . ' ' . self::JAM_BUKA);
        $jamTutup = Carbon::parse($start->format('Y-m-d') . ' ' . self::JAM_TUTUP);

        if ($start->lt($jamBuka)) {
            throw new InvalidSlotException('Jam mulai tidak boleh sebelum 07:00.');
        }
        if ($end->gt($jamTutup)) {
            throw new InvalidSlotException('Jam selesai tidak boleh melebihi 20:00.');
        }
    }

    private function notifyPetugas(Reservation $reservasi): void
    {
        $petugas = User::where('role', 'petugas')->get();
        foreach ($petugas as $p) {
            Notification::create([
                'user_id'        => $p->id,
                'reservation_id' => $reservasi->id,
                'judul'          => 'Reservasi Baru',
                'pesan'          => 'Reservasi baru dari ' . ($reservasi->user->name ?? 'pengguna')
                                    . ' di ' . ($reservasi->facility->nama_fasilitas ?? 'fasilitas') . '.',
                'tipe'           => 'info',
                'link'           => route('petugas.reservasi.index'),
            ]);
        }
    }

    private function notifyPetugasPembatalan(Reservation $reservasi): void
    {
        $petugas = User::where('role', 'petugas')->get();
        foreach ($petugas as $p) {
            Notification::create([
                'user_id'        => $p->id,
                'reservation_id' => $reservasi->id,
                'judul'          => 'Reservasi Dibatalkan',
                'pesan'          => ($reservasi->user->name ?? 'Pengguna') . ' membatalkan reservasi di '
                                    . ($reservasi->facility->nama_fasilitas ?? 'fasilitas') . '.',
                'tipe'           => 'warning',
                'link'           => route('petugas.reservasi.index'),
            ]);
        }
    }

    private function notifyUserApproved(Reservation $reservasi): void
    {
        Notification::create([
            'user_id'        => $reservasi->user_id,
            'reservation_id' => $reservasi->id,
            'judul'          => 'Reservasi Disetujui',
            'pesan'          => 'Reservasi Anda di ' . ($reservasi->facility->nama_fasilitas ?? 'fasilitas')
                                . ' telah disetujui.',
            'tipe'           => 'success',
            'link'           => route('pengguna.reservasi.cetak', $reservasi->id),
        ]);
    }

    private function notifyUserRejected(Reservation $reservasi, string $alasan): void
    {
        Notification::create([
            'user_id'        => $reservasi->user_id,
            'reservation_id' => $reservasi->id,
            'judul'          => 'Reservasi Ditolak',
            'pesan'          => 'Reservasi Anda di ' . ($reservasi->facility->nama_fasilitas ?? 'fasilitas')
                                . ' ditolak. Alasan: ' . $alasan,
            'tipe'           => 'danger',
            'link'           => route('pengguna.reservasi.index'),
        ]);
    }
}