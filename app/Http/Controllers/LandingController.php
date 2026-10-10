<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Facility;
use App\Services\SlotService;
use Carbon\Carbon;

class LandingController extends Controller
{
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

        $lokasiList = Facility::where('status', '!=', 'nonaktif')
            ->orderBy('lokasi')
            ->pluck('lokasi')
            ->unique()
            ->values();

        $totalFasilitas = Facility::where('status', '!=', 'nonaktif')->count();

        $today   = now()->toDateString();
        $tanggal = $this->normalizeDate($request->get('tanggal'), $today);

        return view('landing', compact('facilities', 'lokasiList', 'totalFasilitas', 'tanggal', 'today'));
    }

    /**
     * JSON ketersediaan slot per 30 menit untuk satu fasilitas pada satu tanggal.
     */
    public function checkAvailability(Request $request, $id, SlotService $slotService)
    {
        $facility = Facility::where('status', '!=', 'nonaktif')->findOrFail($id);
        $today    = now()->toDateString();
        $tanggal  = $this->normalizeDate($request->get('tanggal'), $today);

        $isMaintenance = in_array($facility->status, ['dalam_perbaikan', 'selesai'], true);

        // Hanya hitung slot kalau tidak dalam perbaikan
        $slots = $isMaintenance
            ? []
            : $slotService->getSlots($facility->id, $tanggal);

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
     * Pilihan slot disimpan di SESSION.
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
        if ($user && $user->role !== 'pengguna') {
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