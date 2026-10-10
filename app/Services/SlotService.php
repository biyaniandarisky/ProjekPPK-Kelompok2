<?php

namespace App\Services;

use App\Models\Reservation;
use Carbon\Carbon;

class SlotService
{
    /** Slot operasional 30 menit (07.00 - 20.00). */
    public const SLOTS = [
        '07:00','07:30','08:00','08:30','09:00','09:30',
        '10:00','10:30','11:00','11:30','12:00','12:30',
        '13:00','13:30','14:00','14:30','15:00','15:30',
        '16:00','16:30','17:00','17:30','18:00','18:30',
        '19:00','19:30',
    ];

    /** Buffer minimal (menit) untuk pemesanan di hari yang sama. */
    public const BUFFER_MINUTES = 120; // 2 jam

    /**
     * Ambil semua slot untuk fasilitas + tanggal tertentu.
     */
    public function getSlots(int $facilityId, string $tanggal, ?Carbon $now = null): array
    {
        $now = $now ?? now();

        $booked = Reservation::where('facility_id', $facilityId)
            ->where('tanggal', $tanggal)
            ->whereIn('status', ['pending', 'approved'])
            ->get(['start_time', 'end_time']);

        $isToday = $tanggal === $now->toDateString();

        // Buffer 2 jam hanya berlaku untuk HARI INI
        $minAllowed = $isToday
            ? $now->copy()->addMinutes(self::BUFFER_MINUTES)
            : $now->copy()->startOfDay();

        $slots = [];
        foreach (self::SLOTS as $start) {
            $end = Carbon::createFromFormat('H:i', $start)->addMinutes(30)->format('H:i');

            $slotStartAt = Carbon::createFromFormat('Y-m-d H:i', "{$tanggal} {$start}");

            $isBooked = $booked->contains(function ($res) use ($start, $end) {
                $resStart = substr($res->start_time, 0, 5);
                $resEnd   = substr($res->end_time, 0, 5);
                return $start < $resEnd && $end > $resStart;
            });

            $isPast    = $slotStartAt->lte($now);
            $isTooSoon = !$isPast && $slotStartAt->lt($minAllowed);
            $isAvailable = !$isBooked && !$isPast && !$isTooSoon;

            // Label status untuk UI
            if ($isBooked) {
                $label = 'Terisi';
            } elseif ($isPast) {
                $label = 'Lewat';
            } elseif ($isTooSoon) {
                $label = 'Terlalu Dekat';
            } else {
                $label = 'Tersedia';
            }

            $slots[] = [
                'start'        => $start,
                'end'          => $end,
                'is_booked'    => $isBooked,
                'is_past'      => $isPast,
                'is_too_soon'  => $isTooSoon,
                'is_available' => $isAvailable,
                'label'        => $label,
            ];
        }

        return $slots;
    }
}