<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Models\Report;
use App\Models\Facility;
use App\Services\ReservationService;

class PetugasController extends Controller
{
    public function dashboard()
    {
        $pendingReservations = Reservation::with(['facility', 'user'])->where('status', 'pending')->latest()->get();
        $approvedReservations = Reservation::with(['facility', 'user'])->where('status', 'approved')->latest()->get();
        $reports = Report::with(['facility', 'user'])->latest()->get();
        $facilities = Facility::all();

        return view('petugas.dashboard', compact('pendingReservations', 'approvedReservations', 'reports', 'facilities'));
    }

    public function approveReservasi($id, ReservationService $service)
    {
        $res = Reservation::findOrFail($id);

        if ($service->hasConflict($res->facility_id, $res->tanggal->toDateString(), $res->start_time, $res->end_time, $res->id)) {
            return back()->withErrors(['msg' => 'Gagal menyetujui: Jadwal bertabrakan dengan reservasi lain yang telah disetujui!']);
        }

        $res->update([
            'status'     => 'approved',
            'petugas_id' => auth()->id(),
        ]);

        return back()->with('success', "Reservasi #{$res->id} berhasil disetujui.");
    }

    public function rejectReservasi($id)
    {
        $res = Reservation::findOrFail($id);
        $res->update([
            'status'     => 'rejected',
            'petugas_id' => auth()->id(),
        ]);

        return back()->with('info', "Reservasi #{$res->id} telah ditolak.");
    }

    public function emergencyCancel(Request $request, $id)
    {
        $request->validate([
            'alasan_batal' => 'required|string|max:250',
        ]);

        $res = Reservation::findOrFail($id);
        $res->update([
            'status'       => 'cancelled',
            'alasan_batal' => $request->alasan_batal,
            'petugas_id'   => auth()->id(),
        ]);

        return back()->with('warning', "Reservasi #{$res->id} dibatalkan secara darurat.");
    }

    public function processLaporan($id)
    {
        $report = Report::findOrFail($id);
        $report->update([
            'status_laporan' => 'diproses',
            'petugas_id'     => auth()->id(),
        ]);

        return back()->with('success', 'Status laporan diubah menjadi diproses.');
    }

    public function resolveLaporan(Request $request, $id)
    {
        $request->validate([
            'catatan_resolusi' => 'required|string|max:250',
        ]);

        $report = Report::findOrFail($id);
        $report->update([
            'status_laporan'   => 'selesai',
            'catatan_resolusi' => $request->catatan_resolusi,
            'petugas_id'       => auth()->id(),
        ]);

        return back()->with('success', 'Laporan berhasil diselesaikan beserta catatan perbaikan.');
    }

    public function updateFacilityStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:aktif,dalam_perbaikan,nonaktif',
        ]);

        $facility = Facility::findOrFail($id);
        $facility->update(['status' => $request->status]);

        return back()->with('success', "Status fasilitas {$facility->nama_fasilitas} diperbarui ke {$request->status}.");
    }
}