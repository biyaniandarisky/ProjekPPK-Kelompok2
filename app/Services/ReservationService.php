<?php

namespace App\Services;

use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class ReservationService
{
    /**
     * Memeriksa bentrok jadwal dengan reservasi lain yang telah disetujui (status: approved)
     * Menggunakan interval overlap check: (A_start < B_end) AND (A_end > B_start)
     */
    public function hasConflict(int $facilityId, string $tanggal, string $startTime, string $endTime, ?int $excludeReservationId = null): bool
    {
        $start = Carbon::parse($startTime)->format('H:i:s');
        $end = Carbon::parse($endTime)->format('H:i:s');

        $query = Reservation::query()
            ->where('facility_id', $facilityId)
            ->where('tanggal', $tanggal)
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
     * Membuat reservasi baru dengan validasi bentrok ketat
     */
    public function create(array $data, int $userId): Reservation
    {
        if ($this->hasConflict($data['facility_id'], $data['tanggal'], $data['start_time'], $data['end_time'])) {
            throw ValidationException::withMessages([
                'start_time' => 'Jadwal slot waktu fasilitas ini sudah disetujui untuk pemohon lain. Silakan pilih jadwal lain.'
            ]);
        }

        return Reservation::create([
            'facility_id' => $data['facility_id'],
            'user_id'     => $userId,
            'tanggal'     => $data['tanggal'],
            'start_time'  => $data['start_time'],
            'end_time'    => $data['end_time'],
            'tujuan'      => $data['tujuan'],
            'status'      => 'pending',
        ]);
    }
}