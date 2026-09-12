<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Facility;
use App\Models\Reservation;
use App\Models\Report;

class AdminController extends Controller
{
    public function dashboard()
    {
        $users = User::latest()->get();
        $facilities = Facility::all();
        $reservations = Reservation::with(['facility', 'user'])->latest()->get();
        $reports = Report::with(['facility', 'user'])->latest()->get();

        return view('admin.dashboard', compact('users', 'facilities', 'reservations', 'reports'));
    }

    public function verifyUser($id)
    {
        $user = User::findOrFail($id);
        $user->update(['status_verifikasi' => 'verified']);
        return back()->with('success', "Akun {$user->name} berhasil diverifikasi.");
    }

    public function rejectUser($id)
    {
        $user = User::findOrFail($id);
        $user->update(['status_verifikasi' => 'rejected']);
        return back()->with('warning', "Akun {$user->name} telah ditolak.");
    }

    public function storePetugas(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'password' => 'nullable|min:6',
        ]);

        User::create([
            'name'              => $request->name,
            'email'             => $request->email,
            'password'          => Hash::make($request->password ?? 'password'),
            'role'              => 'petugas',
            'status_verifikasi' => 'verified',
        ]);

        return back()->with('success', 'Akun Petugas Sarana berhasil didaftarkan langsung.');
    }

    public function storePenggunaDirect(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'password' => 'nullable|min:6',
        ]);

        User::create([
            'name'              => $request->name,
            'email'             => $request->email,
            'password'          => Hash::make($request->password ?? 'password'),
            'role'              => 'pengguna',
            'status_verifikasi' => 'verified',
        ]);

        return back()->with('success', 'Akun Pengguna (Dosen/Staf) berhasil dibuat dengan status langsung terverifikasi.');
    }

    public function storeFacility(Request $request)
    {
        $validated = $request->validate([
            'nama_fasilitas' => 'required|string|max:150',
            'tipe'           => 'required|in:Ruangan,Laboratorium,Olahraga,Fasilitas Umum',
            'lokasi'         => 'required|string|max:100',
            'kapasitas'      => 'required|integer|min:1',
            'deskripsi'      => 'nullable|string',
        ]);

        Facility::create($validated);
        return back()->with('success', 'Fasilitas baru berhasil ditambahkan ke database.');
    }

    public function exportOkupansi()
    {
        $facilities = Facility::with(['reservations' => fn($q) => $q->where('status', 'approved')])->get();

        $filename = 'rekap_okupansi_fasilitas_' . date('Ymd_His') . '.csv';
        $handle = fopen('php://memory', 'w');
        fputcsv($handle, ['ID Fasilitas', 'Nama Fasilitas', 'Tipe', 'Lokasi', 'Total Reservasi Disetujui', 'Total Jam Penggunaan']);

        foreach ($facilities as $fac) {
            $totalHours = 0;
            foreach ($fac->reservations as $r) {
                $start = strtotime($r->start_time);
                $end = strtotime($r->end_time);
                $totalHours += ($end - $start) / 3600;
            }
            fputcsv($handle, [
                $fac->id,
                $fac->nama_fasilitas,
                $fac->tipe,
                $fac->lokasi,
                $fac->reservations->count(),
                number_format($totalHours, 1)
            ]);
        }

        fseek($handle, 0);
        return response()->stream(function () use ($handle) {
            fpassthru($handle);
        }, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}