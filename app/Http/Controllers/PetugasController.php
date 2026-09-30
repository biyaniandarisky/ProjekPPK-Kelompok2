<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Models\Report;
use App\Models\Facility;
use App\Models\Notification;
use App\Services\ReservationService;

class PetugasController extends Controller
{
    public function dashboard()
    {
        $reservations = Reservation::with('facility')->get();
        $reports = Report::all();
        $today = now()->toDateString();

        $stats = [
            'baru_masuk' => $reservations->where('status', 'pending')->count()
                + $reports->where('status_laporan', 'baru')->count(),

            'masih_berjalan' => $reservations->where('status', 'approved')
                ->filter(fn ($r) => $r->tanggal->toDateString() >= $today)
                ->count(),

            'disetujui' => $reservations->where('status', 'approved')->count(),
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

    public function notifikasiIndex()
    {
        $reservasiBaru = Reservation::with(['facility', 'user'])
            ->where('status', 'pending')
            ->latest()
            ->get();

        $laporanBaru = Report::with(['facility', 'user'])
            ->where('status_laporan', 'baru')
            ->latest()
            ->get();

        $notifikasi = $reservasiBaru
            ->map(fn ($r) => ['tipe' => 'reservasi', 'item' => $r, 'waktu' => $r->created_at])
            ->concat(
                $laporanBaru->map(fn ($l) => ['tipe' => 'laporan', 'item' => $l, 'waktu' => $l->created_at])
            )
            ->sortByDesc('waktu')
            ->values();

        return view('petugas.notifikasi', compact('notifikasi'));
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
        $res = Reservation::with('facility')->findOrFail($id);

        if ($service->hasConflict($res->facility_id, $res->tanggal->toDateString(), $res->start_time, $res->end_time, $res->id)) {
            return back()->withErrors(['msg' => 'Gagal menyetujui: Jadwal bertabrakan dengan reservasi lain yang telah disetujui!']);
        }

        $res->update([
            'status'     => 'approved',
            'petugas_id' => auth()->id(),
        ]);

        // --- KIRIM NOTIFIKASI KE PENGGUNA (DITAMBAHKAN RESERVATION_ID) ---
        Notification::create([
            'user_id'        => $res->user_id,
            'reservation_id' => $res->id, // <-- PENTING: Menghubungkan langsung ID reservasi ke notifikasi
            'judul'          => 'Reservasi Disetujui!',
            'pesan'          => 'Pengajuan reservasi ' . ($res->facility->nama_fasilitas ?? 'fasilitas') . ' untuk tanggal ' . $res->tanggal->format('d M Y') . ' telah disetujui.',
            'tipe'           => 'success',
            'link'           => route('pengguna.reservasi.index'),
        ]);

        return back()->with('success', "Reservasi #{$res->id} berhasil disetujui.");
    }

    public function rejectReservasi(Request $request, $id)
    {
        $request->validate([
            'alasan_tolak' => 'required|string|max:250',
        ], [
            'alasan_tolak.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $res = Reservation::with('facility')->findOrFail($id);
        $res->update([
            'status'       => 'rejected',
            'alasan_tolak' => $request->alasan_tolak,
            'petugas_id'   => auth()->id(),
        ]);

        // --- KIRIM NOTIFIKASI KE PENGGUNA ---
        Notification::create([
            'user_id' => $res->user_id,
            'judul'   => 'Reservasi Ditolak',
            'pesan'   => 'Pengajuan reservasi ' . ($res->facility->nama_fasilitas ?? 'fasilitas') . ' Anda ditolak. Alasan: ' . $request->alasan_tolak,
            'tipe'    => 'danger',
            'link'    => route('pengguna.reservasi.index'),
        ]);

        return back()->with('info', "Reservasi #{$res->id} telah ditolak.");
    }

    public function emergencyCancel(Request $request, $id)
    {
        $request->validate([
            'alasan_batal' => 'required|string|max:250',
        ]);

        $res = Reservation::with('facility')->findOrFail($id);
        $res->update([
            'status'       => 'cancelled',
            'alasan_batal' => $request->alasan_batal,
            'petugas_id'   => auth()->id(),
        ]);

        // --- KIRIM NOTIFIKASI KE PENGGUNA ---
        Notification::create([
            'user_id' => $res->user_id,
            'judul'   => 'Pembatalan Darurat Reservasi',
            'pesan'   => 'Reservasi Anda di ' . ($res->facility->nama_fasilitas ?? 'fasilitas') . ' dibatalkan oleh pihak kampus. Alasan: ' . $request->alasan_batal,
            'tipe'    => 'warning',
            'link'    => route('pengguna.reservasi.index'),
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

        $report->facility->update(['status' => 'dalam_perbaikan']);

        // --- KIRIM NOTIFIKASI KE PENGGUNA ---
        Notification::create([
            'user_id' => $report->user_id,
            'judul'   => 'Laporan Sedang Diproses',
            'pesan'   => 'Laporan kendala Anda pada ' . ($report->facility->nama_fasilitas ?? 'fasilitas') . ' sedang ditindaklanjuti oleh petugas.',
            'tipe'    => 'info',
            'link'    => route('pengguna.laporan.index'),
        ]);

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

        $report->facility->update(['status' => 'aktif']);

        // --- KIRIM NOTIFIKASI KE PENGGUNA ---
        Notification::create([
            'user_id' => $report->user_id,
            'judul'   => 'Laporan Kendala Selesai',
            'pesan'   => 'Laporan kendala pada ' . ($report->facility->nama_fasilitas ?? 'fasilitas') . ' telah selesai ditangani. Catatan: ' . $request->catatan_resolusi,
            'tipe'    => 'success',
            'link'    => route('pengguna.laporan.index'),
        ]);

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

        if ($request->status === 'selesai') {
            $catatan = $request->catatan_resolusi
                ?: 'Perbaikan telah diselesaikan oleh petugas sarana.';

            // Ambil laporan terkait sebelum di-update untuk dikirimi notifikasi
            $laporanTerdampak = $facility->reports()
                ->whereIn('status_laporan', ['baru', 'diproses'])
                ->get();

            $terdampak = $facility->reports()
                ->whereIn('status_laporan', ['baru', 'diproses'])
                ->update([
                    'status_laporan'   => 'selesai',
                    'catatan_resolusi' => $catatan,
                    'petugas_id'       => $petugasId,
                    'updated_at'       => now(),
                ]);

            // Kirim notifikasi ke masing-masing pelapor
            foreach ($laporanTerdampak as $report) {
                Notification::create([
                    'user_id' => $report->user_id,
                    'judul'   => 'Perbaikan Fasilitas Selesai',
                    'pesan'   => 'Perbaikan pada fasilitas ' . $facility->nama_fasilitas . ' telah selesai. Catatan: ' . $catatan,
                    'tipe'    => 'success',
                    'link'    => route('pengguna.laporan.index'),
                ]);
            }

            $facility->update(['status' => 'aktif']);

            return back()->with('success', "Perbaikan {$facility->nama_fasilitas} selesai dan fasilitas kembali Aktif."
                . ($terdampak > 0 ? " {$terdampak} laporan terkait otomatis ditandai Selesai." : ''));
        }

        $facility->update(['status' => 'aktif']);

        return back()->with('info', "Status {$facility->nama_fasilitas} diubah ke Aktif.");
    }
}