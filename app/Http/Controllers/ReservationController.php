<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReservationController extends Controller
{
    /**
     * Menampilkan daftar reservasi milik pengguna login (Index)
     */
    public function index(Request $request)
    {
        $activeStatus = $request->query('status', 'all');

        $query = Reservation::with(['facility', 'petugas'])
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc');

        $myReservations = $query->get();

        // Hitung statistik per status
        $counts = [
            'all'       => $myReservations->count(),
            'pending'   => $myReservations->where('status', 'pending')->count(),
            'approved'  => $myReservations->where('status', 'approved')->count(),
            'rejected'  => $myReservations->where('status', 'rejected')->count(),
            'cancelled' => $myReservations->where('status', 'cancelled')->count(),
        ];

        return view('pengguna.reservasi.index', compact('myReservations', 'activeStatus', 'counts'));
    }

    /**
     * Menampilkan form buat reservasi baru (Create)
     */
    public function create(Request $request)
    {
        $facilities = Facility::all();
        $facilityId = $request->query('facility_id');
        $tanggal    = $request->query('tanggal', now()->toDateString());
        $startTime  = $request->query('start_time', '08:00');
        $endTime    = $request->query('end_time', '08:30');

        $facility = $facilityId ? Facility::find($facilityId) : $facilities->first();

        return view('pengguna.reservasi.create', compact('facilities', 'facility', 'tanggal', 'startTime', 'endTime'));
    }

    /**
     * Menyimpan data reservasi baru ke database (Store)
     */
    public function store(Request $request)
    {
        $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'tanggal'     => 'required|date|after_or_equal:today',
            'start_time'  => 'required',
            'end_time'    => 'required|after:start_time',
            'tujuan'      => 'required|string|max:200',
        ]);

        // Cek Bentrok Jadwal di Database
        $bentrok = Reservation::where('facility_id', $request->facility_id)
            ->where('tanggal', $request->tanggal)
            ->whereIn('status', ['approved', 'pending'])
            ->where(function ($q) use ($request) {
                $q->whereBetween('start_time', [$request->start_time, $request->end_time])
                  ->orWhereBetween('end_time', [$request->start_time, $request->end_time])
                  ->orWhere(function ($sub) use ($request) {
                      $sub->where('start_time', '<=', $request->start_time)
                          ->where('end_time', '>=', $request->end_time);
                  });
            })->exists();

        if ($bentrok) {
            return back()->with('error', 'Fasilitas pada jam tersebut sudah dipesan atau sedang ditinjau. Silakan pilih jam lain.');
        }

        Reservation::create([
            'user_id'     => auth()->id(),
            'facility_id' => $request->facility_id,
            'tanggal'     => $request->tanggal,
            'start_time'  => $request->start_time,
            'end_time'    => $request->end_time,
            'tujuan'      => $request->tujuan,
            'status'      => 'pending',
        ]);

        return redirect()->route('pengguna.reservasi.index')->with('success', 'Permohonan reservasi berhasil diajukan!');
    }

    /**
     * Membatalkan reservasi (Maksimal H-1 jam sebelum jam pemakaian)
     */
    public function cancel($id)
    {
        $reservation = Reservation::where('user_id', auth()->id())->findOrFail($id);

        // Hanya bisa membatalkan reservasi berstatus pending atau approved
        if (!in_array($reservation->status, ['pending', 'approved'])) {
            return back()->with('error', 'Reservasi ini tidak dapat dibatalkan.');
        }

        // Hitung selisih waktu dari sekarang ke jam mulai penggunaan
        $startDateTime = Carbon::parse($reservation->tanggal . ' ' . $reservation->start_time);
        
        // Pengecekan: Pembatalan maksimal dilakukan H-1 jam (60 menit) sebelum jam pemakaian
        if (now()->diffInMinutes($startDateTime, false) < 60) {
            return back()->with('error', 'Pembatalan gagal. Batas waktu pembatalan adalah maksimal 1 jam sebelum jam mulai pemakaian.');
        }

        // Update status menjadi dibatalkan
        $reservation->update([
            'status'       => 'cancelled',
            'alasan_batal' => 'Dibatalkan oleh pengguna.',
        ]);

        return back()->with('success', 'Reservasi berhasil dibatalkan.');
    }
}