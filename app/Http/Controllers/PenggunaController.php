<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Requests\StoreReportRequest;
use App\Services\ReservationService;
use App\Models\Facility;
use App\Models\Reservation;
use App\Models\Report;

class PenggunaController extends Controller
{
    public function dashboard(Request $request)
    {
        $userId = auth()->id();
        $selectedFacilityId = $request->query('facility_id');

        // Tautan lama "?facility_id=" diarahkan ke halaman Formulir Reservasi
        if ($selectedFacilityId) {
            return redirect()->route('pengguna.reservasi.create', ['facility_id' => $selectedFacilityId]);
        }

        // Tombol "Laporkan Masalah" di landing page (?lapor=1) diarahkan ke Formulir Laporan
        if ($request->boolean('lapor')) {
            return redirect()->route('pengguna.laporan.create');
        }

        // Pilihan slot dari pop-up "Jadwal & Ketersediaan Slot" di landing page
        // (disimpan LandingController::bookingIntent ke session) -> lanjut ke Formulir Reservasi
        if ($request->session()->has('booking_intent')) {
            $intent = $request->session()->pull('booking_intent');

            return redirect()->route('pengguna.reservasi.create', [
                'facility_id' => $intent['facility_id'] ?? null,
                'tanggal'     => $intent['tanggal'] ?? null,
                'start'       => $intent['start_time'] ?? null,
                'end'         => $intent['end_time'] ?? null,
            ]);
        }

        // Ambil data reservasi & laporan pengguna
        $myReservations = Reservation::with('facility')->where('user_id', $userId)->latest()->get();
        $myReports = Report::with('facility')->where('user_id', $userId)->latest()->get();
        
        // Ambil seluruh fasilitas agar dapat dipilih di form/dropdown
        $facilities = Facility::all();

        // Hitung statistik
        $stats = [
            'total_reservasi' => $myReservations->count(),
            'disetujui'       => $myReservations->where('status', 'approved')->count(),
            'menunggu'        => $myReservations->where('status', 'pending')->count(),
            'total_laporan'   => $myReports->count(),
        ];

        return view('pengguna.dashboard', compact(
            'myReservations', 
            'myReports', 
            'facilities', 
            'stats', 
            'selectedFacilityId'
        ));
    }

    /**
     * Halaman Formulir Reservasi (lanjutan dari pop-up "Jadwal & Ketersediaan Slot")
     */
    public function createReservasi(Request $request)
    {
        $facilities = Facility::where('status', 'aktif')->orderBy('nama_fasilitas')->get();

        $facility = null;
        if ($request->filled('facility_id')) {
            $facility = Facility::find($request->query('facility_id'));
        }

        if ($facility && $facility->status !== 'aktif') {
            return redirect()->route('landing')
                ->with('info', 'Fasilitas ' . $facility->nama_fasilitas . ' sedang tidak dapat dipesan.');
        }

        // Tanggal default: hari ini (atau kiriman dari pop-up ketersediaan slot)
        $tanggal = $request->query('tanggal');
        if (!$tanggal || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal) || $tanggal < now()->toDateString()) {
            $tanggal = now()->toDateString();
        }

        // Slot terpilih dari pop-up (opsional)
        $startTime = $request->query('start');
        $endTime   = $request->query('end');
        if (!$startTime || !preg_match('/^(0[7-9]|1[0-9]):(00|30)$/', $startTime)) {
            $startTime = null;
        }
        if (!$endTime || !preg_match('/^(0[7-9]|1[0-9]|20):(00|30)$/', $endTime)) {
            $endTime = null;
        }

        return view('pengguna.reservasi.create', compact(
            'facilities',
            'facility',
            'tanggal',
            'startTime',
            'endTime'
        ));
    }

    /**
     * Halaman Riwayat "Reservasi Saya" (dengan filter status)
     */
    public function reservasiIndex(Request $request)
    {
        $userId = auth()->id();

        $myReservations = Reservation::with('facility')
            ->where('user_id', $userId)
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();

        $counts = [
            'all'       => $myReservations->count(),
            'pending'   => $myReservations->where('status', 'pending')->count(),
            'approved'  => $myReservations->where('status', 'approved')->count(),
            'rejected'  => $myReservations->where('status', 'rejected')->count(),
            'cancelled' => $myReservations->where('status', 'cancelled')->count(),
        ];

        $totalLaporan = Report::where('user_id', $userId)->count();

        $activeStatus = $request->query('status', 'all');
        if (!in_array($activeStatus, ['all', 'pending', 'approved', 'rejected', 'cancelled'])) {
            $activeStatus = 'all';
        }

        return view('pengguna.reservasi.index', compact(
            'myReservations',
            'counts',
            'totalLaporan',
            'activeStatus'
        ));
    }

    public function storeReservasi(StoreReservationRequest $request, ReservationService $service)
    {
        $service->create($request->validated(), auth()->id());
        return redirect()->route('pengguna.reservasi.index')->with('success', 'Pengajuan reservasi berhasil dikirim! Menunggu verifikasi petugas.');
    }

    public function cancelReservasi($id)
    {
        $reservation = Reservation::where('user_id', auth()->id())->findOrFail($id);

        if (!in_array($reservation->status, ['pending', 'approved'])) {
            return redirect()->route('pengguna.reservasi.index')->with('info', 'Reservasi ini sudah tidak dapat dibatalkan.');
        }

        $reservation->update(['status' => 'cancelled']);
        return redirect()->route('pengguna.reservasi.index')->with('info', 'Reservasi berhasil dibatalkan.');
    }

    public function createLaporan(Request $request)
    {
        $facilities = Facility::all();
        $selectedFacilityId = $request->query('facility_id');

        return view('pengguna.laporan.create', compact('facilities', 'selectedFacilityId'));
    }

    public function laporanIndex()
    {
        $myReports = Report::with('facility')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('pengguna.laporan.index', compact('myReports'));
    }

    public function storeLaporan(StoreReportRequest $request)
    {
        $data = $request->validated();
        
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('reports', 'public');
        }

        Report::create([
            'facility_id'      => $data['facility_id'],
            'user_id'          => auth()->id(),
            'kategori_laporan' => $data['kategori_laporan'],
            'deskripsi'        => $data['deskripsi'],
            'foto'             => $fotoPath,
            'status_laporan'   => 'baru',
        ]);

        return redirect()->route('pengguna.dashboard')->with('success', 'Laporan kendala fasilitas berhasil dikirim ke tim petugas.');
    }
}