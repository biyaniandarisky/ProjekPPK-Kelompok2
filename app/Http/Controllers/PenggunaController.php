<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Requests\StoreReportRequest;
use App\Services\ReservationService;
use App\Services\ReportService;
use App\Services\SlotService;
use App\Models\Facility;
use App\Models\Reservation;
use App\Models\Report;
use App\Models\Notification;
use Carbon\Carbon;

class PenggunaController extends Controller
{
    /* =========================================================
     |  NOTIFIKASI
     ========================================================= */
    public function indexNotifikasi(Request $request)
    {
        $userId = auth()->id();
        $kat = $request->query('kat');

        $query = Notification::where('user_id', $userId);

        if ($kat === 'reservasi') {
            $query->where('judul', 'LIKE', '%Reservasi%');
        } elseif ($kat === 'laporan') {
            $query->where(function ($q) {
                $q->where('judul', 'LIKE', '%Laporan%')
                  ->orWhere('judul', 'LIKE', '%Perbaikan%');
            });
        }

        $notifications = $query->latest()->get();

        $totalCount = Notification::where('user_id', $userId)->count();
        $reservasiCount = Notification::where('user_id', $userId)->where('judul', 'LIKE', '%Reservasi%')->count();
        $laporanCount = Notification::where('user_id', $userId)->where(function ($q) {
            $q->where('judul', 'LIKE', '%Laporan%')->orWhere('judul', 'LIKE', '%Perbaikan%');
        })->count();

        Notification::where('user_id', $userId)->where('is_read', false)->update(['is_read' => true]);

        return view('pengguna.notifikasi.index', compact('notifications', 'totalCount', 'reservasiCount', 'laporanCount'));
    }

    /* =========================================================
     |  DASHBOARD
     ========================================================= */
    public function dashboard(Request $request)
    {
        $userId = auth()->id();
        $selectedFacilityId = $request->query('facility_id');

        if ($selectedFacilityId) {
            return redirect()->route('pengguna.reservasi.create', ['facility_id' => $selectedFacilityId]);
        }

        if ($request->boolean('lapor')) {
            return redirect()->route('pengguna.laporan.create');
        }

        if ($request->session()->has('booking_intent')) {
            $intent = $request->session()->pull('booking_intent');
            return redirect()->route('pengguna.reservasi.create', [
                'facility_id' => $intent['facility_id'] ?? null,
                'tanggal'     => $intent['tanggal'] ?? null,
                'start'       => $intent['start_time'] ?? null,
                'end'         => $intent['end_time'] ?? null,
            ]);
        }

        $myReservations = Reservation::with('facility')->where('user_id', $userId)->latest()->get();
        $myReports = Report::with('facility')->where('user_id', $userId)->latest()->get();
        $facilities = Facility::where('status', 'aktif')->orderBy('nama_fasilitas')->get();

        $stats = [
            'total_reservasi' => $myReservations->count(),
            'disetujui'       => $myReservations->where('status', 'approved')->count(),
            'menunggu'        => $myReservations->where('status', 'pending')->count(),
            'total_laporan'   => $myReports->count(),
        ];

        $reservasiTerdekat = Reservation::with('facility')
            ->where('user_id', $userId)
            ->whereIn('status', ['pending', 'approved'])
            ->where('tanggal', '>=', now()->toDateString())
            ->orderBy('tanggal')
            ->orderBy('start_time')
            ->first();

        $perluPerhatian = collect();

        $sebentarLagi = Reservation::with('facility')
            ->where('user_id', $userId)
            ->where('status', 'approved')
            ->where('tanggal', now()->toDateString())
            ->whereBetween('start_time', [now()->format('H:i'), now()->addHours(2)->format('H:i')])
            ->first();

        if ($sebentarLagi) {
            $perluPerhatian->push([
                'pesan' => "Reservasi di {$sebentarLagi->facility->nama_fasilitas} akan dimulai dalam 2 jam.",
                'aksi'  => 'Lihat Detail',
                'link'  => route('pengguna.reservasi.index'),
            ]);
        }

        $ditolak = Reservation::with('facility')
            ->where('user_id', $userId)
            ->where('status', 'rejected')
            ->where('updated_at', '>=', now()->subDays(3))
            ->first();

        if ($ditolak) {
            $perluPerhatian->push([
                'pesan' => "Reservasi Anda di {$ditolak->facility->nama_fasilitas} ditolak.",
                'aksi'  => 'Lihat',
                'link'  => route('pengguna.reservasi.index', ['status' => 'rejected']),
            ]);
        }

        return view('pengguna.dashboard', compact(
            'myReservations',
            'myReports',
            'facilities',
            'stats',
            'selectedFacilityId',
            'reservasiTerdekat',
            'perluPerhatian'
        ));
    }

    /* =========================================================
     |  RESERVASI
     ========================================================= */
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

        $tanggal = $request->query('tanggal');
        if (!$tanggal || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal) || $tanggal < now()->toDateString()) {
            $tanggal = now()->toDateString();
        }

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
     * AJAX: ketersediaan slot per fasilitas.
     * Menggunakan SlotService agar konsisten dengan landing page.
     */
    public function getKetersediaanFasilitas(Request $request, $facilityId, SlotService $slotService)
    {
        $tanggal = $request->query('tanggal', now()->toDateString());
        $slots   = $slotService->getSlots((int) $facilityId, $tanggal);

        return response()->json(['slots' => $slots]);
    }

    public function reservasiIndex(Request $request)
    {
        $userId = auth()->id();
        $searchTanggal = $request->query('tanggal');
        $activeStatus = $request->query('status', 'all');

        if (!in_array($activeStatus, ['all', 'pending', 'approved', 'rejected', 'cancelled'])) {
            $activeStatus = 'all';
        }

        $query = Reservation::with('facility')->where('user_id', $userId);

        if ($searchTanggal) {
            $query->whereDate('tanggal', $searchTanggal);
        }

        if ($activeStatus !== 'all') {
            $query->where('status', $activeStatus);
        }

        $myReservations = $query->orderByDesc('tanggal')->orderByDesc('id')->get();

        $allUserReservations = Reservation::where('user_id', $userId)->get();
        $counts = [
            'all'       => $allUserReservations->count(),
            'pending'   => $allUserReservations->where('status', 'pending')->count(),
            'approved'  => $allUserReservations->where('status', 'approved')->count(),
            'rejected'  => $allUserReservations->where('status', 'rejected')->count(),
            'cancelled' => $allUserReservations->where('status', 'cancelled')->count(),
        ];

        $totalLaporan = Report::where('user_id', $userId)->count();

        return view('pengguna.reservasi.index', compact(
            'myReservations',
            'counts',
            'totalLaporan',
            'activeStatus',
            'searchTanggal'
        ));
    }

    public function storeReservasi(StoreReservationRequest $request, ReservationService $service)
    {
        try {
            $service->create($request->validated(), auth()->id());

            return redirect()->route('pengguna.reservasi.index')
                ->with('success', 'Pengajuan reservasi berhasil dikirim! Menunggu verifikasi petugas.');
        } catch (\App\Exceptions\ReservationConflictException $e) {
            return back()->withInput()->withErrors(['conflict' => $e->getMessage()]);
        } catch (\App\Exceptions\InvalidSlotException $e) {
            return back()->withInput()->withErrors(['slot' => $e->getMessage()]);
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function cancelReservasi($id, ReservationService $service)
    {
        try {
            $service->cancel($id, auth()->id());
            return redirect()->route('pengguna.reservasi.index')
                ->with('success', 'Reservasi berhasil dibatalkan.');
        } catch (\App\Exceptions\InvalidSlotException $e) {
            return redirect()->route('pengguna.reservasi.index')
                ->with('error', $e->getMessage());
        }
    }

    /* =========================================================
     |  LAPORAN
     ========================================================= */
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

    public function storeLaporan(StoreReportRequest $request, ReportService $service)
    {
        try {
            $data = $request->validated();

            if ($request->hasFile('foto')) {
                $data['foto'] = $request->file('foto')->store('reports', 'public');
            }

            $service->create($data, auth()->id());

            return redirect()->route('pengguna.dashboard')
                ->with('success', 'Laporan kendala berhasil dikirim ke tim petugas.');
        } catch (\App\Exceptions\InvalidSlotException $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function cetakReservasi($id)
    {
        $reservation = Reservation::with(['facility', 'user', 'petugas'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        if ($reservation->status !== 'approved') {
            return back()->with('info', 'Dokumen persetujuan hanya tersedia untuk reservasi yang telah disetujui.');
        }

        return view('pengguna.reservasi.cetak', compact('reservation'));
    }
}