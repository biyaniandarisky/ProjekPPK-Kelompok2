<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Facility;
use App\Models\Reservation;
use App\Models\Report;

class AdminController extends Controller
{
    /* =========================================================
     |  DASHBOARD
     ========================================================= */
    public function dashboard(Request $request)
    {
        $totalUsers        = User::count();
        $totalReservations = Reservation::count();
        $totalReports      = Report::count();
        $pendingUsers      = User::where('status_verifikasi', 'pending')->get();

        // Ambil filter tanggal jika ada
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');

        // Query fasilitas + filter hitungan berdasarkan rentang tanggal
        $facilities = Facility::withCount([
            'reservations' => function ($query) use ($startDate, $endDate) {
                if ($startDate && $endDate) {
                    $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                }
            },
            'reports' => function ($query) use ($startDate, $endDate) {
                if ($startDate && $endDate) {
                    $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                }
            }
        ])->latest()->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalReservations',
            'totalReports',
            'pendingUsers',
            'facilities',
            'startDate',
            'endDate'
        ));
    }

    /* =========================================================
     |  REKAP OKUPANSI (HALAMAN) — TAMBAHAN BARU
     ========================================================= */
    public function rekapOkupansi(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');

        $facilities = Facility::withCount([
            'reservations' => function ($query) use ($startDate, $endDate) {
                if ($startDate && $endDate) {
                    $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                }
            },
            'reports' => function ($query) use ($startDate, $endDate) {
                if ($startDate && $endDate) {
                    $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                }
            }
        ])->orderBy('nama_fasilitas')->get();

        return view('admin.rekap.okupansi', compact('facilities', 'startDate', 'endDate'));
    }

    /* =========================================================
     |  REKAP KERUSAKAN (HALAMAN) — TAMBAHAN BARU
     ========================================================= */
    public function rekapKerusakan(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');

        $query = Report::with(['user', 'facility', 'petugas']);

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }

        $reports = $query->orderBy('created_at', 'desc')->get();

        return view('admin.rekap.kerusakan', compact('reports', 'startDate', 'endDate'));
    }

    /* =========================================================
     |  REQ 14 & 15: PENDAFTARAN LANGSUNG OLEH ADMIN
     ========================================================= */
    public function storeUserByAdmin(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'role'     => 'required|in:petugas,mahasiswa,dosen,staf',
            'nip_nim'  => 'nullable|string',
            'no_hp'    => 'nullable|string',
            'unit'     => 'nullable|string',
            'password' => 'required|min:8',
        ]);

        User::create([
            'name'              => $request->name,
            'email'             => $request->email,
            'role'              => $request->role,
            'nip'               => $request->nip_nim,
            'no_hp'             => $request->no_hp,
            'unit'              => $request->unit,
            'password'          => Hash::make($request->password),
            'status_verifikasi' => 'verified',
        ]);

        return back()->with('success', "Akun {$request->role} berhasil didaftarkan secara langsung.");
    }

    public function verifyUser($id)
    {
        User::findOrFail($id)->update(['status_verifikasi' => 'verified']);
        return back()->with('success', 'Akun berhasil diverifikasi.');
    }

    /** Menampilkan berkas KTM/KTP (disk private) khusus untuk admin. */
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

    public function rejectUser($id)
    {
        User::findOrFail($id)->update(['status_verifikasi' => 'rejected']);
        return back()->with('success', 'Pengajuan akun ditolak.');
    }

    /* =========================================================
     |  REQ 16: KELOLA FASILITAS
     ========================================================= */
    public function storeFacility(Request $request)
    {
        $request->validate([
            'nama_fasilitas' => 'required|string|max:150',
            'tipe'           => 'required|in:Ruangan,Laboratorium,Olahraga,Fasilitas Umum',
            'lokasi'         => 'required|string|max:100',
            'kapasitas'      => 'required|numeric',
        ]);

        Facility::create([
            'nama_fasilitas' => $request->nama_fasilitas,
            'tipe'           => $request->tipe,
            'lokasi'         => $request->lokasi,
            'kapasitas'      => $request->kapasitas,
            'status'         => 'aktif',
        ]);

        return back()->with('success', 'Fasilitas baru berhasil ditambahkan.');
    }

    public function updateFacility(Request $request, $id)
    {
        $facility = Facility::findOrFail($id);

        $request->validate([
            'nama_fasilitas' => 'required|string|max:150',
            'tipe'           => 'required|in:Ruangan,Laboratorium,Olahraga,Fasilitas Umum',
            'lokasi'         => 'required|string|max:100',
            'kapasitas'      => 'required|numeric',
        ]);

        $facility->update([
            'nama_fasilitas' => $request->nama_fasilitas,
            'tipe'           => $request->tipe,
            'lokasi'         => $request->lokasi,
            'kapasitas'      => $request->kapasitas,
        ]);

        return back()->with('success', 'Fasilitas berhasil diperbarui.');
    }

    public function toggleFacilityStatus($id)
    {
        $facility  = Facility::findOrFail($id);
        $newStatus = $facility->status === 'aktif' ? 'nonaktif' : 'aktif';
        $facility->update(['status' => $newStatus]);

        return back()->with('success', 'Status fasilitas berhasil diubah.');
    }

    /* =========================================================
     |  REQ 17: EXPORT FULL DATA REKAP (CSV / EXCEL / PDF)
     ========================================================= */
    public function exportFullData(Request $request, $format)
    {
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');

        $facilities = Facility::withCount([
            'reservations' => function ($query) use ($startDate, $endDate) {
                if ($startDate && $endDate) {
                    $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                }
            },
            'reports' => function ($query) use ($startDate, $endDate) {
                if ($startDate && $endDate) {
                    $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                }
            }
        ])->get();

        // ===== PDF: render view, user tinggal print/save as PDF =====
        if ($format === 'pdf') {
            return response()
                ->view('admin.rekap.pdf', compact('facilities', 'startDate', 'endDate'))
                ->header('Content-Type', 'text/html');
        }

        $filename = "rekap_full_data_kampusreserve_" . date('Ymd_His')
                  . "." . ($format === 'excel' ? 'xls' : 'csv');

        // ===== EXCEL (HTML table, bisa dibuka Excel) =====
        if ($format === 'excel') {
            $html  = '<table border="1">';
            $html .= '<tr>';
            $html .= '<th>ID Fasilitas</th><th>Nama Fasilitas</th><th>Tipe</th><th>Lokasi</th>';
            $html .= '<th>Kapasitas</th><th>Status</th><th>Total Okupansi</th><th>Total Kerusakan</th>';
            $html .= '</tr>';

            foreach ($facilities as $f) {
                $html .= '<tr>';
                $html .= '<td>FAS-' . $f->id . '</td>';
                $html .= '<td>' . e($f->nama_fasilitas) . '</td>';
                $html .= '<td>' . e($f->tipe) . '</td>';
                $html .= '<td>' . e($f->lokasi) . '</td>';
                $html .= '<td>' . e($f->kapasitas) . '</td>';
                $html .= '<td>' . e($f->status) . '</td>';
                $html .= '<td>' . e($f->reservations_count) . '</td>';
                $html .= '<td>' . e($f->reports_count) . '</td>';
                $html .= '</tr>';
            }
            $html .= '</table>';

            return response($html, 200, [
                'Content-Type'        => 'application/vnd.ms-excel',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        }

        // ===== CSV (default) =====
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () use ($facilities) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'ID Fasilitas',
                'Nama Fasilitas',
                'Tipe',
                'Lokasi',
                'Kapasitas',
                'Status',
                'Total Okupansi (Reservasi)',
                'Total Kerusakan (Laporan)'
            ]);

            foreach ($facilities as $f) {
                fputcsv($file, [
                    'FAS-' . $f->id,
                    $f->nama_fasilitas,
                    $f->tipe,
                    $f->lokasi,
                    $f->kapasitas,
                    $f->status,
                    $f->reservations_count,
                    $f->reports_count
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}