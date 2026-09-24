<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Facility;
use App\Models\Reservation;
use Carbon\Carbon;

class LandingController extends Controller
{
    /** Slot operasional 30 menit (07.00 - 20.00). */
    private const OPERATIONAL_SLOTS = [
        '07:00','07:30','08:00','08:30','09:00','09:30',
        '10:00','10:30','11:00','11:30','12:00','12:30',
        '13:00','13:30','14:00','14:30','15:00','15:30',
        '16:00','16:30','17:00','17:30','18:00','18:30',
        '19:00','19:30',
    ];

    /**
     * Halaman awal untuk Pengunjung (belum login) maupun pengguna lain.
     */
    public function index(Request $request)
    {
        $query = Facility::query()->where('status', '!=', 'nonaktif');

        if ($request->filled('tipe') && $request->tipe !== 'Semua') {
            $query->where('tipe', $request->tipe);
        }

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where('nama_fasilitas', 'like', "%{$search}%");
        }

        if ($request->filled('lokasi')) {
            $query->where('lokasi', $request->lokasi);
        }

        if ($request->filled('kapasitas') && is_numeric($request->kapasitas)) {
            $query->where('kapasitas', '>=', (int) $request->kapasitas);
        }

        $facilities = $query->orderBy('id')->get();

        // Daftar lokasi / gedung untuk dropdown filter
        $lokasiList = Facility::where('status', '!=', 'nonaktif')
            ->orderBy('lokasi')
            ->pluck('lokasi')
            ->unique()
            ->values();

        // Total fasilitas tanpa filter (untuk keterangan judul)
        $totalFasilitas = Facility::where('status', '!=', 'nonaktif')->count();

        $today   = now()->toDateString();
        $tanggal = $this->normalizeDate($request->get('tanggal'), $today);

        return view('landing', compact('facilities', 'lokasiList', 'totalFasilitas', 'tanggal', 'today'));
    }

    /**
     * JSON ketersediaan slot per 30 menit untuk satu fasilitas pada satu tanggal.
     * Fasilitas berstatus "dalam_perbaikan" mengembalikan slot kosong.
     */
    public function checkAvailability(Request $request, $id)
    {
        $facility = Facility::where('status', '!=', 'nonaktif')->findOrFail($id);
        $today    = now()->toDateString();
        $tanggal  = $this->normalizeDate($request->get('tanggal'), $today);

        $isMaintenance = $facility->status === 'dalam_perbaikan';

        $slots = [];
        if (!$isMaintenance) {
            $approved = Reservation::where('facility_id', $facility->id)
                ->where('tanggal', $tanggal)
                ->where('status', 'approved')
                ->get(['start_time', 'end_time']);

            $now = now();

            foreach (self::OPERATIONAL_SLOTS as $start) {
                $end = Carbon::createFromFormat('H:i', $start)->addMinutes(30)->format('H:i');

                $isBooked = $approved->contains(function ($res) use ($start, $end) {
                    // Overlap: slotStart < resEnd && slotEnd > resStart
                    return $start < substr($res->end_time, 0, 5)
                        && $end > substr($res->start_time, 0, 5);
                });

                $slotStartAt = Carbon::createFromFormat('Y-m-d H:i', "{$tanggal} {$start}");
                $isPast      = $slotStartAt->lte($now);

                $slots[] = [
                    'start'        => $start,
                    'end'          => $end,
                    'is_booked'    => $isBooked,
                    'is_past'      => $isPast,
                    'is_available' => !$isBooked && !$isPast,
                ];
            }
        }

        return response()->json([
            'facility' => [
                'id'         => $facility->id,
                'nama'       => $facility->nama_fasilitas,
                'tipe'       => $facility->tipe,
                'lokasi'     => $facility->lokasi,
                'kapasitas'  => $facility->kapasitas,
                'foto'       => $facility->foto,
                'status'     => $facility->status,
            ],
            'tanggal'        => $tanggal,
            'is_maintenance' => $isMaintenance,
            'slots'          => $slots,
        ]);
    }

    /**
     * Klik "Pesan" dari popup jadwal.
     * Pilihan slot disimpan di SESSION supaya tidak hilang ketika Pengunjung
     * diarahkan ke halaman login / daftar, lalu dipakai lagi setelah login.
     */
    public function bookingIntent(Request $request)
    {
        $data = $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'tanggal'     => 'required|date_format:Y-m-d|after_or_equal:today',
            'start_time'  => ['required', 'regex:/^(0[7-9]|1[0-9]):(00|30)$/'],
            'end_time'    => ['required', 'regex:/^(0[7-9]|1[0-9]|20):(00|30)$/', 'after:start_time'],
        ]);

        $facility = Facility::findOrFail($data['facility_id']);
        if ($facility->status !== 'aktif') {
            return response()->json([
                'message' => 'Fasilitas ini sedang tidak dapat dipesan.',
            ], 422);
        }

        $user = $request->user();

        // Petugas & admin tidak memesan fasilitas
        if ($user && !$user->isPengguna()) {
            return response()->json([
                'message' => 'Hanya akun Pengguna (Mahasiswa/Dosen/Staf) yang dapat memesan fasilitas.',
            ], 403);
        }

        $request->session()->put('booking_intent', $data);

        if (!$user) {
            $request->session()->flash(
                'info',
                "Anda masuk sebagai Pengunjung. Silakan login atau daftar akun untuk memesan {$facility->nama_fasilitas}."
            );
            return response()->json(['redirect' => route('login')]);
        }

        return response()->json(['redirect' => route('pengguna.dashboard')]);
    }

    private function normalizeDate(?string $value, string $fallback): string
    {
        if (!$value) {
            return $fallback;
        }
        try {
            $date = Carbon::createFromFormat('Y-m-d', $value);
            return $date->lt(Carbon::parse($fallback)) ? $fallback : $date->toDateString();
        } catch (\Throwable $e) {
            return $fallback;
        }
    }
}
