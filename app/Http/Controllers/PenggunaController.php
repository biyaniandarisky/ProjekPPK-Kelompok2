<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Requests\StoreReportRequest;
use App\Services\ReservationService;
use App\Models\Facility;
use App\Models\Reservation;
use App\Models\Report;
use App\Models\Notification;
use Carbon\Carbon; // TAMBAHAN: Untuk manipulasi tanggal & waktu slot

class PenggunaController extends Controller
{
    public function indexNotifikasi(Request $request)
    {
        $userId = auth()->id();
        $kat = $request->query('kat');

        $query = Notification::where('user_id', $userId);

        if ($kat === 'reservasi') {
            $query->where('judul', 'LIKE', '%Reservasi%');
        } elseif ($kat === 'laporan') {
            $query->where(function($q) {
                $q->where('judul', 'LIKE', '%Laporan%')
                ->orWhere('judul', 'LIKE', '%Perbaikan%');
            });
        }

        $notifications = $query->latest()->get();

        $totalCount = Notification::where('user_id', $userId)->count();
        $reservasiCount = Notification::where('user_id', $userId)->where('judul', 'LIKE', '%Reservasi%')->count();
        $laporanCount = Notification::where('user_id', $userId)->where(function($q) {
            $q->where('judul', 'LIKE', '%Laporan%')->orWhere('judul', 'LIKE', '%Perbaikan%');
        })->count();

        Notification::where('user_id', $userId)->where('is_read', false)->update(['is_read' => true]);

        // PASTIKAN MENUNJUK KE FOLDER 'pengguna.notifikasi.index'
        return view('pengguna.notifikasi.index', compact('notifications', 'totalCount', 'reservasiCount', 'laporanCount'));
    }
    
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
     * Halaman Formulir Reservasi
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

        // Tanggal default: hari ini
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
     * API AJAX: Mengambil Ketersediaan Slot Jam Fasilitas
     */
    public function getKetersediaanFasilitas(Request $request, $facilityId)
    {
        $tanggal = $request->query('tanggal', now()->toDateString());

        // Ambil reservasi yang disetujui/ditinjau pada tanggal & fasilitas tersebut
        $bookedReservations = Reservation::where('facility_id', $facilityId)
            ->where('tanggal', $tanggal)
            ->whereIn('status', ['approved', 'pending'])
            ->get(['start_time', 'end_time']);

        // Generate slot waktu 30 menitan dari 07:00 s/d 20:00
        $slots = [];
        $start = Carbon::createFromTimeString('07:00');
        $end   = Carbon::createFromTimeString('20:00');

        while ($start < $end) {
            $slotStartStr = $start->format('H:i');
            $slotEnd = (clone $start)->addMinutes(30);
            $slotEndStr = $slotEnd->format('H:i');

            // Cek apakah slot ini bentrok dengan reservasi yang ada
            $isAvailable = true;
            foreach ($bookedReservations as $res) {
                $resStart = Carbon::createFromTimeString($res->start_time)->format('H:i');
                $resEnd   = Carbon::createFromTimeString($res->end_time)->format('H:i');

                if ($slotStartStr >= $resStart && $slotStartStr < $resEnd) {
                    $isAvailable = false;
                    break;
                }
            }

            $slots[] = [
                'start'        => $slotStartStr,
                'end'          => $slotEndStr,
                'is_available' => $isAvailable,
            ];

            $start->addMinutes(30);
        }

        return response()->json(['slots' => $slots]);
    }

    public function reservasiIndex(Request $request)
    {
        $userId = auth()->id();
        
        // Ambil input pencarian tanggal jika ada
        $searchTanggal = $request->query('tanggal');

        $query = Reservation::with('facility')
            ->where('user_id', $userId);

        // Filter berdasarkan tanggal jika diisi
        if ($searchTanggal) {
            $query->whereDate('tanggal', $searchTanggal);
        }

        $myReservations = $query->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();

        // Hitung counts tetap merujuk pada keseluruhan data user (atau bisa disesuaikan)
        $allUserReservations = Reservation::where('user_id', $userId)->get();
        $counts = [
            'all'       => $allUserReservations->count(),
            'pending'   => $allUserReservations->where('status', 'pending')->count(),
            'approved'  => $allUserReservations->where('status', 'approved')->count(),
            'rejected'  => $allUserReservations->where('status', 'rejected')->count(),
            'cancelled' => $allUserReservations->where('status', 'cancelled')->count(),
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
            'activeStatus',
            'searchTanggal' // Kirim variabel tanggal ke view
        ));
    }

    public function storeReservasi(StoreReservationRequest $request, ReservationService $service)
    {
        $service->create($request->validated(), auth()->id());
        return redirect()->route('pengguna.reservasi.index')->with('success', 'Pengajuan reservasi berhasil dikirim! Menunggu verifikasi petugas.');
    }

    /**
     * Pembatalan Reservasi (dengan Validasi Maksimal H-1 Jam)
     */
    public function cancelReservasi($id)
    {
        $reservation = Reservation::where('user_id', auth()->id())->findOrFail($id);

        if (!in_array($reservation->status, ['pending', 'approved'])) {
            return redirect()->route('pengguna.reservasi.index')->with('info', 'Reservasi ini sudah tidak dapat dibatalkan.');
        }

        // Pengecekan Batas Waktu Pembatalan (Maksimal H-1 Jam / 60 Menit sebelum jam mulai)
        $tglStr = $reservation->tanggal instanceof Carbon ? $reservation->tanggal->format('Y-m-d') : $reservation->tanggal;
        $startDateTime = Carbon::parse($tglStr . ' ' . $reservation->start_time);

        if (now()->diffInMinutes($startDateTime, false) < 60) {
            return redirect()->route('pengguna.reservasi.index')
                ->with('info', 'Pembatalan gagal. Batas waktu pembatalan adalah maksimal 1 jam sebelum jam mulai pemakaian.');
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

    /**
     * Cetak / Preview Dokumen Persetujuan Reservasi
     */
    public function cetakReservasi($id)
    {
        $reservation = Reservation::with(['facility', 'user', 'petugas'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        // Hanya reservasi yang berstatus approved yang bisa dicetak
        if ($reservation->status !== 'approved') {
            return back()->with('info', 'Dokumen persetujuan hanya tersedia untuk reservasi yang telah disetujui.');
        }

        return view('pengguna.reservasi.cetak', compact('reservation'));
    }
}