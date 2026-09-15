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

    public function storeReservasi(StoreReservationRequest $request, ReservationService $service)
    {
        $service->create($request->validated(), auth()->id());
        return redirect()->route('pengguna.dashboard')->with('success', 'Pengajuan reservasi berhasil dikirim! Menunggu verifikasi petugas.');
    }

    public function cancelReservasi($id)
    {
        $reservation = Reservation::where('user_id', auth()->id())->findOrFail($id);
        $reservation->update(['status' => 'cancelled']);
        return redirect()->route('pengguna.dashboard')->with('info', 'Reservasi berhasil dibatalkan.');
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