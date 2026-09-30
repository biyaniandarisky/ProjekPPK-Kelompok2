@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <!-- Title Section -->
    <div>
        <h1 class="text-3xl font-black text-slate-900">Halo, {{ explode(' ', auth()->user()->name)[0] }} 👋</h1>
        <p class="text-sm text-slate-500 mt-1">Verifikasi reservasi, kelola laporan kendala, dan atur status fasilitas.</p>
    </div>

    <!-- Statistik Ringkas (klik untuk buka halaman terkait) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <a href="{{ route('petugas.reservasi.index') }}"
           class="group flex items-center gap-4 rounded-2xl p-5 shadow bg-gradient-to-br from-amber-400 to-amber-500 text-white hover:shadow-lg hover:-translate-y-0.5 transition">
            <div class="w-11 h-11 rounded-xl bg-white/25 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-bold text-amber-50">Baru Masuk (Permintaan)</p>
                <p class="text-3xl font-black leading-tight">{{ $stats['baru_masuk'] }}</p>
            </div>
            <svg class="w-4 h-4 ml-auto text-amber-100 group-hover:translate-x-1 transition shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
            </svg>
        </a>

        <a href="{{ route('petugas.reservasi.index') }}"
           class="group flex items-center gap-4 rounded-2xl p-5 shadow bg-gradient-to-br from-blue-600 to-blue-800 text-white hover:shadow-lg hover:-translate-y-0.5 transition">
            <div class="w-11 h-11 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2"/>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-bold text-blue-100">Masih Berjalan</p>
                <p class="text-3xl font-black leading-tight">{{ $stats['masih_berjalan'] }}</p>
            </div>
            <svg class="w-4 h-4 ml-auto text-blue-100 group-hover:translate-x-1 transition shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
            </svg>
        </a>

        <a href="{{ route('petugas.reservasi.index') }}"
           class="group flex items-center gap-4 rounded-2xl p-5 shadow bg-gradient-to-br from-emerald-500 to-emerald-700 text-white hover:shadow-lg hover:-translate-y-0.5 transition">
            <div class="w-11 h-11 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.6-3A12 12 0 0112 2.9 12 12 0 013.4 6 12 12 0 003 9c0 5.6 3.8 10.3 9 11.6 5.2-1.3 9-6 9-11.6 0-1-.1-2-.4-3z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-bold text-emerald-50">Disetujui</p>
                <p class="text-3xl font-black leading-tight">{{ $stats['disetujui'] }}</p>
            </div>
            <svg class="w-4 h-4 ml-auto text-emerald-100 group-hover:translate-x-1 transition shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
            </svg>
        </a>
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