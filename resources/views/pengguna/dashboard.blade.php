@extends('layouts.app')

@section('content')

    <!-- Title Section -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-900">Panel Mahasiswa / Dosen</h1>
            <p class="text-sm text-slate-500">Kelola reservasi dan laporan kendala fasilitas kamu di sini.</p>
        </div>
    </div>

    <!-- Statistik Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <a href="{{ route('pengguna.reservasi.index') }}" class="bg-white rounded-2xl p-5 shadow border border-slate-100 hover:shadow-md transition">
            <p class="text-xs font-bold text-slate-500">Total Reservasi</p>
            <p class="text-2xl font-black text-blue-900">{{ $stats['total_reservasi'] ?? 0 }}</p>
        </a>
        <a href="{{ route('pengguna.reservasi.index', ['status' => 'approved']) }}" class="bg-white rounded-2xl p-5 shadow border border-slate-100 hover:shadow-md transition">
            <p class="text-xs font-bold text-slate-500">Disetujui</p>
            <p class="text-2xl font-black text-emerald-600">{{ $stats['disetujui'] ?? 0 }}</p>
        </a>
        <a href="{{ route('pengguna.reservasi.index', ['status' => 'pending']) }}" class="bg-white rounded-2xl p-5 shadow border border-slate-100 hover:shadow-md transition">
            <p class="text-xs font-bold text-slate-500">Menunggu</p>
            <p class="text-2xl font-black text-amber-500">{{ $stats['menunggu'] ?? 0 }}</p>
        </a>
        <a href="{{ route('pengguna.laporan.index') }}" class="bg-white rounded-2xl p-5 shadow border border-slate-100 hover:shadow-md transition">
            <p class="text-xs font-bold text-slate-500">Total Laporan</p>
            <p class="text-2xl font-black text-rose-600">{{ $stats['total_laporan'] ?? 0 }}</p>
        </a>
    </div>

    <!-- Kartu Menu Aksi Utama -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Kartu 1: Ajukan Reservasi Baru -->
        <a href="{{ route('pengguna.reservasi.create') }}"
            class="group relative bg-white rounded-2xl p-6 shadow border border-slate-100 overflow-hidden cursor-pointer hover:shadow-lg transition duration-300 flex flex-col justify-between min-h-[220px]">
            <div class="absolute inset-0 bg-gradient-to-r from-blue-950/90 to-blue-900/80 z-10"></div>
            <img src="https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=800&q=80"
                 alt="Fasilitas Kampus"
                 class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition duration-500">

            <div class="relative z-20 text-white space-y-2">
                <span class="bg-blue-500/30 text-blue-200 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider border border-blue-400/30">
                    Layanan Fasilitas
                </span>
                <h2 class="text-xl font-black leading-snug">Ajukan Reservasi Baru</h2>
                <p class="text-xs text-slate-200 max-w-sm">Lihat jadwal ketersediaan slot 30 menit di katalog fasilitas, pilih slot yang kosong, lalu isi formulir reservasi.</p>
            </div>

            <div class="relative z-20 pt-4 flex items-center text-xs font-bold text-blue-200 group-hover:text-white transition">
                <span>Lihat Jadwal Fasilitas</span>
                <svg class="w-4 h-4 ml-1 transform group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                </svg>
            </div>
        </a>

        <!-- Kartu 2: Laporkan Kendala Fasilitas -->
        <a href="{{ route('pengguna.laporan.create') }}"
            class="group relative bg-white rounded-2xl p-6 shadow border border-slate-100 overflow-hidden cursor-pointer hover:shadow-lg transition duration-300 flex flex-col justify-between min-h-[220px]">
            <div class="absolute inset-0 bg-gradient-to-r from-rose-950/90 to-rose-900/80 z-10"></div>
            <img src="https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=800&q=80"
                 alt="Perbaikan Fasilitas"
                 class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition duration-500">

            <div class="relative z-20 text-white space-y-2">
                <span class="bg-rose-500/30 text-rose-200 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider border border-rose-400/30">
                    Layanan Pengaduan
                </span>
                <h2 class="text-xl font-black leading-snug">Laporkan Kendala Fasilitas</h2>
                <p class="text-xs text-slate-200 max-w-sm">AC mati, proyektor rusak, atau fasilitas kurang layak? Laporkan kendalamu agar tim teknis segera memperbaiki.</p>
            </div>

            <div class="relative z-20 pt-4 flex items-center text-xs font-bold text-rose-200 group-hover:text-white transition">
                <span>Isi Form Laporan</span>
                <svg class="w-4 h-4 ml-1 transform group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                </svg>
            </div>
        </a>
    </div>

    <!-- Reservasi Terakhir -->
    <div class="bg-white rounded-2xl shadow border border-slate-100 p-6 space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="font-black text-slate-900 text-base">Reservasi Terakhir</h2>
            <a href="{{ route('pengguna.reservasi.index') }}" class="text-xs font-bold text-blue-900 hover:text-blue-700">Lihat Semua →</a>
        </div>

        <div class="space-y-2">
            @forelse($myReservations->take(3) as $r)
                <div class="flex flex-wrap items-center justify-between gap-2 border border-slate-100 rounded-xl p-3 text-xs">
                    <div class="space-y-1">
                        <p class="font-black text-slate-900">{{ $r->facility->nama_fasilitas ?? '-' }}</p>
                        <p class="text-slate-500">
                            {{ \Carbon\Carbon::parse($r->tanggal)->format('d M Y') }} •
                            {{ \Carbon\Carbon::parse($r->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($r->end_time)->format('H:i') }} WIB
                        </p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold
                        @if($r->status === 'approved') bg-emerald-100 text-emerald-700
                        @elseif($r->status === 'rejected') bg-rose-100 text-rose-700
                        @elseif($r->status === 'cancelled') bg-slate-200 text-slate-600
                        @else bg-amber-100 text-amber-700 @endif">
                        @if($r->status === 'approved') Disetujui
                        @elseif($r->status === 'rejected') Ditolak
                        @elseif($r->status === 'cancelled') Dibatalkan
                        @else Menunggu Persetujuan @endif
                    </span>
                </div>
            @empty
                <p class="text-center text-slate-400 text-xs py-6">Belum ada riwayat reservasi.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection