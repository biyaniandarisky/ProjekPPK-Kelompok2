@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    <div>
        <h1 class="text-2xl font-black text-slate-900">Panel Petugas Sarana</h1>
        <p class="text-sm text-slate-500">Verifikasi reservasi, kelola laporan kendala, dan atur status fasilitas.</p>
    </div>

    <!-- Statistik Reservasi -->
    <div>
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Statistik Reservasi</p>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="bg-white rounded-2xl p-5 shadow border border-slate-100">
                <p class="text-xs font-bold text-slate-500">Total Reservasi</p>
                <p class="text-2xl font-black text-blue-900">{{ $stats['total_reservasi'] }}</p>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow border border-slate-100">
                <p class="text-xs font-bold text-slate-500">Menunggu</p>
                <p class="text-2xl font-black text-amber-500">{{ $stats['menunggu'] }}</p>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow border border-slate-100">
                <p class="text-xs font-bold text-slate-500">Disetujui</p>
                <p class="text-2xl font-black text-emerald-600">{{ $stats['disetujui'] }}</p>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow border border-slate-100">
                <p class="text-xs font-bold text-slate-500">Ditolak</p>
                <p class="text-2xl font-black text-rose-600">{{ $stats['ditolak'] }}</p>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow border border-slate-100">
                <p class="text-xs font-bold text-slate-500">Dibatalkan</p>
                <p class="text-2xl font-black text-slate-500">{{ $stats['dibatalkan'] }}</p>
            </div>
        </div>
    </div>

    <!-- Statistik Laporan -->
    <div>
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Statistik Laporan</p>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl p-5 shadow border border-slate-100">
                <p class="text-xs font-bold text-slate-500">Total Laporan</p>
                <p class="text-2xl font-black text-blue-900">{{ $stats['total_laporan'] }}</p>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow border border-slate-100">
                <p class="text-xs font-bold text-slate-500">Baru</p>
                <p class="text-2xl font-black text-amber-500">{{ $stats['laporan_baru'] }}</p>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow border border-slate-100">
                <p class="text-xs font-bold text-slate-500">Diproses</p>
                <p class="text-2xl font-black text-blue-700">{{ $stats['laporan_proses'] }}</p>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow border border-slate-100">
                <p class="text-xs font-bold text-slate-500">Selesai</p>
                <p class="text-2xl font-black text-emerald-600">{{ $stats['laporan_selesai'] }}</p>
            </div>
        </div>
    </div>

    <!-- Statistik Fasilitas -->
    <div>
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Statistik Fasilitas</p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl p-5 shadow border border-slate-100">
                <p class="text-xs font-bold text-slate-500">Total Fasilitas</p>
                <p class="text-2xl font-black text-blue-900">{{ $stats['total_fasilitas'] }}</p>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow border border-slate-100">
                <p class="text-xs font-bold text-slate-500">Aktif</p>
                <p class="text-2xl font-black text-emerald-600">{{ $stats['fasilitas_aktif'] }}</p>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow border border-slate-100">
                <p class="text-xs font-bold text-slate-500">Dalam Perbaikan</p>
                <p class="text-2xl font-black text-amber-500">{{ $stats['fasilitas_perbaikan'] }}</p>
            </div>
        </div>
    </div>

    <!-- Kartu Menu Utama -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">
        <!-- Kartu 1: Reservasi -->
        <a href="{{ route('petugas.reservasi.index') }}"
           class="group relative bg-white rounded-2xl p-6 shadow border border-slate-100 overflow-hidden cursor-pointer hover:shadow-lg transition duration-300 flex flex-col justify-between min-h-[220px]">
            <div class="absolute inset-0 bg-gradient-to-r from-blue-950/90 to-blue-900/80 z-10"></div>
            <img src="{{ asset('images/reservasi.jpg') }}"
                 alt="Reservasi Fasilitas"
                 class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition duration-500">

            <div class="relative z-20 text-white space-y-2">
                <span class="bg-blue-500/30 text-blue-200 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider border border-blue-400/30">
                    Verifikasi Fasilitas
                </span>
                <h2 class="text-xl font-black leading-snug">Reservasi</h2>
                <p class="text-xs text-slate-200 max-w-sm">Kelola antrian reservasi: setujui, tolak, atau batalkan secara darurat.</p>
            </div>

            <div class="relative z-20 pt-4 flex items-center text-xs font-bold text-blue-200 group-hover:text-white transition">
                <span>Buka Daftar Reservasi</span>
                <svg class="w-4 h-4 ml-1 transform group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                </svg>
            </div>
        </a>

        <!-- Kartu 2: Laporan -->
        <a href="{{ route('petugas.laporan.index') }}"
           class="group relative bg-white rounded-2xl p-6 shadow border border-slate-100 overflow-hidden cursor-pointer hover:shadow-lg transition duration-300 flex flex-col justify-between min-h-[220px]">
            <div class="absolute inset-0 bg-gradient-to-r from-rose-950/90 to-rose-900/80 z-10"></div>
            <img src="{{ asset('images/laporan.jpg') }}"
                 alt="Laporan Kendala Fasilitas"
                 class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition duration-500">

            <div class="relative z-20 text-white space-y-2">
                <span class="bg-rose-500/30 text-rose-200 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider border border-rose-400/30">
                    Layanan Teknis
                </span>
                <h2 class="text-xl font-black leading-snug">Laporan</h2>
                <p class="text-xs text-slate-200 max-w-sm">Proses laporan kendala yang masuk dari pengguna sampai selesai ditangani.</p>
            </div>

            <div class="relative z-20 pt-4 flex items-center text-xs font-bold text-rose-200 group-hover:text-white transition">
                <span>Buka Laporan Kendala</span>
                <svg class="w-4 h-4 ml-1 transform group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                </svg>
            </div>
        </a>
        <!-- Kartu 3: Fasilitas -->
        <a href="{{ route('petugas.fasilitas.index') }}"
           class="group relative bg-white rounded-2xl p-6 shadow border border-slate-100 overflow-hidden cursor-pointer hover:shadow-lg transition duration-300 flex flex-col justify-between min-h-[220px]">
            <div class="absolute inset-0 bg-gradient-to-r from-emerald-950/90 to-emerald-900/80 z-10"></div>
            <img src="{{ asset('images/fasilitas.jpg') }}"
                 alt="Status Fasilitas"
                 class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition duration-500">

            <div class="relative z-20 text-white space-y-2">
                <span class="bg-emerald-500/30 text-emerald-100 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider border border-emerald-400/30">
                    Status Sarana
                </span>
                <h2 class="text-xl font-black leading-snug">Fasilitas</h2>
                <p class="text-xs text-slate-200 max-w-sm">Ubah status fasilitas: Dalam Perbaikan, Selesai, atau Aktifkan kembali. Laporan terkait ikut menyesuaikan.</p>
            </div>

            <div class="relative z-20 pt-4 flex items-center text-xs font-bold text-emerald-200 group-hover:text-white transition">
                <span>Buka Daftar Fasilitas</span>
                <svg class="w-4 h-4 ml-1 transform group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                </svg>
            </div>
        </a>
    </div>
</div>
@endsection
