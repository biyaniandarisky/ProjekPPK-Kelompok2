<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use Illuminate\Http\Request;

class FacilityController extends Controller
{
    /**
     * Daftar fasilitas untuk pengunjung (tanpa login) dan pengguna.
     */
    public function index(Request $request)
    {
        $query = Facility::where('status', '!=', 'nonaktif');

        // Filter pencarian
        if ($request->filled('q')) {
            $query->where('nama_fasilitas', 'like', '%' . $request->q . '%');
        }

        // Filter tipe
        if ($request->filled('tipe')) {
            $query->where('tipe', $request->tipe);
        }

        // Filter lokasi
        if ($request->filled('lokasi')) {
            $query->where('lokasi', $request->lokasi);
        }

        // Filter kapasitas minimal
        if ($request->filled('kapasitas') && is_numeric($request->kapasitas)) {
            $query->where('kapasitas', '>=', (int) $request->kapasitas);
        }

        $facilities = $query->orderBy('nama_fasilitas')->paginate(12)->withQueryString();

        // Daftar lokasi untuk dropdown
        $lokasiList = Facility::where('status', '!=', 'nonaktif')
            ->distinct()
            ->orderBy('lokasi')
            ->pluck('lokasi');

        // Daftar tipe untuk dropdown
        $tipeList = Facility::where('status', '!=', 'nonaktif')
            ->distinct()
            ->orderBy('tipe')
            ->pluck('tipe');

        return view('facilities.index', compact('facilities', 'lokasiList', 'tipeList'));
    }

    /**
     * Detail fasilitas + ketersediaan slot.
     */
    public function show($id, Request $request)
    {
        $facility = Facility::where('status', '!=', 'nonaktif')->findOrFail($id);
        $tanggal = $request->query('tanggal', now()->toDateString());

        return view('facilities.show', compact('facility', 'tanggal'));
    }
}