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
        $reservations = Reservation::with('facility')->get();
        $reports = Report::all();
        $facilities = Facility::all();

        $stats = [
            'total_reservasi' => $reservations->count(),
            'menunggu'        => $reservations->where('status', 'pending')->count(),
            'disetujui'       => $reservations->where('status', 'approved')->count(),
            'ditolak'         => $reservations->where('status', 'rejected')->count(),
            'dibatalkan'      => $reservations->where('status', 'cancelled')->count(),
            'total_laporan'   => $reports->count(),
            'laporan_baru'    => $reports->where('status_laporan', 'baru')->count(),
            'laporan_proses'  => $reports->where('status_laporan', 'diproses')->count(),
            'laporan_selesai' => $reports->where('status_laporan', 'selesai')->count(),
            'total_fasilitas' => $facilities->count(),
            'fasilitas_aktif' => $facilities->where('status', 'aktif')->count(),
            'fasilitas_perbaikan' => $facilities->where('status', 'dalam_perbaikan')->count(),
        ];

        return view('petugas.dashboard', compact('stats'));
    }

    public function reservasiIndex()
    {
        $reservations = Reservation::with(['facility', 'user'])->latest()->get();

        return view('petugas.reservasi', compact('reservations'));
    }

    public function laporanIndex()
    {
        $reports = Report::with(['facility', 'user'])->latest()->get();

        return view('petugas.laporan', compact('reports'));
    }

    public function fasilitasIndex()
    {
        $facilities = Facility::withCount([
            'reports as laporan_terbuka_count' => function ($q) {
                $q->whereIn('status_laporan', ['baru', 'diproses']);
            },
        ])->orderBy('nama_fasilitas')->get();

        return view('petugas.fasilitas', compact('facilities'));
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
        $report = Report::with('facility')->findOrFail($id);
        $report->update([
            'status_laporan' => 'diproses',
            'petugas_id'     => auth()->id(),
        ]);

        // Laporan diproses otomatis menandai fasilitas sedang dalam perbaikan.
        $report->facility->update(['status' => 'dalam_perbaikan']);

        return back()->with('success', "Laporan diproses. Status {$report->facility->nama_fasilitas} diubah ke Dalam Perbaikan.");
    }

    public function resolveLaporan(Request $request, $id)
    {
        $request->validate([
            'catatan_resolusi' => 'required|string|max:250',
        ]);

        $report = Report::with('facility')->findOrFail($id);
        $report->update([
            'status_laporan'   => 'selesai',
            'catatan_resolusi' => $request->catatan_resolusi,
            'petugas_id'       => auth()->id(),
        ]);

        // Setelah kendala selesai ditangani, fasilitas otomatis aktif lagi.
        $report->facility->update(['status' => 'aktif']);

        return back()->with('success', "Laporan selesai. Status {$report->facility->nama_fasilitas} dikembalikan ke Aktif.");
    }

    public function updateFacilityStatus(Request $request, $id)
    {
        $request->validate([
            'status'           => 'required|in:aktif,dalam_perbaikan,selesai',
            'catatan_resolusi' => 'nullable|string|max:250',
        ]);

        $facility  = Facility::findOrFail($id);
        $petugasId = auth()->id();

        // Aksi: fasilitas masuk masa perbaikan.
        // Semua laporan yang masih "baru" untuk fasilitas ini ikut berubah jadi "diproses".
        if ($request->status === 'dalam_perbaikan') {
            $facility->update(['status' => 'dalam_perbaikan']);

            $terdampak = $facility->reports()
                ->where('status_laporan', 'baru')
                ->update([
                    'status_laporan' => 'diproses',
                    'petugas_id'     => $petugasId,
                    'updated_at'     => now(),
                ]);

            return back()->with('success', "{$facility->nama_fasilitas} ditandai Dalam Perbaikan."
                . ($terdampak > 0 ? " {$terdampak} laporan terkait otomatis berubah ke Diproses." : ''));
        }

        // Aksi: perbaikan selesai.
        // Laporan yang masih terbuka otomatis ditutup (selesai) dan fasilitas kembali Aktif.
        if ($request->status === 'selesai') {
            $catatan = $request->catatan_resolusi
                ?: 'Perbaikan telah diselesaikan oleh petugas sarana.';

            $terdampak = $facility->reports()
                ->whereIn('status_laporan', ['baru', 'diproses'])
                ->update([
                    'status_laporan'   => 'selesai',
                    'catatan_resolusi' => $catatan,
                    'petugas_id'       => $petugasId,
                    'updated_at'       => now(),
                ]);

            $facility->update(['status' => 'aktif']);

            return back()->with('success', "Perbaikan {$facility->nama_fasilitas} selesai dan fasilitas kembali Aktif."
                . ($terdampak > 0 ? " {$terdampak} laporan terkait otomatis ditandai Selesai." : ''));
        }

        // Aksi: mengaktifkan kembali fasilitas tanpa menyentuh laporan.
        $facility->update(['status' => 'aktif']);

        return back()->with('info', "Status {$facility->nama_fasilitas} diubah ke Aktif.");
    }
}