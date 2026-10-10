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

class AdminController extends Controller
{
    /* =========================================================
     |  HELPER
     ========================================================= */

    /** Redirect ke halaman (tab) dashboard admin dengan flash message. */
    private function toPage(string $page, string $msg, string $type = 'success')
    {
        return redirect()
            ->route('admin.dashboard', ['page' => $page])
            ->with($type, $msg);
    }

    /**
     * Query semua akun "pengguna" (bukan admin & bukan petugas).
     * whereNotIn saja TIDAK cocok dengan role NULL, jadi NULL ditambahkan manual.
     */
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

    /** Periode rekap: default awal bulan s/d hari ini. Tanggal terbalik otomatis ditukar. */
    private function period(Request $request): array
    {
        $start = $this->parseDate($request->input('start_date'), now()->startOfMonth())->startOfDay();
        $end   = $this->parseDate($request->input('end_date'), now())->endOfDay();

        if ($start->gt($end)) {
            [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
        }

        return [$start, $end];
    }

    /**
     * Rekap okupansi + frekuensi kerusakan PER FASILITAS pada periode terpilih.
     * Atribut tambahan per fasilitas: jam_terpakai, okupansi (%),
     * reservations_count, reports_count (semua laporan), kerusakan_count (kategori Kerusakan).
     */
    private function buildRekap(Request $request): Collection
    {
        [$start, $end] = $this->period($request);

        $hari        = max(1, (int) $start->copy()->startOfDay()->diffInDays($end->copy()->startOfDay()) + 1);
        $jamTersedia = $hari * 8; // asumsi 8 jam operasional per hari

        $facilities = Facility::query()
            ->when($request->filled('facility_id'), fn($q) => $q->where('id', $request->facility_id))
            ->withCount([
                'reservations' => fn($q) => $q
                    ->whereNotIn('status', ['rejected', 'cancelled'])
                    ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()]),
                'reports' => fn($q) => $q->whereBetween('created_at', [$start, $end]),
                'reports as kerusakan_count' => fn($q) => $q
                    ->whereBetween('created_at', [$start, $end])
                    ->where('kategori_laporan', 'Kerusakan'),
            ])
            ->orderBy('nama_fasilitas')
            ->get();

        // Satu query untuk semua jam terpakai (hindari N+1)
        $jamPerFasilitas = Reservation::whereIn('facility_id', $facilities->pluck('id'))
            ->whereNotIn('status', ['rejected', 'cancelled'])
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
        }

        return $facilities;
    }

    /** Rekap per LOKASI (agregasi dari rekap per fasilitas). */
    private function rekapPerLokasi(Collection $rekap): Collection
    {
        return $rekap
            ->groupBy(fn($f) => $f->lokasi ?: '(Tanpa lokasi)')
            ->map(fn($g, $lokasi) => (object) [
                'lokasi'      => $lokasi,
                'fasilitas'   => $g->count(),
                'reservasi'   => (int) $g->sum('reservations_count'),
                'jam'         => round($g->sum('jam_terpakai'), 1),
                'okupansi'    => (int) round($g->avg('okupansi')),
                'laporan'     => (int) $g->sum('reports_count'),
                'kerusakan'   => (int) $g->sum('kerusakan_count'),
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

        [$start, $end] = $this->period($request);
        $rekap = $this->buildRekap($request);

        return view('admin.dashboard', [
            'totalUsers'            => User::count(),
            'reservationsThisMonth' => Reservation::whereMonth('tanggal', now()->month)
                ->whereYear('tanggal', now()->year)->count(),
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
        ]);
    }

    /* =========================================================
     |  REDIRECT ALIAS (route lama -> tab di dashboard)
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
     |  PENDAFTARAN AKUN LANGSUNG OLEH ADMIN
     |  (petugas TIDAK registrasi mandiri; pengguna boleh didaftarkan admin)
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
        $data['status_verifikasi'] = 'verified'; // dibuat admin -> langsung aktif

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

    /** Endpoint generik (kompatibel dengan route lama /register-user). */
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
        // Hanya akun pengguna biasa yang boleh dihapus lewat route ini
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
     |  VERIFIKASI REGISTRASI MANDIRI PENGGUNA
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
     |  FASILITAS (tambah / edit / nonaktifkan)
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

        // Menonaktifkan lewat form edit juga harus dicek reservasi mendatang
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

    /** Nonaktifkan <-> Aktifkan. */
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
     |  EXPORT (CSV / EXCEL / PDF)
     |  Berisi: rekap okupansi + frekuensi kerusakan per fasilitas & per lokasi
     ========================================================= */
    public function exportFullData(Request $request, string $format)
    {
        abort_unless(in_array($format, ['csv', 'excel', 'pdf'], true), 404);

        [$start, $end] = $this->period($request);
        $periode = $start->format('d/m/Y') . ' - ' . $end->format('d/m/Y');

        $rekap  = $this->buildRekap($request);
        $lokasi = $this->rekapPerLokasi($rekap);

        $headFas = [
            'ID Fasilitas', 'Nama Fasilitas', 'Tipe', 'Lokasi', 'Kapasitas', 'Status',
            'Total Reservasi', 'Jam Terpakai', 'Okupansi (%)', 'Total Laporan', 'Frekuensi Kerusakan',
        ];
        $rowsFas = $rekap->map(fn($f) => [
            'FAS-' . $f->id, $f->nama_fasilitas, $f->tipe, $f->lokasi, $f->kapasitas, $f->status,
            $f->reservations_count, $f->jam_terpakai, $f->okupansi, $f->reports_count, $f->kerusakan_count,
        ])->all();

        $headLok = [
            'Lokasi', 'Jumlah Fasilitas', 'Total Reservasi', 'Jam Terpakai',
            'Rata-rata Okupansi (%)', 'Total Laporan', 'Frekuensi Kerusakan',
        ];
        $rowsLok = $lokasi->map(fn($l) => [
            $l->lokasi, $l->fasilitas, $l->reservasi, $l->jam, $l->okupansi, $l->laporan, $l->kerusakan,
        ])->all();

        $name = 'rekap_kampusreserve_' . date('Ymd_His');

        if ($format === 'csv') {
            return response()->streamDownload(function () use ($periode, $headFas, $rowsFas, $headLok, $rowsLok) {
                $out = fopen('php://output', 'w');
                fwrite($out, "\xEF\xBB\xBF"); // BOM agar Excel membaca UTF-8
                fputcsv($out, ['Rekap Okupansi & Frekuensi Kerusakan']);
                fputcsv($out, ['Periode', $periode]);
                fputcsv($out, []);
                fputcsv($out, ['REKAP PER FASILITAS']);
                fputcsv($out, $headFas);
                foreach ($rowsFas as $r) {
                    fputcsv($out, $r);
                }
                fputcsv($out, []);
                fputcsv($out, ['REKAP PER LOKASI']);
                fputcsv($out, $headLok);
                foreach ($rowsLok as $r) {
                    fputcsv($out, $r);
                }
                fclose($out);
            }, "{$name}.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
        }

        $sections = [
            'Rekap per Fasilitas' => [$headFas, $rowsFas],
            'Rekap per Lokasi'    => [$headLok, $rowsLok],
        ];

        if ($format === 'excel') {
            return response($this->exportHtml($sections, $periode, false), 200, [
                'Content-Type'        => 'application/vnd.ms-excel; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$name}.xls\"",
            ]);
        }

        // PDF: pakai DomPDF kalau terpasang (composer require barryvdh/laravel-dompdf),
        // kalau tidak, tampilkan halaman siap-cetak (Ctrl+P -> Save as PDF).
        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return \Barryvdh\DomPDF\Facade\Pdf::loadHTML($this->exportHtml($sections, $periode, false))
                ->setPaper('a4', 'landscape')
                ->download("{$name}.pdf");
        }

        return response($this->exportHtml($sections, $periode, true));
    }

    /** Bangun HTML tabel untuk export Excel & PDF. */
    private function exportHtml(array $sections, string $periode, bool $autoPrint): string
    {
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Rekap Okupansi &amp; Kerusakan</title>'
            . '<style>body{font-family:Arial,sans-serif;font-size:11px;color:#0f172a}'
            . 'h1{font-size:16px;margin:0 0 4px}h2{font-size:13px;margin:18px 0 6px}'
            . 'table{border-collapse:collapse;width:100%}th,td{border:1px solid #94a3b8;padding:4px 6px;text-align:left}'
            . 'th{background:#e2e8f0}@page{size:A4 landscape;margin:12mm}</style></head><body>'
            . '<h1>Rekap Okupansi &amp; Frekuensi Kerusakan Fasilitas</h1>'
            . '<p>Periode: ' . e($periode) . '</p>';

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