<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Facility;
use App\Models\Reservation;
use App\Models\Report;
use App\Models\Notification;

class AdminController extends Controller
{
    /* =========================================================
     |  HELPER
     ========================================================= */
    private function toPage(string $page, string $msg)
    {
        return redirect()
            ->route('admin.dashboard', ['page' => $page])
            ->with('success', $msg);
    }

    private function buildRekap(Request $request)
    {
        $start = $request->filled('start_date')
            ? Carbon::parse($request->start_date)->startOfDay()
            : now()->startOfMonth();

        $end = $request->filled('end_date')
            ? Carbon::parse($request->end_date)->endOfDay()
            : now()->endOfDay();

        $hari = max(1, $start->copy()->startOfDay()->diffInDays($end->copy()->startOfDay()) + 1);
        $jamTersedia = $hari * 8;

        $facilities = Facility::query()
            ->when($request->facility_id, fn($q, $id) => $q->where('id', $id))
            ->withCount([
                'reservations' => fn($q) => $q
                    ->whereNotIn('status', ['rejected', 'cancelled'])
                    ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()]),
                'reports' => fn($q) => $q->whereBetween('created_at', [$start, $end]),
            ])
            ->orderBy('nama_fasilitas')
            ->get();

        foreach ($facilities as $f) {
            $jam = 0;
            $f->reservations()
                ->whereNotIn('status', ['rejected', 'cancelled'])
                ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
                ->get(['start_time', 'end_time'])
                ->each(function ($r) use (&$jam) {
                    $jam += max(0, Carbon::parse($r->start_time)->diffInMinutes(Carbon::parse($r->end_time)) / 60);
                });

            $f->jam_terpakai = round($jam, 1);
            $f->okupansi = min(100, (int) round($jam / $jamTersedia * 100));
        }

        return $facilities;
    }

    /* =========================================================
     |  DASHBOARD
     ========================================================= */
    public function dashboard(Request $request)
    {
        $occupancy = [];
        foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum'] as $i => $nama) {
            $occupancy[$nama] = Reservation::whereDate('tanggal', now()->startOfWeek()->addDays($i))
                ->whereNotIn('status', ['rejected', 'cancelled'])
                ->count();
        }

        $totalLaporan = Report::count();
        $damageFreq = Report::selectRaw('kategori_laporan, COUNT(*) as jml')
            ->groupBy('kategori_laporan')
            ->pluck('jml', 'kategori_laporan')
            ->map(fn($j) => $totalLaporan ? (int) round($j / $totalLaporan * 100) : 0)
            ->toArray();

        return view('admin.dashboard', [
            'totalUsers'            => User::count(),
            'reservationsThisMonth' => Reservation::whereMonth('tanggal', now()->month)
                ->whereYear('tanggal', now()->year)->count(),
            'reportsThisMonth'      => Report::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)->count(),
            'pendingUsers'          => User::where('status_verifikasi', 'pending')->get(),
            'occupancy'             => $occupancy,
            'damageFreq'            => $damageFreq,
            'petugas'               => User::where('role', 'petugas')->latest()->get(),
            'facilities'            => Facility::orderBy('id')->get(),
            'rekap'                 => $this->buildRekap($request),
        ]);
    }

    /* =========================================================
     |  REDIRECT ALIAS (route lama)
     ========================================================= */
    public function rekapOkupansi(Request $r)
    {
        return redirect()->route('admin.dashboard', array_merge($r->query(), ['page' => 'rekap']));
    }

    public function rekapKerusakan(Request $r)
    {
        return redirect()->route('admin.dashboard', array_merge($r->query(), ['page' => 'rekap']));
    }

    public function usersIndex()
    {
        return redirect()->route('admin.dashboard', ['page' => 'verifikasi']);
    }

    public function facilitiesIndex()
    {
        return redirect()->route('admin.dashboard', ['page' => 'fasilitas']);
    }

    /* =========================================================
     |  AKUN PETUGAS
     ========================================================= */
    public function storePetugas(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'nim_nip'  => 'required|string|max:30|unique:users,nim_nip',
            'email'    => 'required|email|unique:users,email',
            'no_hp'    => 'nullable|string|max:20',
            'unit'     => 'nullable|string|max:100',
            'password' => 'required|min:8',
        ]);

        User::create(array_merge($data, [
            'role'              => 'petugas',
            'tipe_pengguna'     => 'petugas',
            'status_verifikasi' => 'verified',
        ]));

        return $this->toPage('petugas', 'Akun petugas berhasil didaftarkan.');
    }

    public function destroyPetugas($id)
    {
        User::where('role', 'petugas')->findOrFail($id)->delete();
        return $this->toPage('petugas', 'Akun petugas dihapus.');
    }

    /* =========================================================
     |  AKUN PENGGUNA (Daftar Langsung)
     ========================================================= */
    public function storeUserByAdmin(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'role'     => 'required|in:petugas,pengguna',
            'nim_nip'  => 'nullable|string|max:30|unique:users,nim_nip',
            'no_hp'    => 'nullable|string|max:20',
            'unit'     => 'nullable|string|max:100',
            'password' => 'required|min:8',
        ]);

        User::create(array_merge($data, ['status_verifikasi' => 'verified']));

        return back()->with('success', "Akun {$data['role']} berhasil didaftarkan.");
    }

    public function storePenggunaDirect(Request $request)
    {
        $request->merge(['role' => 'pengguna']);
        return $this->storeUserByAdmin($request);
    }

    /* =========================================================
     |  VERIFIKASI USER
     ========================================================= */
    public function verifyUser($id)
    {
        $user = User::findOrFail($id);
        $user->update(['status_verifikasi' => 'verified']);

        Notification::create([
            'user_id' => $user->id,
            'judul'   => 'Akun Diverifikasi',
            'pesan'   => 'Akun Anda telah diverifikasi oleh admin. Silakan login.',
            'tipe'    => 'success',
            'link'    => route('login'),
        ]);

        return $this->toPage('verifikasi', 'Akun berhasil diverifikasi.');
    }

    public function rejectUser(Request $request, $id)
    {
        $request->validate([
            'alasan_tolak' => 'required|string|max:250',
        ], [
            'alasan_tolak.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $user = User::findOrFail($id);
        $user->update(['status_verifikasi' => 'rejected']);

        Notification::create([
            'user_id' => $user->id,
            'judul'   => 'Akun Ditolak',
            'pesan'   => 'Verifikasi akun Anda ditolak. Alasan: ' . $request->alasan_tolak,
            'tipe'    => 'danger',
            'link'    => route('login'),
        ]);

        return $this->toPage('verifikasi', 'Pengajuan akun ditolak.');
    }

    public function showKtm($id)
    {
        $user = User::findOrFail($id);

        abort_unless(
            $user->ktm_path && Storage::disk('local')->exists($user->ktm_path),
            404,
            'Berkas KTM/KTP tidak ditemukan.'
        );

        return Storage::disk('local')->response($user->ktm_path);
    }

    /* =========================================================
     |  FASILITAS
     ========================================================= */
    private function facilityRules(): array
    {
        return [
            'nama_fasilitas' => 'required|string|max:150',
            'tipe'           => 'required|in:Ruangan,Laboratorium,Olahraga,Fasilitas Umum',
            'lokasi'         => 'required|string|max:100',
            'kapasitas'      => 'required|integer|min:1',
            'deskripsi'      => 'nullable|string',
            'status'         => 'required|in:aktif,dalam_perbaikan,nonaktif',
            'foto'           => 'nullable|image|max:2048',
        ];
    }

    public function storeFacility(Request $request)
    {
        $data = $request->validate($this->facilityRules());

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('facilities', 'public');
        }

        Facility::create($data);

        return $this->toPage('fasilitas', 'Fasilitas baru berhasil ditambahkan.');
    }

    public function updateFacility(Request $request, $id)
    {
        $facility = Facility::findOrFail($id);
        $data = $request->validate($this->facilityRules());

        if ($request->hasFile('foto')) {
            if ($facility->foto) {
                Storage::disk('public')->delete($facility->foto);
            }
            $data['foto'] = $request->file('foto')->store('facilities', 'public');
        } else {
            unset($data['foto']);
        }

        $facility->update($data);

        return $this->toPage('fasilitas', 'Fasilitas berhasil diperbarui.');
    }

    public function destroyFacility($id)
    {
        $facility = Facility::findOrFail($id);

        // Cek reservasi aktif
        $adaReservasi = Reservation::where('facility_id', $id)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($adaReservasi) {
            return $this->toPage('fasilitas', 'Tidak bisa hapus fasilitas — masih ada reservasi aktif.');
        }

        if ($facility->foto) {
            Storage::disk('public')->delete($facility->foto);
        }

        $facility->delete();

        return $this->toPage('fasilitas', 'Fasilitas dihapus.');
    }

    public function toggleFacilityStatus($id)
    {
        $facility = Facility::findOrFail($id);

        // Cek reservasi aktif kalau mau nonaktifkan
        if ($facility->status === 'aktif') {
            $adaReservasi = Reservation::where('facility_id', $id)
                ->whereIn('status', ['pending', 'approved'])
                ->where('tanggal', '>=', now()->toDateString())
                ->exists();

            if ($adaReservasi) {
                return $this->toPage('fasilitas', 'Tidak bisa nonaktifkan — masih ada reservasi aktif.');
            }
        }

        $facility->update([
            'status' => $facility->status === 'aktif' ? 'nonaktif' : 'aktif',
        ]);

        return $this->toPage('fasilitas', 'Status fasilitas berhasil diubah.');
    }

    /* =========================================================
     |  EXPORT (CSV / EXCEL / PDF)
     ========================================================= */
    public function exportFullData(Request $request, $format)
    {
        $rekap = $this->buildRekap($request);
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        if ($format === 'pdf') {
            return view('admin.rekap.pdf', compact('rekap', 'startDate', 'endDate'));
        }

        $head = [
            'ID Fasilitas', 'Nama Fasilitas', 'Tipe', 'Lokasi',
            'Kapasitas', 'Status', 'Total Reservasi',
            'Jam Terpakai', 'Okupansi (%)', 'Total Laporan'
        ];

        $rows = $rekap->map(fn($f) => [
            'FAS-' . $f->id,
            $f->nama_fasilitas,
            $f->tipe,
            $f->lokasi,
            $f->kapasitas,
            $f->status,
            $f->reservations_count,
            $f->jam_terpakai,
            $f->okupansi,
            $f->reports_count,
        ]);

        $name = 'rekap_kampusreserve_' . date('Ymd_His');

        if ($format === 'excel') {
            $html = '<table border="1"><tr>' .
                collect($head)->map(fn($h) => "<th>$h</th>")->implode('') .
                '</tr>';

            foreach ($rows as $r) {
                $html .= '<tr>' .
                    collect($r)->map(fn($c) => '<td>' . e($c) . '</td>')->implode('') .
                    '</tr>';
            }

            return response($html . '</table>', 200, [
                'Content-Type'        => 'application/vnd.ms-excel',
                'Content-Disposition' => "attachment; filename=\"{$name}.xls\"",
            ]);
        }

        return response()->streamDownload(function () use ($head, $rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $head);
            foreach ($rows as $r) {
                fputcsv($out, $r);
            }
            fclose($out);
        }, "{$name}.csv", ['Content-Type' => 'text/csv']);
    }
}