@extends('layouts.app')

@section('content')
<div class="space-y-4">

    <!-- HERO BANNER (FULL WIDTH / SAMPAI PINGGIR LAYAR) -->
    <div class="w-full bg-[#0f2540] text-white py-6 px-4 sm:px-6 lg:px-8 shadow-md">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row md:items-center justify-between gap-4">
            
            <!-- Left Text Section -->
            <div class="space-y-1">
                <p class="text-xs font-extrabold text-blue-300 tracking-wider uppercase">
                    Civitas Akademika Kampus &bull; Layanan Sarana &amp; Prasarana
                </p>

                <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white flex items-center gap-2">
                    Halo, {{ auth()->user()->nama ?? auth()->user()->name ?? 'Pengguna' }} 👋
                </h1>

                <p class="text-xs sm:text-sm text-slate-200 font-medium leading-relaxed pt-0.5">
                    Selamat datang di portal reservasi kampus. Pantau permohonan yang sedang ditinjau dan tindak lanjut aduan fasilitas secara efisien.
                </p>
            </div>

            <!-- Right Action Buttons -->
            <div class="flex items-center gap-2.5 shrink-0 pt-2 md:pt-0">
                <a href="{{ route('pengguna.reservasi.create') }}" 
                   class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow transition">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    Pinjam Ruangan
                </a>

                <a href="{{ route('pengguna.laporan.create') }}" 
                   class="inline-flex items-center justify-center px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow transition">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    Laporkan Fasilitas
                </a>
            </div>

        </div>
    </div>

    <!-- MAIN CONTENT CONTAINER -->
    <div class="max-w-7xl mx-auto px-4 py-2 space-y-4">

        <!-- RINGKASAN AKTIVITAS SAYA (KARTU COMPACT HEIGHT) -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            
            <!-- Card 1: Total Reservasi -->
            <div class="bg-blue-600 rounded-xl p-3 text-white flex flex-col justify-between h-20 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold text-blue-100 tracking-wide uppercase">Total Reservasi</span>
                    <div class="p-1 bg-white/10 rounded-lg">
                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>

                <div class="flex items-end justify-between">
                    <span class="text-2xl font-black text-white leading-none">{{ $stats['total_reservasi'] ?? 0 }}</span>
                    <a href="{{ route('pengguna.reservasi.index') }}" class="text-[10px] font-extrabold text-white bg-white/20 hover:bg-white/30 px-2 py-1 rounded-lg transition">
                        Lihat &rarr;
                    </a>
                </div>
            </div>

            <!-- Card 2: Menunggu Review -->
            <div class="bg-amber-500 rounded-xl p-3 text-white flex flex-col justify-between h-20 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold text-amber-100 tracking-wide uppercase">Menunggu Review</span>
                    <div class="p-1 bg-white/10 rounded-lg">
                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>

                <div class="flex items-end justify-between">
                    <span class="text-2xl font-black text-white leading-none">{{ $stats['menunggu'] ?? 0 }}</span>
                    <a href="{{ route('pengguna.reservasi.index', ['status' => 'pending']) }}" class="text-[10px] font-extrabold text-white bg-white/20 hover:bg-white/30 px-2 py-1 rounded-lg transition">
                        Lihat &rarr;
                    </a>
                </div>
            </div>

            <!-- Card 3: Disetujui -->
            <div class="bg-emerald-600 rounded-xl p-3 text-white flex flex-col justify-between h-20 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold text-emerald-100 tracking-wide uppercase">Disetujui</span>
                    <div class="p-1 bg-white/10 rounded-lg">
                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>

                <div class="flex items-end justify-between">
                    <span class="text-2xl font-black text-white leading-none">{{ $stats['disetujui'] ?? 0 }}</span>
                    <a href="{{ route('pengguna.reservasi.index', ['status' => 'approved']) }}" class="text-[10px] font-extrabold text-white bg-white/20 hover:bg-white/30 px-2 py-1 rounded-lg transition">
                        Lihat &rarr;
                    </a>
                </div>
            </div>

            <!-- Card 4: Riwayat Kendala -->
            <div class="bg-rose-600 rounded-xl p-3 text-white flex flex-col justify-between h-20 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold text-rose-100 tracking-wide uppercase">Riwayat Kendala</span>
                    <div class="p-1 bg-white/10 rounded-lg">
                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        </svg>
                    </div>
                </div>

                <div class="flex items-end justify-between">
                    <span class="text-2xl font-black text-white leading-none">{{ $stats['total_laporan'] ?? 0 }}</span>
                    <a href="{{ route('pengguna.laporan.index') }}" class="text-[10px] font-extrabold text-white bg-white/20 hover:bg-white/30 px-2 py-1 rounded-lg transition">
                        Lihat &rarr;
                    </a>
                </div>
            </div>

        </div>

        <!-- SEBELAHAN: TABEL RESERVASI TERAKHIR & LAPORAN TERAKHIR (DIPERBESAR & LEGA) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

            <!-- KIRI: 3 RESERVASI TERAKHIR -->
            <div class="bg-white rounded-xl shadow-xs border border-slate-200 p-4 space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                    <h2 class="font-black text-slate-900 text-xs uppercase tracking-wider flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                        3 Reservasi Terakhir
                    </h2>

                    <a href="{{ route('pengguna.reservasi.index') }}" class="text-xs font-bold text-blue-800 hover:text-blue-600 transition">Lihat Semua &rarr;</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <tbody class="divide-y divide-slate-100">
                            @forelse($myReservations->take(3) as $r)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-2.5 pr-2">
                                        <p class="font-extrabold text-slate-900 text-xs sm:text-sm truncate max-w-[260px] leading-snug">
                                            {{ $r->facility->nama_fasilitas ?? '-' }}
                                        </p>

                                        <p class="text-xs text-slate-500 font-medium mt-0.5">
                                            📅 {{ \Carbon\Carbon::parse($r->tanggal)->format('d M Y') }} &bull;
                                            ⏰ {{ \Carbon\Carbon::parse($r->start_time)->format('H:i') }}-{{ \Carbon\Carbon::parse($r->end_time)->format('H:i') }} WIB
                                        </p>
                                    </td>

                                    <td class="py-2.5 text-right whitespace-nowrap align-middle">
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider
                                            @if($r->status === 'approved') bg-emerald-100 text-emerald-800 border border-emerald-200
                                            @elseif($r->status === 'rejected') bg-rose-100 text-rose-800 border border-rose-200
                                            @elseif($r->status === 'cancelled') bg-slate-200 text-slate-700 border border-slate-300
                                            @else bg-amber-100 text-amber-800 border border-amber-200 @endif">
                                            @if($r->status === 'approved') Disetujui
                                            @elseif($r->status === 'rejected') Ditolak
                                            @elseif($r->status === 'cancelled') Dibatalkan
                                            @else Menunggu @endif
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-slate-400 text-xs py-6 font-medium">
                                        Belum ada riwayat reservasi.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- KANAN: 3 LAPORAN TERAKHIR -->
            <div class="bg-white rounded-xl shadow-xs border border-slate-200 p-4 space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                    <h2 class="font-black text-slate-900 text-xs uppercase tracking-wider flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-600"></span>
                        3 Laporan Kendala Terakhir
                    </h2>

                    <a href="{{ route('pengguna.laporan.index') }}" class="text-xs font-bold text-rose-800 hover:text-rose-600 transition">Lihat Semua &rarr;</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <tbody class="divide-y divide-slate-100">
                            @forelse(($myReports ?? collect())->take(3) as $l)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-2.5 pr-2">
                                        <p class="font-extrabold text-slate-900 text-xs sm:text-sm truncate max-w-[260px] leading-snug">
                                            {{ $l->kategori_laporan ?? $l->deskripsi ?? 'Laporan Kendala' }}
                                        </p>

                                        <p class="text-xs text-slate-500 font-medium mt-0.5">
                                            📍 {{ $l->facility->nama_fasilitas ?? 'Fasilitas Kampus' }} &bull;
                                            📅 {{ \Carbon\Carbon::parse($l->created_at)->format('d M Y') }}
                                        </p>
                                    </td>

                                    <td class="py-2.5 text-right whitespace-nowrap align-middle">
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider
                                            @if($l->status_laporan === 'selesai' || $l->status === 'resolved') bg-emerald-100 text-emerald-800 border border-emerald-200
                                            @elseif($l->status_laporan === 'proses' || $l->status === 'in_progress') bg-blue-100 text-blue-800 border border-blue-200
                                            @else bg-amber-100 text-amber-800 border border-amber-200 @endif">
                                            {{ ucfirst($l->status_laporan ?? $l->status ?? 'Menunggu') }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-slate-400 text-xs py-6 font-medium">
                                        Belum ada riwayat laporan kendala.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection