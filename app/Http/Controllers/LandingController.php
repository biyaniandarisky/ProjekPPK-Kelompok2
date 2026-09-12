<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Facility;
use App\Models\Reservation;
use Carbon\Carbon;

class LandingController extends Controller
{
    public function index(Request $request)
    {
        $query = Facility::query()->where('status', '!=', 'nonaktif');

        if ($request->filled('tipe') && $request->tipe !== 'Semua') {
            $query->where('tipe', $request->tipe);
        }

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('nama_fasilitas', 'like', "%{$search}%")
                  ->orWhere('lokasi', 'like', "%{$search}%");
            });
        }

        $facilities = $query->get();
        return view('landing', compact('facilities'));
    }

    public function checkAvailability(Request $request, $id)
    {
        $facility = Facility::findOrFail($id);
        $tanggal = $request->get('tanggal', now()->addDay()->toDateString());

        // Daftar 26 slot 30-menit operasional (07.00 - 20.00)
        $operationalSlots = [
            '07:00','07:30','08:00','08:30','09:00','09:30',
            '10:00','10:30','11:00','11:30','12:00','12:30',
            '13:00','13:30','14:00','14:30','15:00','15:30',
            '16:00','16:30','17:00','17:30','18:00','18:30',
            '19:00','19:30'
        ];

        $approvedReservations = Reservation::where('facility_id', $id)
            ->where('tanggal', $tanggal)
            ->where('status', 'approved')
            ->get();

        $slots = [];
        foreach ($operationalSlots as $startTime) {
            $slotStart = Carbon::parse($startTime);
            $slotEnd = $slotStart->copy()->addMinutes(30);

            $isBooked = false;
            $bookedBy = null;

            foreach ($approvedReservations as $res) {
                $resStart = Carbon::parse($res->start_time);
                $resEnd = Carbon::parse($res->end_time);

                // Overlap: slotStart < resEnd && slotEnd > resStart
                if ($slotStart->lt($resEnd) && $slotEnd->gt($resStart)) {
                    $isBooked = true;
                    $bookedBy = $res->user->name ?? 'Pemohon Kampus';
                    break;
                }
            }

            $slots[] = [
                'time' => $startTime . ' - ' . $slotEnd->format('H:i'),
                'is_available' => !$isBooked,
                'booked_by' => $bookedBy,
            ];
        }

        return response()->json([
            'facility' => $facility,
            'tanggal'  => $tanggal,
            'slots'    => $slots,
        ]);
    }
}