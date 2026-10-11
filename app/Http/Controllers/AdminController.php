<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Facility;
use App\Models\Reservation;
use App\Models\Report;
use App\Models\Notification;
use App\Services\SlotService;

class AdminController extends Controller
{
    /* =========================================================
     |  HELPER
     ========================================================= */

    /**
     * Jumlah jam operasional per hari — DIAMBIL dari SlotService::SLOTS
     * agar konsisten dengan sistem pemesanan (07.00-20.00, 26 slot @30 menit = 13 jam).
     */
    private function jamOperasionalPerHari(): float
    {
        return count(SlotService::SLOTS) * 0.5; // 26 × 0.5 = 13 jam
    }

    /** Redirect ke halaman (tab) dashboard admin dengan flash message. */
    private function toPage(string $page, string $msg, string $type = 'success')
    {
        return redirect()
            ->route('admin.dashboard', ['page' => $page])
            ->with($type, $msg);
    }

    private function penggunaQuery()
    {
        return User::where(function ($q) {
            $q->whereNull('role')->orWhereNotIn('role', ['admin', 'petugas']);
        });
    }

    private function parseDate($value, Carbon $default): Carbon
    {
        try {
            return $value ? Carbon::parse($value) : $default;
        } catch (\Throwable $e) {
            return $default;
        }
    }

    private function period(Request $request): array
    {
        $start = $this->parseDate($request->input('start_date'), now()->startOfMonth())->startOfDay();
        $end   = $this->parseDate($request->input('end_date'), now())->endOfDay();

        if ($start->gt($end)) {
            [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
        }

        return [$start, $end];
    }

    /* =========================================================
     |  REKAP PER FASILITAS
     ========================================================= */
    private function buildRekap(Request $request): Collection
    {
        [$start, $end] = $this->period($request);

        $hari        = max(1, (int) $start->copy()->startOfDay()->diffInDays($end->copy()->startOfDay()) + 1);
        $jamPerHari  = $this->jamOperasionalPerHari(); // 13 jam
        $jamTersedia = $hari * $jamPerHari;

        $facilities = Facility::query()
            ->when($request->filled('facility_id'), fn($q) => $q->where('id', $request->facility_id))
            ->withCount([
                // Reservasi: HANYA yang approved (konsisten dengan diagram dashboard)
                'reservations' => fn($q) => $q
                    ->where('status', 'approved')
                    ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()]),
                'reports' => fn($q) => $q->whereBetween('created_at', [$start, $end]),
                'reports as kerusakan_count' => fn($q) => $q
                    ->whereBetween('created_at', [$start, $end])
                    ->where('kategori_laporan', 'Kerusakan'),
                'reports as kebersihan_count' => fn($q) => $q
                    ->whereBetween('created_at', [$start, $end])
                    ->where('kategori_laporan', 'Kebersihan'),
                'reports as fasilitas_count' => fn($q) => $q
                    ->whereBetween('created_at', [$start, $end])
                    ->where('kategori_laporan', 'Fasilitas'),
                'reports as lainnya_count' => fn($q) => $q
                    ->whereBetween('created_at', [$start, $end])
                    ->where('kategori_laporan', 'Lainnya'),
            ])
            ->orderBy('nama_fasilitas')
            ->get();

        // Jam terpakai: HANYA dari reservasi approved
        $jamPerFasilitas = Reservation::whereIn('facility_id', $facilities->pluck('id'))
            ->where('status', 'approved')
            ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->get(['facility_id', 'start_time', 'end_time'])
            ->groupBy('facility_id')
            ->map(fn($rows) => $rows->sum(
                fn($r) => abs(Carbon::parse($r->start_time)->diffInMinutes(Carbon::parse($r->end_time))) / 60
            ));

        foreach ($facilities as $f) {
            $jam = (float) ($jamPerFasilitas[$f->id] ?? 0);
            $f->jam_terpakai = round($jam, 1);
            $f->okupansi     = min(100, (int) round($jam / $jamTersedia * 100));

            $f->total_kerusakan = $f->kerusakan_count + $f->kebersihan_count
                + $f->fasilitas_count + $f->lainnya_count;
        }

        return $facilities;
    }

    /** Rekap per LOKASI — dipakai hanya untuk export. */
    private function rekapPerLokasi(Collection $rekap): Collection
    {
        return $rekap
            ->groupBy(fn($f) => $f->lokasi ?: '(Tanpa lokasi)')
            ->map(fn($g, $lokasi) => (object) [
                'lokasi'    => $lokasi,
                'fasilitas' => $g->count(),
                'reservasi' => (int) $g->sum('reservations_count'),
                'jam'       => round($g->sum('jam_terpakai'), 1),
                'okupansi'  => (int) round($g->avg('okupansi')),
                'laporan'   => (int) $g->sum('reports_count'),
                'kerusakan' => (int) $g->sum('kerusakan_count'),
            ])
            ->sortByDesc('kerusakan')
            ->values();
    }

    private function hasActiveReservation($facilityId, bool $onlyUpcoming = true): bool
    {
        return Reservation::where('facility_id', $facilityId)
            ->whereIn('status', ['pending', 'approved'])
            ->when($onlyUpcoming, fn($q) => $q->where('tanggal', '>=', now()->toDateString()))
            ->exists();
    }

    /* =========================================================
     |  DASHBOARD
     ========================================================= */
    public function dashboard(Request $request)
    {
        // ---- Reservasi per hari: 7 HARI KE DEPAN mulai HARI INI ----
        // HANYA hitung status 'approved' (sudah disetujui petugas).
        $hariIndo = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
        $occupancy = [];
        $today = now()->startOfDay();

        for ($i = 0; $i < 7; $i++) {
            $tanggal  = $today->copy()->addDays($i);
            $namaHari = $hariIndo[$tanggal->dayOfWeek];
            $label    = $namaHari . ' ' . $tanggal->format('d/m');

            $occupancy[$label] = Reservation::whereDate('tanggal', $tanggal->toDateString())
                ->where('status', 'approved')
                ->count();
        }

        // ---- Frekuensi kerusakan global ----
        $totalLaporan = Report::count();
        $damageFreq = Report::selectRaw('kategori_laporan, COUNT(*) as jml')
            ->groupBy('kategori_laporan')
            ->pluck('jml', 'kategori_laporan')
            ->map(fn($j) => $totalLaporan ? (int) round($j / $totalLaporan * 100) : 0)
            ->toArray();

        // ---- Rekap untuk tab Rekapitulasi ----
        [$start, $end] = $this->period($request);
        $rekap = $this->buildRekap($request);

        $hariPeriode      = max(1, (int) $start->copy()->startOfDay()->diffInDays($end->copy()->startOfDay()) + 1);
        $jamPerHari       = $this->jamOperasionalPerHari();
        $totalJamTersedia = $hariPeriode * $jamPerHari;

        return view('admin.dashboard', [
            'totalUsers'            => User::count(),
            'reservationsThisMonth' => Reservation::whereMonth('tanggal', now()->month)
                ->whereYear('tanggal', now()->year)
                ->where('status', 'approved')
                ->count(),
            'reportsThisMonth'      => Report::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)->count(),
            'pendingUsers'          => $this->penggunaQuery()->where('status_verifikasi', 'pending')->latest()->get(),
            'occupancy'             => $occupancy,
            'damageFreq'            => $damageFreq,
            'petugas'               => User::where('role', 'petugas')->latest()->get(),
            'pengguna'              => $this->penggunaQuery()->latest()->get(),
            'facilities'            => Facility::orderBy('id')->get(),
            'rekap'                 => $rekap,
            'rekapLokasi'           => $this->rekapPerLokasi($rekap),
            'periode'               => [
                'start' => $start->translatedFormat('d M Y'),
                'end'   => $end->translatedFormat('d M Y'),
            ],
            'jumlahHariPeriode'     => $hariPeriode,
            'jamOperasionalPerHari' => $jamPerHari,
            'totalJamTersedia'      => $totalJamTersedia,
        ]);
    }

    /* =========================================================
     |  REDIRECT ALIAS
     ========================================================= */
    public function rekapIndex(Request $r)
    {
        return redirect()->route('admin.dashboard', array_merge($r->query(), ['page' => 'rekap']));
    }

    public function usersIndex()
    {
        return redirect()->route('admin.dashboard', ['page' => 'data_akun']);
    }

    public function usersCreate()
    {
        return redirect()->route('admin.dashboard', ['page' => 'tambah_akun']);
    }

    public function facilitiesIndex()
    {
        return redirect()->route('admin.dashboard', ['page' => 'fasilitas']);
    }

    /* =========================================================
     |  PENDAFTARAN AKUN OLEH ADMIN
     ========================================================= */
    private function createAccount(Request $request, string $role)
    {
        $rules = [
            'name'     => 'required|string|max:255',
            'nim_nip'  => 'required|string|max:30|unique:users,nim_nip',
            'email'    => 'required|email|max:255|unique:users,email',
            'no_hp'    => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
        ];

        if ($role === 'petugas') {
            $rules['unit'] = 'nullable|string|max:100';
        } else {
            $rules['tipe_pengguna'] = 'required|in:mahasiswa,dosen,staf';
        }

        $data = $request->validate($rules, [
            'email.unique'   => 'Email sudah terdaftar.',
            'nim_nip.unique' => 'NIM/NIP sudah terdaftar.',
        ]);

        $data['role']              = $role;
        $data['status_verifikasi'] = 'verified';

        if ($role === 'petugas') {
            $data['tipe_pengguna'] = 'petugas';
        }

        User::create($data);

        $label = $role === 'petugas' ? 'petugas' : 'pengguna';
        return $this->toPage('data_akun', "Akun {$label} berhasil didaftarkan.");
    }

    public function storePetugas(Request $request)
    {
        return $this->createAccount($request, 'petugas');
    }

    public function storePenggunaDirect(Request $request)
    {
        return $this->createAccount($request, 'pengguna');
    }

    public function storeUserByAdmin(Request $request)
    {
        $request->validate(['role' => 'required|in:petugas,pengguna']);
        return $this->createAccount($request, $request->input('role'));
    }

    public function destroyPetugas($id)
    {
        $user = User::where('role', 'petugas')->findOrFail($id);
        $nama = $user->name;

        try {
            $user->delete();
        } catch (QueryException $e) {
            return $this->toPage('data_akun', "Akun petugas {$nama} tidak bisa dihapus karena masih memiliki data terkait.", 'error');
        }

        return $this->toPage('data_akun', "Akun petugas {$nama} dihapus.");
    }

    public function destroyPengguna($id)
    {
        $user = $this->penggunaQuery()->findOrFail($id);
        $nama = $user->name;

        try {
            $user->delete();
        } catch (QueryException $e) {
            return $this->toPage('data_akun', "Akun pengguna {$nama} tidak bisa dihapus karena masih memiliki reservasi/laporan.", 'error');
        }

        return $this->toPage('data_akun', "Akun pengguna {$nama} berhasil dihapus.");
    }

    /* =========================================================
     |  VERIFIKASI
     ========================================================= */
    public function verifyUser($id)
    {
        $user = $this->penggunaQuery()->findOrFail($id);

        if ($user->status_verifikasi !== 'pending') {
            return $this->toPage('verifikasi', 'Akun ini tidak sedang menunggu verifikasi.', 'error');
        }

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

        $user = $this->penggunaQuery()->findOrFail($id);

        if ($user->status_verifikasi !== 'pending') {
            return $this->toPage('verifikasi', 'Akun ini tidak sedang menunggu verifikasi.', 'error');
        }

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
            'status'         => 'required|in:aktif,dalam_perbaikan,selesai,nonaktif',
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

        if ($data['status'] === 'nonaktif'
            && $facility->status !== 'nonaktif'
            && $this->hasActiveReservation($facility->id)) {
            return $this->toPage('fasilitas', 'Tidak bisa menonaktifkan — masih ada reservasi aktif/mendatang.', 'error');
        }

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

    public function toggleFacilityStatus($id)
    {
        $facility = Facility::findOrFail($id);

        if ($facility->status === 'nonaktif') {
            $facility->update(['status' => 'aktif']);
            return $this->toPage('fasilitas', 'Fasilitas diaktifkan kembali.');
        }

        if ($this->hasActiveReservation($facility->id)) {
            return $this->toPage('fasilitas', 'Tidak bisa menonaktifkan — masih ada reservasi aktif/mendatang.', 'error');
        }

        $facility->update(['status' => 'nonaktif']);

        return $this->toPage('fasilitas', 'Fasilitas berhasil dinonaktifkan.');
    }

    /* =========================================================
     |  EXPORT
     ========================================================= */
    public function exportFullData(Request $request, string $format)
    {
        abort_unless(in_array($format, ['csv', 'excel', 'pdf'], true), 404);

        [$start, $end] = $this->period($request);
        $periode = $start->format('d/m/Y') . ' - ' . $end->format('d/m/Y');

        $rekap = $this->buildRekap($request);

        $headOkupansi = [
            'ID Fasilitas', 'Nama Fasilitas', 'Tipe', 'Lokasi',
            'Total Reservasi (Approved)', 'Jam Terpakai', 'Okupansi (%)',
        ];
        $rowsOkupansi = $rekap->map(fn($f) => [
            'FAS-' . $f->id,
            $f->nama_fasilitas,
            $f->tipe,
            $f->lokasi,
            $f->reservations_count,
            $f->jam_terpakai,
            $f->okupansi,
        ])->all();

        $headKerusakan = [
            'Nama Fasilitas', 'Lokasi',
            'Kerusakan', 'Kebersihan', 'Fasilitas', 'Lainnya', 'Total',
        ];
        $rowsKerusakan = $rekap->map(fn($f) => [
            $f->nama_fasilitas,
            $f->lokasi,
            $f->kerusakan_count,
            $f->kebersihan_count,
            $f->fasilitas_count,
            $f->lainnya_count,
            $f->total_kerusakan,
        ])->all();

        $name = 'rekap_kampusreserve_' . date('Ymd_His');

        if ($format === 'csv') {
            return response()->streamDownload(function () use ($periode, $headOkupansi, $rowsOkupansi, $headKerusakan, $rowsKerusakan) {
                $out = fopen('php://output', 'w');
                fwrite($out, "\xEF\xBB\xBF");
                fputcsv($out, ['Rekap Okupansi & Frekuensi Kerusakan Fasilitas']);
                fputcsv($out, ['Periode', $periode]);
                fputcsv($out, ['Basis okupansi', '13 jam/hari (07.00-20.00)']);
                fputcsv($out, ['Catatan', 'Reservasi yang dihitung hanya yang berstatus APPROVED']);
                fputcsv($out, []);

                fputcsv($out, ['1. OKUPANSI PER FASILITAS']);
                fputcsv($out, $headOkupansi);
                foreach ($rowsOkupansi as $r) fputcsv($out, $r);
                fputcsv($out, []);

                fputcsv($out, ['2. FREKUENSI KERUSAKAN PER FASILITAS']);
                fputcsv($out, $headKerusakan);
                foreach ($rowsKerusakan as $r) fputcsv($out, $r);

                fclose($out);
            }, "{$name}.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
        }

        $sections = [
            '1. Okupansi per Fasilitas'            => [$headOkupansi, $rowsOkupansi],
            '2. Frekuensi Kerusakan per Fasilitas' => [$headKerusakan, $rowsKerusakan],
        ];

        if ($format === 'excel') {
            return response($this->exportHtml($sections, $periode, false), 200, [
                'Content-Type'        => 'application/vnd.ms-excel; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$name}.xls\"",
            ]);
        }

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return \Barryvdh\DomPDF\Facade\Pdf::loadHTML($this->exportHtml($sections, $periode, false))
                ->setPaper('a4', 'landscape')
                ->download("{$name}.pdf");
        }

        return response($this->exportHtml($sections, $periode, true));
    }

    private function exportHtml(array $sections, string $periode, bool $autoPrint): string
    {
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Rekap Okupansi &amp; Kerusakan</title>'
            . '<style>body{font-family:Arial,sans-serif;font-size:11px;color:#0f172a}'
            . 'h1{font-size:16px;margin:0 0 4px}h2{font-size:13px;margin:18px 0 6px}'
            . 'table{border-collapse:collapse;width:100%}th,td{border:1px solid #94a3b8;padding:4px 6px;text-align:left}'
            . 'th{background:#e2e8f0}@page{size:A4 landscape;margin:12mm}</style></head><body>'
            . '<h1>Rekap Okupansi &amp; Frekuensi Kerusakan Fasilitas</h1>'
            . '<p>Periode: ' . e($periode) . ' &nbsp;|&nbsp; Basis okupansi: 13 jam/hari (07.00-20.00)'
            . ' &nbsp;|&nbsp; Hanya reservasi <strong>approved</strong> yang dihitung.</p>';

        foreach ($sections as $title => [$head, $rows]) {
            $html .= '<h2>' . e($title) . '</h2><table border="1"><thead><tr>';
            foreach ($head as $h) {
                $html .= '<th>' . e($h) . '</th>';
            }
            $html .= '</tr></thead><tbody>';
            foreach ($rows as $r) {
                $html .= '<tr>';
                foreach ($r as $c) {
                    $html .= '<td>' . e($c) . '</td>';
                }
                $html .= '</tr>';
            }
            if (empty($rows)) {
                $html .= '<tr><td colspan="' . count($head) . '">Tidak ada data.</td></tr>';
            }
            $html .= '</tbody></table>';
        }

        if ($autoPrint) {
            $html .= '<script>window.onload=function(){window.print()}</script>';
        }

        return $html . '</body></html>';
    }
}