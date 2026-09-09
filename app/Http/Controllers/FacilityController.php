<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use Illuminate\Http\Request;

class FacilityController extends Controller
{
    /**
     * Menampilkan daftar semua fasilitas kampus.
     */
    public function index()
    {
        // Mengambil semua data fasilitas dari database Aiven
        $facilities = Facility::all(); 

        // Mengirim data $facilities ke halaman view 'facilities.index'
        return view('facilities.index', compact('facilities')); 
    }
}