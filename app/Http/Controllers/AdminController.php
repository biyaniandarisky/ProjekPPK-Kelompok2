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
    public function dashboard(Request $request)
    {
        $totalUsers = User::count();
        $totalReservations = Reservation::count();
        $totalReports = Report::count();
        $pendingUsers = User::where('status_verifikasi', 'pending')->get();

        // Ambil filter tanggal jika ada
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

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

    // Req 14 & 15: Pendaftaran langsung Petugas / Pengguna oleh Admin
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
        abort_unless($user->ktm_path && Storage::disk('local')->exists($user->ktm_path), 404, 'Berkas KTM/KTP tidak ditemukan.');
        return Storage::disk('local')->response($user->ktm_path);
    }

    public function rejectUser($id)
    {
        User::findOrFail($id)->update(['status_verifikasi' => 'rejected']);
        return back()->with('success', 'Pengajuan akun ditolak.');
    }

    // Req 16: Kelola Fasilitas
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

    public function toggleFacilityStatus($id)
    {
        $facility = Facility::findOrFail($id);
        $newStatus = $facility->status === 'aktif' ? 'nonaktif' : 'aktif';
        $facility->update(['status' => $newStatus]);

        return back()->with('success', 'Status fasilitas berhasil diubah.');
    }

    // Req 17: Ekspor Full Data Rekap (Sesuai Filter Tanggal)
    public function exportFullData(Request $request, $format)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Filter hitungan rekap sebelum di-export
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

        if ($format === 'pdf') {
            return back()->with('info', 'Export PDF sedang diproses.');
        }

        $filename = "rekap_full_data_kampusreserve_" . date('Ymd_His') . "." . ($format === 'excel' ? 'xlsx' : 'csv');
        
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($facilities) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID Fasilitas', 'Nama Fasilitas', 'Tipe', 'Lokasi', 'Kapasitas', 'Status', 'Total Okupansi (Reservasi)', 'Total Kerusakan (Laporan)']);

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